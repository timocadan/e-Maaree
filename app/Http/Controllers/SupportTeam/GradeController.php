<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Requests\Grade\GradeCreate;
use App\Http\Requests\Grade\GradeUpdate;
use App\Models\Grade;
use App\Repositories\ExamRepo;
use App\Http\Controllers\Controller;
use App\Repositories\MyClassRepo;

class GradeController extends Controller
{
    protected $exam, $my_class;

    public function __construct(ExamRepo $exam, MyClassRepo $my_class)
    {
        $this->exam = $exam;
        $this->my_class = $my_class;

        $this->middleware('teamSA', ['except' => ['destroy', 'index'] ]);
        $this->middleware('super_admin', ['only' => ['destroy'] ]);
        $this->middleware('teamSAT', ['only' => ['index'] ]);
    }

    public function index()
    {
         $d['grades'] = $this->exam->allGrades();
         $d['class_types'] = $this->my_class->getTypes();
        return view('pages.support_team.grades.index', $d);
    }

    public function create()
    {
        return redirect()->route('grades.index')->with('focus_tab', 'new-grade');
    }

    public function store(GradeCreate $req)
    {
        $data = $req->all();

        $this->exam->createGrade($data);
        return back()->with('flash_success', __('msg.store_ok'));
    }

    public function edit($grade_id)
    {
        $d['class_types'] = $this->my_class->getTypes();
        $d['gr'] = Grade::findOrFail($grade_id);
        return view('pages.support_team.grades.edit', $d);
    }

    public function update(GradeUpdate $req, $grade_id)
    {
        Grade::findOrFail($grade_id);
        $data = $req->all();
        $this->exam->updateGrade($grade_id, $data);
        return back()->with('flash_success', __('msg.update_ok'));
    }

    public function destroy($grade_id)
    {
        Grade::findOrFail($grade_id);
        $this->exam->deleteGrade($grade_id);
        return back()->with('flash_success', __('msg.del_ok'));
    }
}
