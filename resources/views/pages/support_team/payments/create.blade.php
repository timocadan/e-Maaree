@extends('layouts.master')
@section('page_title', 'Create Payment')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Create Payment</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <form class="ajax-store" method="post" action="{{ route('payments.store') }}">
                        @csrf
                        {{-- Row 1: Title and Amount (ETB) side-by-side --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-semibold">Title <span class="text-danger">*</span></label>
                                    <input name="title" value="{{ old('title') }}" required type="text" class="form-control" placeholder="Eg. School Fees">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount" class="font-weight-semibold">Amount ({{ Qs::getCurrency() }}) <span class="text-danger">*</span></label>
                                    <input class="form-control" value="{{ old('amount') }}" required name="amount" id="amount" type="number" placeholder="0">
                                </div>
                            </div>
                        </div>

                        {{-- Row 2: Class and Payment Method side-by-side --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="my_class_id" class="font-weight-semibold">Class</label>
                                    <select class="form-control select-search" name="my_class_id" id="my_class_id">
                                        <option value="">All Classes</option>
                                        @foreach($my_classes as $c)
                                            <option {{ old('my_class_id') == $c->id ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="method" class="font-weight-semibold">Payment Method</label>
                                    <select class="form-control select" name="method" id="method">
                                        @foreach(Qs::getPaymentMethods() as $pm)
                                            <option value="{{ $pm }}" {{ old('method', 'Cash') == $pm ? 'selected' : '' }}>{{ $pm }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description" class="font-weight-semibold">Description</label>
                            <input class="form-control" value="{{ old('description') }}" name="description" id="description" type="text" placeholder="Optional">
                        </div>

                        <div class="text-right mt-4">
                            <button type="submit" class="btn btn-danger">Submit <i class="icon-paperplane ml-2"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{--Payment Create Ends--}}

@endsection
