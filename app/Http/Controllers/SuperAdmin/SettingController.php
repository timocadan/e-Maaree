<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingUpdate;
use App\Models\MarkConfig;
use App\Repositories\MyClassRepo;
use App\Repositories\SettingRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Tenancy;

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
        $first = MarkConfig::first();
        $d['current_active_slot'] = $first ? (int) $first->active_slot : 0;
        return view('pages.super_admin.settings', $d);
    }

    public function updateActiveSlot(Request $req)
    {
        $req->validate(['active_slot' => 'required|integer|min:0|max:10']);
        $slot = (int) $req->active_slot;
        MarkConfig::query()->update(['active_slot' => $slot]);
        return back()->with('flash_success', __('Active assessment slot set to :n. All other slots are now locked.', ['n' => $slot + 1]));
    }

    public function update(SettingUpdate $req)
    {
        $sets = $req->except('_token', '_method', 'logo');
        $sets['lock_exam'] = isset($sets['lock_exam']) && $sets['lock_exam'] == 1 ? 1 : 0;

        foreach ($sets as $key => $value) {
            if (strpos($key, 'next_term_fees_') === 0) {
                $this->setting->updateOrCreate($key, $value ?? '');
            } elseif (in_array($key, ['phone2', 'website'], true)) {
                $this->setting->updateOrCreate($key, $value ?? '');
            } else {
                $this->setting->updateOrCreate($key, $value);
            }
        }

        if ($req->hasFile('logo')) {
            $file = $req->file('logo');
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $tenancy = app(Tenancy::class);
            $tenantId = $tenancy->initialized && $tenancy->tenant
                ? $tenancy->tenant->getTenantKey()
                : 'default';
            $path = 'logos/' . $tenantId . '/logo.' . $ext;
            Storage::disk('central_public')->putFileAs(
                'logos/' . $tenantId,
                $file,
                'logo.' . $ext
            );
            $this->setting->updateOrCreate('logo', $path);
        }

        return back()->with('flash_success', __('msg.update_ok'));
    }
}
