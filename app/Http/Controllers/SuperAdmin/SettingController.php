<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingUpdate;
use App\Models\MarkConfig;
use App\Models\MarkTemplate;
use App\Repositories\MyClassRepo;
use App\Repositories\SettingRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    protected $setting, $my_class;

    public function __construct(SettingRepo $setting, MyClassRepo $my_class)
    {
        $this->setting = $setting;
        $this->my_class = $my_class;
    }

    public function index()
    {
         $s = $this->setting->all();
         $d['class_types'] = $this->my_class->getTypes();
         $d['s'] = $s->flatMap(function($s){
            return [$s->type => $s->description];
        });
        $d['s']['weekend_type'] = $d['s']['weekend_type'] ?? 'sat_sun';
        $d['assessment_schemes'] = $this->assessmentSchemes();
        $schemeIds = $d['assessment_schemes']->pluck('id')->all();
        $d['scheme_assessment_map'] = $d['assessment_schemes']->mapWithKeys(function (array $scheme) {
            return [(string) $scheme['id'] => $scheme['titles']];
        })->all();
        $d['current_active_scheme_id'] = $this->resolveCurrentActiveSchemeId($d['s']['active_scheme_id'] ?? null, $schemeIds);
        $currentTitles = $d['scheme_assessment_map'][(string) $d['current_active_scheme_id']] ?? [];
        $d['current_active_assessment_title'] = $this->resolveCurrentActiveAssessmentTitle(
            $d['s']['active_assessment_title'] ?? '',
            $currentTitles,
            $d['current_active_scheme_id']
        );

        return view('pages.super_admin.settings', $d);
    }

    public function updateActiveSlot(Request $req)
    {
        $req->validate([
            'active_scheme_id' => ['required', 'integer', 'exists:mark_templates,id'],
        ]);

        $scheme = MarkTemplate::findOrFail((int) $req->input('active_scheme_id'));
        $titles = collect($scheme->slotsForDisplay())
            ->pluck('label')
            ->map(function ($label) {
                return trim((string) $label);
            })
            ->filter()
            ->values()
            ->all();

        $req->validate([
            'active_assessment_title' => ['required', 'string', Rule::in($titles)],
        ]);

        $schemeId = (int) $scheme->id;
        $title = trim((string) $req->input('active_assessment_title'));
        $this->setting->updateOrCreate('active_scheme_id', $schemeId);
        $this->setting->updateOrCreate('active_assessment_title', $title);

        MarkConfig::with('template')
            ->where('mark_template_id', $schemeId)
            ->get()
            ->each(function (MarkConfig $config) use ($title) {
            $matchedSlot = collect($config->slotsForDisplay())->first(function (array $slot) use ($title) {
                return trim((string) ($slot['label'] ?? '')) === $title;
            });

            if ($matchedSlot) {
                $config->update(['active_slot' => (int) ($matchedSlot['slot_index'] ?? 0)]);
            }
        });

        return back()->with('flash_success', __('Active assessment set to :title for :scheme. All other slots are now locked.', ['title' => $title, 'scheme' => $scheme->name]));
    }

    public function update(SettingUpdate $req)
    {
        $sets = $req->except('_token', '_method');

        foreach ($sets as $key => $value) {
            if (strpos($key, 'next_term_fees_') === 0) {
                $this->setting->updateOrCreate($key, $value ?? '');
            } else {
                $this->setting->updateOrCreate($key, $value);
            }
        }

        return back()->with('flash_success', __('msg.update_ok'));
    }

    protected function assessmentSchemes()
    {
        return MarkTemplate::orderBy('name')
            ->get()
            ->map(function (MarkTemplate $template) {
                return [
                    'id' => (int) $template->id,
                    'name' => trim((string) $template->name),
                    'titles' => collect($template->slotsForDisplay())
                        ->pluck('label')
                        ->map(function ($label) {
                            return trim((string) $label);
                        })
                        ->filter()
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ;
    }

    protected function resolveCurrentActiveSchemeId($storedSchemeId, array $schemeIds): int
    {
        $storedSchemeId = (int) $storedSchemeId;
        if ($storedSchemeId > 0 && in_array($storedSchemeId, $schemeIds, true)) {
            return $storedSchemeId;
        }

        $firstConfig = MarkConfig::whereNotNull('mark_template_id')->orderBy('id')->first();
        if ($firstConfig && in_array((int) $firstConfig->mark_template_id, $schemeIds, true)) {
            return (int) $firstConfig->mark_template_id;
        }

        return (int) ($schemeIds[0] ?? 0);
    }

    protected function resolveCurrentActiveAssessmentTitle(string $storedTitle, array $assessmentTitles, int $schemeId): string
    {
        $storedTitle = trim($storedTitle);
        if ($storedTitle !== '' && in_array($storedTitle, $assessmentTitles, true)) {
            return $storedTitle;
        }

        $firstConfig = MarkConfig::with('template')
            ->where('mark_template_id', $schemeId)
            ->orderBy('id')
            ->first();
        if ($firstConfig) {
            $legacyIndex = (int) ($firstConfig->active_slot ?? 0);
            foreach ($firstConfig->slotsForDisplay() as $slot) {
                if ((int) ($slot['slot_index'] ?? -1) === $legacyIndex) {
                    return trim((string) ($slot['label'] ?? ''));
                }
            }
        }

        return $assessmentTitles[0] ?? '';
    }
}
