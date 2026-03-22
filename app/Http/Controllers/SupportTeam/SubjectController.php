<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Requests\Subject\SubjectCreate;
use App\Http\Requests\Subject\SubjectUpdate;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    protected $my_class, $user;

    public function __construct(MyClassRepo $my_class, UserRepo $user)
    {
        $this->middleware('teamSA', ['except' => ['destroy',] ]);
        $this->middleware('super_admin', ['only' => ['destroy',] ]);

        $this->my_class = $my_class;
        $this->user = $user;
    }

    public function index(Request $request)
    {
        $d['my_classes'] = $this->my_class->all();
        $d['teachers'] = $this->user->getUserByType('teacher');
        $classId = $request->query('class_id');
        $d['selected_class'] = $classId ? $this->my_class->find((int) $classId) : null;
        $d['subjects'] = $d['selected_class'] ? $this->my_class->getSubjectsPaginated(15, (int) $classId) : null;

        return view('pages.support_team.subjects.index', $d);
    }

    public function store(SubjectCreate $req)
    {
        $data = $req->all();
        $this->my_class->createSubject($data);

        return Qs::jsonStoreOk();
    }

    public function edit($id)
    {
        $id = Qs::decodeHash($id);
        if ($id === null) {
            abort(404);
        }
        $d['s'] = $sub = $this->my_class->findSubject($id);
        $d['my_classes'] = $this->my_class->all();
        $d['teachers'] = $this->user->getUserByType('teacher');

        return is_null($sub) ? Qs::goWithDanger('subjects.index') : view('pages.support_team.subjects.edit', $d);
    }

    public function update(SubjectUpdate $req, $id)
    {
        $id = Qs::decodeHash($id);
        if ($id === null) {
            abort(404);
        }
        $data = $req->all();
        $this->my_class->updateSubject($id, $data);

        return Qs::jsonUpdateOk();
    }

    public function destroy($id)
    {
        $id = Qs::decodeHash($id);
        if ($id === null) {
            abort(404);
        }
        $this->my_class->deleteSubject($id);
        return back()->with('flash_success', __('msg.del_ok'));
    }
}
