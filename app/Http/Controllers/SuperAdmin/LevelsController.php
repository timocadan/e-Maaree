<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\ClassType;
use App\Models\Grade;
use App\Models\MarkConfig;
use App\Models\MarkTemplate;
use App\Models\MyClass;
use Illuminate\Http\Request;

class LevelsController extends Controller
{
    public function index()
    {
        $levels = ClassType::orderBy('name')->withCount('myClasses')->get();
        $templates = MarkTemplate::orderBy('name')->get();
        $school_year = trim((string) Qs::getSetting('current_session')) ?: Qs::getCurrentSession();
        $terms = [1 => __('Term 1'), 2 => __('Term 2')];

        $configs = [];
        foreach ($levels as $level) {
            foreach (array_keys($terms) as $termId) {
                $cfg = MarkConfig::where('class_type_id', $level->id)
                    ->where('term_id', $termId)
                    ->where('school_year', $school_year)
                    ->first();
                $configs[$level->id][$termId] = $cfg ? $cfg->mark_template_id : null;
            }
        }

        return view('pages.support_team.classes.types.index', compact('levels', 'templates', 'school_year', 'terms', 'configs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'term_1_template_id' => 'nullable|exists:mark_templates,id',
            'term_2_template_id' => 'nullable|exists:mark_templates,id',
        ]);

        $name = trim($request->name);
        $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 10)) ?: 'L' . (ClassType::max('id') + 1);
        $base = $code;
        $i = 0;
        while (ClassType::where('code', $code)->exists()) {
            $code = $base . (++$i);
        }

        $level = ClassType::create([
            'name' => $name,
            'code' => $code,
        ]);

        $this->syncLevelMapping($level->id, trim((string) Qs::getSetting('current_session')) ?: Qs::getCurrentSession(), [
            1 => $request->input('term_1_template_id'),
            2 => $request->input('term_2_template_id'),
        ]);

        return redirect()->route('levels.index')->with('flash_success', 'Level added successfully.');
    }

    public function update(Request $request, $level_id)
    {
        $level = ClassType::findOrFail($level_id);

        $request->validate([
            'name' => 'required|string|max:100',
            'term_1_template_id' => 'nullable|exists:mark_templates,id',
            'term_2_template_id' => 'nullable|exists:mark_templates,id',
        ]);

        $level->update(['name' => trim($request->name)]);
        $this->syncLevelMapping($level->id, trim((string) Qs::getSetting('current_session')) ?: Qs::getCurrentSession(), [
            1 => $request->input('term_1_template_id'),
            2 => $request->input('term_2_template_id'),
        ]);

        return redirect()->route('levels.index')->with('flash_success', 'Level updated successfully.');
    }

    public function destroy($level_id)
    {
        $level = ClassType::findOrFail($level_id);

        $classesCount = MyClass::where('class_type_id', $level_id)->count();
        $gradesCount = Grade::where('class_type_id', $level_id)->count();

        if ($classesCount > 0 || $gradesCount > 0) {
            return back()->with('flash_danger', 'This level cannot be deleted because it is in use (Classes: ' . $classesCount . ', Grades: ' . $gradesCount . '). Remove or reassign them first.');
        }

        $level->delete();
        return redirect()->route('levels.index')->with('flash_success', 'Level deleted successfully.');
    }

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'config' => 'array',
            'config.*.label' => 'nullable|string|max:60',
            'config.*.max' => 'nullable|integer|min:0|max:100',
        ]);

        $items = $this->normalizeConfig($request->input('config', []));
        if (empty($items)) {
            $items = MarkTemplate::defaultConfiguration();
        }

        MarkTemplate::create([
            'name' => $request->input('name'),
            'configuration' => $items,
        ]);

        return redirect()->route('levels.index')->with('flash_success', __('Scheme created.'));
    }

    public function updateTemplate(Request $request, $template_id)
    {
        $template = MarkTemplate::findOrFail($template_id);

        $request->validate([
            'name' => 'required|string|max:120',
            'config' => 'array',
            'config.*.label' => 'nullable|string|max:60',
            'config.*.max' => 'nullable|integer|min:0|max:100',
        ]);

        $items = $this->normalizeConfig($request->input('config', []));
        if (empty($items)) {
            $items = MarkTemplate::defaultConfiguration();
        }

        $template->update([
            'name' => $request->input('name'),
            'configuration' => $items,
        ]);

        return redirect()->route('levels.index')->with('flash_success', __('Scheme updated.'));
    }

    public function destroyTemplate($template_id)
    {
        $template = MarkTemplate::findOrFail($template_id);

        $inUse = MarkConfig::where('mark_template_id', $template->id)->count();
        if ($inUse > 0) {
            return redirect()->route('levels.index')
                ->with('flash_danger', __('This scheme cannot be deleted because it is mapped to one or more levels.'));
        }

        $template->delete();
        return redirect()->route('levels.index')->with('flash_success', __('Scheme deleted.'));
    }

    public function saveMapping(Request $request)
    {
        $request->validate([
            'school_year' => 'required|string|max:15',
            'mapping' => 'array',
            'mapping.*' => 'array',
            'mapping.*.*' => 'nullable|exists:mark_templates,id',
        ]);

        $schoolYear = trim((string) $request->input('school_year'));
        $mapping = $request->input('mapping', []);

        foreach ($mapping as $classTypeId => $termMap) {
            if (!is_array($termMap)) {
                continue;
            }
            $classTypeId = (int) $classTypeId;
            $this->syncLevelMapping($classTypeId, $schoolYear, $termMap);
        }

        return redirect()->route('levels.index')->with('flash_success', __('Mapping saved.'));
    }

    protected function syncLevelMapping(int $classTypeId, string $schoolYear, array $termMap): void
    {
        if ($classTypeId < 1 || trim($schoolYear) === '') {
            return;
        }

        foreach ($termMap as $termId => $templateId) {
            $termId = (int) $termId;
            if (!in_array($termId, [1, 2], true)) {
                continue;
            }
            MarkConfig::updateOrCreate(
                [
                    'class_type_id' => $classTypeId,
                    'term_id' => $termId,
                    'school_year' => $schoolYear,
                ],
                [
                    'mark_template_id' => $templateId ?: null,
                    'active_slot' => 0,
                ]
            );
        }
    }

    protected function normalizeConfig(array $config): array
    {
        $items = [];
        foreach ($config as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            $max = isset($row['max']) ? (int) $row['max'] : 0;
            if ($label === '' && $max <= 0) {
                continue;
            }
            $items[] = ['label' => ($label !== '' ? $label : 'Slot'), 'max' => max(0, min(100, $max))];
            if (count($items) >= 10) {
                break;
            }
        }
        return $items;
    }
}
