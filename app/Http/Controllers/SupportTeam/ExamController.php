<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Requests\Exam\ExamCreate;
use App\Http\Requests\Exam\ExamUpdate;
use App\Models\Exam;
use App\Repositories\ExamRepo;
use App\Http\Controllers\Controller;

class ExamController extends Controller
{
    protected $exam;
    public function __construct(ExamRepo $exam)
    {
        $this->middleware('teamSA', ['except' => ['destroy',] ]);
        $this->middleware('super_admin', ['only' => ['destroy',] ]);

        $this->exam = $exam;
    }

    public function index()
    {
        $d['exams'] = $this->exam->all();
        return view('pages.support_team.exams.index', $d);
    }

    public function create()
    {
        return redirect()->route('exams.index')->with('focus_tab', 'new-exam');
    }

    public function store(ExamCreate $req)
    {
        $data = $req->only(['name', 'term']);
        $data['year'] = Qs::getSetting('current_session');

        $this->exam->create($data);
        return back()->with('flash_success', __('msg.store_ok'));
    }

    public function edit($exam_id)
    {
        $d['ex'] = Exam::findOrFail($exam_id);
        return view('pages.support_team.exams.edit', $d);
    }

    public function update(ExamUpdate $req, $exam_id)
    {
        Exam::findOrFail($exam_id);
        $data = $req->only(['name', 'term']);
        $this->exam->update($exam_id, $data);
        return back()->with('flash_success', __('msg.update_ok'));
    }

    public function destroy($exam_id)
    {
        Exam::findOrFail($exam_id);
        $this->exam->delete($exam_id);
        return back()->with('flash_success', __('msg.del_ok'));
    }
}
