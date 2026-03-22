<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Helpers\Pay;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\PaymentCreate;
use App\Http\Requests\Payment\PaymentUpdate;
use App\Models\Setting;
use App\Repositories\MyClassRepo;
use App\Repositories\PaymentRepo;
use App\Repositories\StudentRepo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PDF;

class PaymentController extends Controller
{
    protected $my_class, $pay, $student, $year;

    public function __construct(MyClassRepo $my_class, PaymentRepo $pay, StudentRepo $student)
    {
        $this->my_class = $my_class;
        $this->pay = $pay;
        $this->year = Qs::getCurrentSession();
        $this->student = $student;

        $this->middleware('teamAccount')->except(['invoice', 'receipts', 'pdf_receipts']);
    }

    public function index()
    {
        $d['selected'] = false;
        $d['years'] = $this->pay->getPaymentYears();

        return view('pages.support_team.payments.index', $d);
    }

    public function show($year)
    {
        $d['payments'] = $p = $this->pay->getPayment(['year' => $year])->get();

        if(($p->count() < 1)){
            return Qs::goWithDanger('payments.index');
        }

        $d['selected'] = true;
        $d['my_classes'] = $this->my_class->all();
        $d['years'] = $this->pay->getPaymentYears();
        $d['year'] = $year;

        return view('pages.support_team.payments.index', $d);

    }

    public function select_year(Request $req)
    {
        return Qs::goToRoute(['payments.show', $req->year]);
    }

    public function create()
    {
        $d['my_classes'] = $this->my_class->all();
        return view('pages.support_team.payments.create', $d);
    }

    public function invoice($st_id, $year = NULL)
    {
        $user_id = Qs::decodeHash($st_id);
        if ($user_id === null) {
            abort(404);
        }
        if (Qs::userIsStudent()) {
            if ((int) $user_id !== (int) Auth::id()) {
                return redirect()->route('dashboard')->with('flash_danger', __('msg.denied'));
            }
        } elseif (!Qs::userIsTeamAccount()) {
            return redirect()->route('dashboard')->with('flash_danger', __('msg.denied'));
        }

        $inv = $year ? $this->pay->getAllMyPR($user_id, $year) : $this->pay->getAllMyPR($user_id);
        $d['sr'] = $this->student->findByUserId($user_id)->first();
        if (!$d['sr']) {
            return Qs::goWithDanger('dashboard');
        }
        $pr = $inv->get();
        $d['uncleared'] = $pr->where('paid', 0);
        $d['cleared'] = $pr->where('paid', 1);

        return view('pages.support_team.payments.invoice', $d);
    }

    public function receipts($pr_id)
    {
        $record_id = Qs::decodeHash($pr_id);
        if ($record_id === null) {
            abort(404);
        }
        try {
             $d['pr'] = $pr = $this->pay->getRecord(['id' => $record_id])->with('receipt')->first();
        } catch (ModelNotFoundException $ex) {
            return back()->with('flash_danger', __('msg.rnf'));
        }
        if (Qs::userIsStudent() && (int) $pr->student_id !== (int) Auth::id()) {
            return redirect()->route('dashboard')->with('flash_danger', __('msg.denied'));
        }
        if (!Qs::userIsStudent() && !Qs::userIsTeamAccount()) {
            return redirect()->route('dashboard')->with('flash_danger', __('msg.denied'));
        }

        $d['receipts'] = $pr->receipt;
        $d['payment'] = $pr->payment;
        $d['sr'] = $this->student->findByUserId($pr->student_id)->first();
        $d['s'] = Setting::all()->flatMap(function($s){
            return [$s->type => $s->description];
        });

        return view('pages.support_team.payments.receipt', $d);
    }

    public function pdf_receipts($pr_id)
    {
        $record_id = Qs::decodeHash($pr_id);
        if ($record_id === null) {
            abort(404);
        }
        try {
            $d['pr'] = $pr = $this->pay->getRecord(['id' => $record_id])->with('receipt')->first();
        } catch (ModelNotFoundException $ex) {
            return back()->with('flash_danger', __('msg.rnf'));
        }
        if (Qs::userIsStudent() && (int) $pr->student_id !== (int) Auth::id()) {
            return redirect()->route('dashboard')->with('flash_danger', __('msg.denied'));
        }
        if (!Qs::userIsStudent() && !Qs::userIsTeamAccount()) {
            return redirect()->route('dashboard')->with('flash_danger', __('msg.denied'));
        }
        $d['receipts'] = $pr->receipt;
        $d['payment'] = $pr->payment;
        $d['sr'] = $sr =$this->student->findByUserId($pr->student_id)->first();
        $d['s'] = Setting::all()->flatMap(function($s){
            return [$s->type => $s->description];
        });

        $pdf_name = 'Receipt_'.$pr->ref_no;

        return PDF::loadView('pages.support_team.payments.receipt', $d)->download($pdf_name);

        //return $this->downloadReceipt('pages.support_team.payments.receipt', $d, $pdf_name);
    }

