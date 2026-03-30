<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Requests\UserRequest;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\StudentRecord;


class UserController extends Controller
{
    protected $user, $loc, $my_class;

    public function __construct(UserRepo $user, LocationRepo $loc, MyClassRepo $my_class)
    {
        $this->middleware('teamSA', ['only' => ['index', 'store', 'edit', 'update'] ]);
        $this->middleware('super_admin', ['only' => ['reset_pass','destroy', 'parents', 'reset_parent_pass'] ]);

        $this->user = $user;
        $this->loc = $loc;
        $this->my_class = $my_class;
    }

    public function index()
    {
        $ut = $this->user->getAllTypes();
        $ut2 = $ut->where('level', '>', 2);

        $d['user_types'] = Qs::userIsAdmin() ? $ut2 : $ut;
        $d['states'] = $this->loc->getStates();
        $d['users'] = $this->user->getPTAUsers();
        $d['nationals'] = $this->loc->getAllNationals();
        $d['blood_groups'] = $this->user->getBloodGroups();
        return view('pages.support_team.users.index', $d);
    }

    public function edit($id)
    {
        $id = Qs::decodeHash($id);
        if ($id === null) {
            abort(404);
        }
        $d['user'] = $this->user->find($id);
        $d['states'] = $this->loc->getStates();
        $d['users'] = $this->user->getPTAUsers();
        $d['blood_groups'] = $this->user->getBloodGroups();
        $d['nationals'] = $this->loc->getAllNationals();
        return view('pages.support_team.users.edit', $d);
    }

    public function reset_pass($id)
    {
        $id = Qs::decodeHash($id);
        if ($id === null) {
            abort(404);
        }
        // Redirect if Making Changes to Head of Super Admins
        if(Qs::headSA($id)){
            return back()->with('flash_danger', __('msg.denied'));
        }

        $data['password'] = Hash::make('user');
        $this->user->update($id, $data);
        return back()->with('flash_success', __('msg.pu_reset'));
    }

