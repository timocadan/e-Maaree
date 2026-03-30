<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Requests\Section\SectionCreate;
use App\Http\Requests\Section\SectionUpdate;
use App\Models\Section;
use App\Repositories\MyClassRepo;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepo;

class SectionController extends Controller
{
    protected $my_class, $user;

    public function __construct(MyClassRepo $my_class, UserRepo $user)
    {
        $this->middleware('teamSA', ['except' => ['destroy',] ]);
        $this->middleware('super_admin', ['only' => ['destroy',] ]);

        $this->my_class = $my_class;
        $this->user = $user;
    }

    public function index()
    {
        $d['my_classes'] = $this->my_class->all();
        $d['sections'] = $this->my_class->getAllSections();
        $d['teachers'] = $this->user->getUserByType('teacher');

        return view('pages.support_team.sections.index', $d);
    }

    public function store(SectionCreate $req)
    {
        $data = $req->all();
        $this->my_class->createSection($data);

        return Qs::jsonStoreOk();
    }

    public function edit($section_id)
    {
        $d['s'] = $s = Section::findOrFail($section_id);
        $d['teachers'] = $this->user->getUserByType('teacher');
        return view('pages.support_team.sections.edit', $d);
    }

    public function update(SectionUpdate $req, $section_id)
    {
        $section = Section::findOrFail($section_id);
        $data = $req->only(['name', 'teacher_id']);
        $section->update($data);

        return Qs::jsonUpdateOk();
    }

    public function destroy($section_id)
    {
        $section = Section::findOrFail($section_id);
        if((int) $section->active === 1){
            return back()->with('pop_warning', 'Every class must have a default section, You Cannot Delete It');
        }

        $section->delete();
        return back()->with('flash_success', __('msg.del_ok'));
    }

}