    protected function downloadReceipt($page, $data, $name = NULL){
        $path = 'receipts/file.html';
        $disk = Storage::disk('local');
        $disk->put($path, view($page, $data) );
        $html = $disk->get($path);
        return PDF::loadHTML($html)->download($name);
    }

    public function pay_now(Request $req, $pr_id)
    {
        $pr_id = Qs::decodeHash($pr_id);
        if ($pr_id === null) {
            abort(404);
        }
        $this->validate($req, [
            'amt_paid' => 'required|numeric'
        ], [], ['amt_paid' => 'Amount Paid']);

        $pr = $this->pay->findRecord($pr_id);
        $payment = $this->pay->find($pr->payment_id);
        $d['amt_paid'] = $amt_p = $pr->amt_paid + $req->amt_paid;
        $d['balance'] = $bal = $payment->amount - $amt_p;
        $d['paid'] = $bal < 1 ? 1 : 0;

        $this->pay->updateRecord($pr_id, $d);

        $d2['amt_paid'] = $req->amt_paid;
        $d2['balance'] = $bal;
        $d2['pr_id'] = $pr_id;
        $d2['year'] = $this->year;

        $this->pay->createReceipt($d2);
        return Qs::jsonUpdateOk();
    }

    public function manage($class_id = NULL)
    {
        $d['my_classes'] = $this->my_class->all();
        $d['selected'] = false;

        if($class_id){
            $d['students'] = $st = $this->student->getRecord(['my_class_id' => $class_id])->get()->sortBy('user.name');
            if($st->count() < 1){
                return Qs::goWithDanger('payments.manage');
            }
            $d['selected'] = true;
            $d['my_class_id'] = $class_id;
        }

        return view('pages.support_team.payments.manage', $d);
    }

    public function select_class(Request $req)
    {
        $this->validate($req, [
            'my_class_id' => 'required|exists:my_classes,id'
        ], [], ['my_class_id' => 'Class']);

        $wh['my_class_id'] = $class_id = $req->my_class_id;

        $pay1 = $this->pay->getPayment(['my_class_id' => $class_id, 'year' => $this->year])->get();
        $pay2 = $this->pay->getGeneralPayment(['year' => $this->year])->get();
        $payments = $pay2->count() ? $pay1->merge($pay2) : $pay1;
        $students = $this->student->getRecord($wh)->get();

        if($payments->count() && $students->count()){
            foreach($payments as $p){
                foreach($students as $st){
                    $pr['student_id'] = $st->user_id;
                    $pr['payment_id'] = $p->id;
                    $pr['year'] = $this->year;
                    $rec = $this->pay->createRecord($pr);
                    $rec->ref_no ?: $rec->update(['ref_no' => mt_rand(100000, 99999999)]);

                }
            }
        }

        return Qs::goToRoute(['payments.manage', $class_id]);
    }

    public function store(PaymentCreate $req)
    {
        $data = $req->all();
        $data['year'] = $this->year;
        $data['ref_no'] = Pay::genRefCode();
        $this->pay->create($data);

        return Qs::jsonStoreOk();
    }

    public function edit($id)
    {
        $id = Qs::decodeHash($id);
        if ($id === null) {
            abort(404);
        }
        $d['payment'] = $pay = $this->pay->find($id);

        return is_null($pay) ? Qs::goWithDanger('payments.index') : view('pages.support_team.payments.edit', $d);
    }

    public function update(PaymentUpdate $req, $id)
    {
        $id = Qs::decodeHash($id);
        if ($id === null) {
            abort(404);
        }
        $data = $req->all();
        $this->pay->update($id, $data);

        return Qs::jsonUpdateOk();
    }

    public function destroy($id)
    {
        $id = Qs::decodeHash($id);
        if ($id === null) {
            abort(404);
        }
        $this->pay->find($id)->delete();

        return Qs::deleteOk('payments.index');
    }

    public function reset_record($id)
    {
        $id = Qs::decodeHash($id);
        if ($id === null) {
            abort(404);
        }
        $pr['amt_paid'] = $pr['paid'] = $pr['balance'] = 0;
        $this->pay->updateRecord($id, $pr);
        $this->pay->deleteReceipts(['pr_id' => $id]);

        return back()->with('flash_success', __('msg.update_ok'));
    }
}
