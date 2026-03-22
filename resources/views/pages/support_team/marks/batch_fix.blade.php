@extends('layouts.master')
@section('page_title', 'Fix Mark Errors')
@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title"><i class="icon-wrench mr-2"></i> Batch Fix </h5>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <form class="ajax-update" method="post" action="{{ route('marks.batch_update') }}">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-md-10">
                        <fieldset>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="term" class="col-form-label font-weight-bold">Term:</label>
                                        <select required id="term" name="term" class="form-control select">
                                            @foreach($terms ?? [1 => 'Term 1', 2 => 'Term 2'] as $val => $label)
                                                <option {{ ($selected && isset($term) && $term == $val) ? 'selected' : '' }} value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="my_class_id" class="col-form-label font-weight-bold">Class:</label>
                                        <select required onchange="getClassSections(this.value)" id="my_class_id" name="my_class_id" class="form-control select">
                                            <option value="">Select Class</option>
                                            @foreach($my_classes as $c)
                                                <option {{ ($selected && $my_class_id == $c->id) ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="section_id" class="col-form-label font-weight-bold">Section:</label>
                                        <select required id="section_id" name="section_id" data-placeholder="Select Class First" class="form-control select">
                                            @if($selected)
                                                @foreach($sections->where('my_class_id', $my_class_id) as $s)
                                                    <option {{ $section_id == $s->id ? 'selected' : '' }} value="{{ $s->id }}">{{ $s->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>

                            </div>

                        </fieldset>
                    </div>

                    <div class="col-md-2 mt-4">
                        <div class="text-right mt-1">
                            <button type="submit" class="btn btn-danger">Fix Errors <i class="icon-wrench2 ml-2"></i></button>
                        </div>
                    </div>

                </div>

            </form>

        </div>
    </div>
@endsection
