@extends('layouts.master')
@section('page_title', 'Edit Grade')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Edit Grade</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <form method="post" action="{{ request()->root() . '/grades/' . $gr->id }}">
                        @csrf @method('PUT')
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input name="name" value="{{ $gr->name }}" required type="text" class="form-control" placeholder="Eg. C4">
                            </div>
                        </div>

                        {{-- Grade Type (hidden to keep form minimalist) --}}
                        <input type="hidden" name="class_type_id" value="{{ $gr->class_type_id }}">

                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Mark From <span class="text-danger">*</span></label>
                            <div class="col-lg-3">
                                <input name="mark_from" min="0" max="100" value="{{ $gr->mark_from }}" required type="number" class="form-control" placeholder="0">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Mark To <span class="text-danger">*</span></label>
                            <div class="col-lg-3">
                                <input name="mark_to" min="0" max="100" value="{{ $gr->mark_to }}" required type="number" class="form-control" placeholder="0">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="remark" class="col-lg-3 col-form-label font-weight-semibold">Remark</label>
                            <div class="col-lg-9">
                                <input type="text" name="remark" id="remark" value="{{ old('remark', $gr->remark) }}" class="form-control" placeholder="e.g. Excellent or Aad u Wanaagsan">
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary" style="background-color: #D32F2F; border-color: #D32F2F;">Submit form <i class="icon-paperplane ml-2"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{--Grade Edit Ends--}}

@endsection
