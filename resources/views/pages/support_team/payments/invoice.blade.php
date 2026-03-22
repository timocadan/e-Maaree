@extends('layouts.master')
@section('page_title', Qs::userIsStudent() ? 'My Payments' : 'Manage Payments')
@section('content')
<style>
    .table-payment-records thead th { background-color: #002147; color: #fff; font-weight: 600; padding: 10px 12px; border: 1px solid #002147; }
    .table-payment-records tbody td { vertical-align: middle; }
    .pay-now-cell .form-row { align-items: center; }
    .pay-now-cell .form-control { min-width: 140px; width: 140px; }
    .pay-now-cell { padding-right: 1rem; min-width: 200px; }
    .btn-pay-brand { background-color: #D32F2F; color: #fff; border-color: #D32F2F; }
    .btn-pay-brand:hover { background-color: #b71c1c; color: #fff; border-color: #b71c1c; }
</style>
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">{{ Qs::userIsStudent() ? 'Your Payment Records' : 'Manage Payment Records for ' . $sr->user->name }} </h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-highlight">
                    <li class="nav-item"><a href="#all-uc" class="nav-link active" data-toggle="tab">Incomplete Payments</a></li>
                    <li class="nav-item"><a href="#all-cl" class="nav-link" data-toggle="tab">Completed Payments</a></li>
                </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="all-uc">
                <table class="table table-bordered table-payment-records datatable-payment-no-export table-responsive">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        {{-- <th>Pay_Ref</th> --}}
                        <th>Amount ({{ Qs::getCurrency() }})</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        @if(Qs::userIsTeamAccount())
                        <th>Pay Now</th>
                        @endif
                        {{-- <th>Receipt_No</th> --}}
                        {{-- <th>Year</th> --}}
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($uncleared as $uc)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $uc->payment->title }}</td>
                            {{-- <td>{{ $uc->payment->ref_no }}</td> --}}

                            <td class="font-weight-bold" id="amt-{{ Qs::hash($uc->id) }}" data-amount="{{ $uc->payment->amount }}">{{ $uc->payment->amount }}</td>
                            <td id="amt_paid-{{ Qs::hash($uc->id) }}" data-amount="{{ $uc->amt_paid ?: 0 }}" class="text-blue font-weight-bold">{{ $uc->amt_paid ?: '0.00' }}</td>
                            <td id="bal-{{ Qs::hash($uc->id) }}" class="text-danger font-weight-bold">{{ $uc->balance ?: $uc->payment->amount }}</td>

                            @if(Qs::userIsTeamAccount())
                            <td class="pay-now-cell">
                                <form id="{{ Qs::hash($uc->id) }}" method="post" class="ajax-pay" action="{{ route('payments.pay_now', Qs::hash($uc->id)) }}">
                                    @csrf
                                    <div class="form-row align-items-center no-gutters">
                                        <div class="col">
                                            <input min="1" max="{{ $uc->balance ?: $uc->payment->amount }}" id="val-{{ Qs::hash($uc->id) }}" class="form-control" required placeholder="Amount" name="amt_paid" type="number">
                                        </div>
                                        <div class="col-auto ml-2">
                                            <button data-text="Pay" class="btn btn-sm btn-pay-brand" type="submit">Pay <i class="icon-paperplane ml-1"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                            @endif
                            {{-- <td>{{ $uc->ref_no }}</td> --}}
                            {{-- <td>{{ $uc->year }}</td> --}}

                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i>
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-left">

                                            @if(Qs::userIsTeamAccount())
                                            {{--Reset Payment--}}
                                            <a id="{{ Qs::hash($uc->id) }}" onclick="confirmReset(this.id)" href="#" class="dropdown-item"><i class="icon-reset"></i> Reset Payment</a>
                                            <form method="post" id="item-reset-{{ Qs::hash($uc->id) }}" action="{{ route('payments.reset_record', Qs::hash($uc->id)) }}" class="hidden">@csrf @method('delete')</form>
                                            @endif

                                            {{--Receipt--}}
                                                <a target="_blank" href="{{ route('payments.receipts', Qs::hash($uc->id)) }}" class="dropdown-item"><i class="icon-printer"></i> Print Receipt</a>
                                            {{--PDF Receipt--}}
                            {{--                    <a  href="{{ route('payments.pdf_receipts', Qs::hash($uc->id)) }}" class="dropdown-item download-receipt"><i class="icon-download"></i> Download Receipt</a>--}}

                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="all-cl">
                <table class="table table-bordered table-payment-records table-responsive">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Amount ({{ Qs::getCurrency() }})</th>
                        <th>Paid</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cleared as $cl)
                        <tr>
                            <td>{{ $cl->payment->title }}</td>
                            <td class="font-weight-bold">{{ $cl->payment->amount }}</td>
                            <td class="text-success font-weight-bold">{{ $cl->amt_paid ?: $cl->payment->amount }}</td>
                            <td>{{ optional($cl->updated_at)->format('d M Y') ?: '—' }}</td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            <a target="_blank" href="{{ route('payments.receipts', Qs::hash($cl->id)) }}" class="dropdown-item"><i class="icon-printer"></i> Print Receipt</a>
                                            @if(Qs::userIsTeamAccount())
                                            <a id="{{ Qs::hash($cl->id) }}" onclick="confirmReset(this.id)" href="#" class="dropdown-item"><i class="icon-reset"></i> Reset Payment</a>
                                            <form method="post" id="item-reset-{{ Qs::hash($cl->id) }}" action="{{ route('payments.reset_record', Qs::hash($cl->id)) }}" class="hidden">@csrf @method('delete')</form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
        </div>
        </div>
    </div>

    {{--Payments Invoice List Ends--}}

@endsection
