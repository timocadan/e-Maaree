<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Requests\MyClass\ClassCreate;
use App\Http\Requests\MyClass\ClassUpdate;
use App\Models\MyClass;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use App\Http\Controllers\Controller;

class MyClassController extends Controller
{
    protected $my_class, $user;

    public function __construct(MyClassRepo $my_class, UserRepo $user)
    {
        $this->middleware('teamSA', ['except' => ['destroy',] ]);
        $this->middleware('super_admin', ['only' => ['destroy',] ]);

        $this->my_class = $my_class;
        $this->user = $user;
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $query = $this->my_class->getClassQuery();
        $classTypeId = $request->query('class_type_id');
        if ($classTypeId) {
            $query->where('class_type_id', $classTypeId);
        }
        $d['my_classes'] = $query->get();
        $d['class_types'] = $this->my_class->getTypes();
        $d['filter_class_type_id'] = $classTypeId;
        $d['filter_class_type'] = $classTypeId ? $this->my_class->findType($classTypeId) : null;

        return view('pages.support_team.classes.index', $d);
    }

    public function create()
    {
        return redirect()->route('classes.index')->with('focus_tab', 'new-class');
    }

    public function store(ClassCreate $req)
    {
        $data = $req->all();
        $mc = $this->my_class->create($data);

        // Create Default Section
        $s =['my_class_id' => $mc->id,
            'name' => 'A',
            'active' => 1,
            'teacher_id' => NULL,
        ];

        $this->my_class->createSection($s);

        return Qs::jsonStoreOk();
    }

    public function edit($class_id)
    {
        $d['c'] = MyClass::findOrFail($class_id);
        return view('pages.support_team.classes.edit', $d);
    }

    public function update(ClassUpdate $req, $class_id)
    {
        MyClass::findOrFail($class_id);
        $data = $req->only(['name']);
        $this->my_class->update($class_id, $data);
        return Qs::jsonUpdateOk();
    }

    public function destroy($class_id)
    {
        MyClass::findOrFail($class_id);
        $this->my_class->delete($class_id);
        return back()->with('flash_success', __('msg.del_ok'));
    }

}