    public function parents()
    {
        $parents = User::where('user_type', 'parent')
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'phone']);

        $parentIds = $parents->pluck('id')->all();
        $childrenByParent = [];

        if (!empty($parentIds)) {
            $childrenByParent = StudentRecord::with('user:id,name')
                ->whereIn('my_parent_id', $parentIds)
                ->get()
                ->groupBy('my_parent_id')
                ->map(function ($recs) {
                    return $recs
                        ->map(function ($r) {
                            return $r->user ? $r->user->name : null;
                        })
                        ->filter()
                        ->values()
                        ->all();
                })
                ->all();
        }

        return view('pages.support_team.users.parents', [
            'parents' => $parents,
            'childrenByParent' => $childrenByParent,
        ]);
    }

    public function reset_parent_pass($id)
    {
        $id = Qs::decodeHash($id);
        if ($id === null) {
            abort(404);
        }

        $parent = User::findOrFail($id);
        if ($parent->user_type !== 'parent') {
            return back()->with('flash_danger', __('msg.denied'));
        }

        if (Qs::headSA($id)) {
            return back()->with('flash_danger', __('msg.denied'));
        }

        $this->user->update($id, ['password' => Hash::make('123456')]);
        return back()->with('flash_success', 'Parent password reset to default (123456).');
    }

    public function store(UserRequest $req)
    {
        $user_type = $this->user->findType($req->user_type)->title;

        $data = $req->except(Qs::getStaffRecord());
        $data['name'] = ucwords($req->name);
        $data['user_type'] = $user_type;
        $data['photo'] = Qs::getDefaultUserImage();
        $data['code'] = strtoupper(Str::random(10));

        $user_is_staff = in_array($user_type, Qs::getStaff());
        $user_is_teamSA = in_array($user_type, Qs::getTeamSA());

        $emp_date = $req->emp_date ?: now()->format('Y-m-d');
        $staff_id = Qs::getAppCode().'/STAFF/'.date('Y/m', strtotime($emp_date)).'/'.mt_rand(1000, 9999);
        if ($user_is_teamSA) {
            $uname = $req->username;
            if (empty($uname)) {
                if (!empty($req->email)) {
                    $uname = strtolower(trim(explode('@', $req->email)[0]));
                } else {
                    $uname = strtolower(preg_replace('/[^a-z0-9]+/i', '.', trim($req->name)));
                }
                if (strlen($uname) < 8) {
                    $uname .= '.' . Str::random(4);
                }
                $uname = \App\User::where('username', $uname)->exists() ? ($uname . '.' . mt_rand(100, 999)) : $uname;
            }
        } else {
            $uname = $staff_id;
        }
        $data['username'] = $uname;

        $pass = $req->password ?: $user_type;
        $data['password'] = Hash::make($pass);

        if($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Qs::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $photo->storeAs(Qs::getUploadPath($user_type).$data['code'], $f['name']);
            $data['photo'] = asset('storage/' . $f['path']);
        }

        /* Ensure that both username and Email are not blank*/
        if(!$uname && !$req->email){
            return back()->with('pop_error', __('msg.user_invalid'));
        }

        $user = $this->user->create($data); // Create User

        /* CREATE STAFF RECORD */
        if($user_is_staff){
            $d2 = $req->only(Qs::getStaffRecord());
            $d2['user_id'] = $user->id;
            $d2['code'] = $staff_id;
            $d2['emp_date'] = $d2['emp_date'] ?: $emp_date;
            $this->user->createStaffRecord($d2);
        }

        return Qs::jsonStoreOk();
    }

    public function update(UserRequest $req, $id)
    {
        $id = Qs::decodeHash($id);
        if ($id === null) {
            abort(404);
        }
        // Redirect if Making Changes to Head of Super Admins
        if(Qs::headSA($id)){
            return Qs::json(__('msg.denied'), FALSE);
        }

        $user = $this->user->find($id);

        $user_type = $user->user_type;
        $user_is_staff = in_array($user_type, Qs::getStaff());
        $user_is_teamSA = in_array($user_type, Qs::getTeamSA());

        $data = $req->except(Qs::getStaffRecord());
        $data['name'] = ucwords($req->name);
        $data['user_type'] = $user_type;
        // Normalize optional fields: empty string -> null to avoid DB issues (columns are nullable)
        foreach (['gender', 'state_id', 'lga_id', 'nal_id', 'bg_id', 'email', 'phone', 'phone2', 'address'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] === '') {
                $data[$key] = null;
            }
        }

        if($user_is_staff && !$user_is_teamSA){
            $data['username'] = Qs::getAppCode().'/STAFF/'.date('Y/m', strtotime($req->emp_date)).'/'.mt_rand(1000, 9999);
        }
        else {
            $data['username'] = $user->username;
        }

        if($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Qs::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $photo->storeAs(Qs::getUploadPath($user_type).$user->code, $f['name']);
            $data['photo'] = asset('storage/' . $f['path']);
        }

        $this->user->update($id, $data);   /* UPDATE USER RECORD */

        /* UPDATE STAFF RECORD */
        if($user_is_staff){
            $d2 = $req->only(Qs::getStaffRecord());
            $d2['code'] = $data['username'];
            $this->user->updateStaffRecord(['user_id' => $id], $d2);
        }

        return Qs::jsonUpdateOk();
    }

    public function show($user_id)
    {
        $user_id = Qs::decodeHash($user_id);
        if ($user_id === null) {
            abort(404);
        }

        $data['user'] = $this->user->find($user_id);
        $user = $data['user'];

        // Smart address: for parents with empty address, show first child's address
        $data['display_address'] = $user->address;
        if ($user->user_type == 'parent') {
            $addr = trim($user->address ?? '');
            if ($addr === '') {
                $firstChild = Qs::findMyChildren($user_id)->first();
                if ($firstChild && $firstChild->user) {
                    $data['display_address'] = $firstChild->user->address ?? '';
                }
            }
        }

        /* Prevent Other Students from viewing Profile of others*/
        if(Auth::user()->id != $user_id && !Qs::userIsTeamSAT() && !Qs::userIsMyChild(Auth::user()->id, $user_id)){
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        }

        return view('pages.support_team.users.show', $data);
    }

    public function destroy($id)
    {
        $id = Qs::decodeHash($id);
        if ($id === null) {
            abort(404);
        }
        // Redirect if Making Changes to Head of Super Admins
        if(Qs::headSA($id)){
            return back()->with('pop_error', __('msg.denied'));
        }

        $user = $this->user->find($id);

        if($user->user_type == 'teacher' && $this->userTeachesSubject($user)) {
            return back()->with('pop_error', __('msg.del_teacher'));
        }

        $path = Qs::getUploadPath($user->user_type).$user->code;
        Storage::exists($path) ? Storage::deleteDirectory($path) : true;
        $this->user->delete($user->id);

        return back()->with('flash_success', __('msg.del_ok'));
    }

    protected function userTeachesSubject($user)
    {
        $subjects = $this->my_class->findSubjectByTeacher($user->id);
        return ($subjects->count() > 0) ? true : false;
    }

}
