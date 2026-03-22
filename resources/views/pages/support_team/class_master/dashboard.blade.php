@extends('layouts.master')
@section('page_title', 'Class Master Dashboard')
@section('content')
    <div class="card">
        <div class="card-header header-elements-inline" style="background-color: #1A1A1A;">
            <h6 class="card-title font-weight-bold text-white"><i class="icon-users4 mr-2 text-white"></i> Class Master Dashboard</h6>
            {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            @if($form_master_classes->isEmpty())
                <p class="text-muted mb-0">You are not assigned as Form Master to any class. Contact admin to set your class.</p>
            @else
                <form method="get" action="{{ route('class_master.dashboard') }}" class="mb-3 row align-items-end">
                    <div class="col-md-3 form-group mb-0">
                        <label class="font-weight-semibold">Class</label>
                        <select name="class_id" class="form-control form-control-sm select" onchange="this.form.submit()">
                            @foreach($form_master_classes as $c)
                                <option value="{{ $c->id }}" {{ (isset($class_id) && $class_id == $c->id) ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-0">
                        <label class="font-weight-semibold">Section</label>
                        <select name="section_id" class="form-control form-control-sm select" onchange="this.form.submit()">
                            <option value="">—</option>
                            @foreach($sections as $sec)
                                <option value="{{ $sec->id }}" {{ (isset($section_id) && $section_id == $sec->id) ? 'selected' : '' }}>{{ $sec->name ?? 'Section' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-0">
                        <label class="font-weight-semibold">Term</label>
                        <select name="term" class="form-control form-control-sm select" onchange="this.form.submit()">
                            @foreach($terms ?? [1 => 'Term 1', 2 => 'Term 2'] as $val => $label)
                                <option value="{{ $val }}" {{ (isset($term) && $term == $val) ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="year" value="{{ $year ?? '' }}">
                </form>

                @if(isset($term) && $class_id && $section_id && $students->isNotEmpty())
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <span class="text-muted small">{{ $my_class->name ?? '' }} · {{ $terms[$term] ?? 'Term '.$term }} · {{ $year ?? '' }}</span>
                        <form method="post" action="{{ route('class_master.generate_ranks') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="class_id" value="{{ $class_id }}">
                            <input type="hidden" name="section_id" value="{{ $section_id }}">
                            <input type="hidden" name="term" value="{{ $term }}">
                            <button type="submit" class="btn btn-sm" style="background-color: #D32F2F; color: #fff; border-color: #D32F2F;">Generate class ranks</button>
                        </form>
                    </div>
                    <style>
                        .class-master-table { width: 100%; border-collapse: collapse; }
                        .class-master-table thead th { background-color: #002147; color: #fff; font-weight: 600; padding: 10px 8px; text-align: left; }
                        .class-master-table thead th.col-pos { text-align: center; }
                        .class-master-table thead th.col-total { text-align: right; }
                        .class-master-table tbody tr { border-bottom: 1px solid #eee; }
                        .class-master-table tbody td { padding: 8px; }
                        .class-master-table tbody td.col-pos { text-align: center; font-weight: 600; }
                        .class-master-table tbody td.col-total { text-align: right; }
                    </style>
                    <table class="table class-master-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">S/N</th>
                                <th>Name</th>
                                <th style="width: 120px;">ADM No</th>
                                <th class="col-total" style="width: 100px;">Grand total</th>
                                <th class="col-pos" style="width: 90px;">Position</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $idx => $st)
                                @php
                                    $uid = $st->user_id;
                                    $gt = $grand_totals[$uid] ?? 0;
                                    $exr = $exam_records[$uid] ?? null;
                                    $classPos = $exr && isset($exr->class_pos) ? $exr->class_pos : '—';
                                @endphp
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $st->user->name ?? '—' }}</td>
                                    <td>{{ $st->adm_no ?? '—' }}</td>
                                    <td class="col-total">{{ $gt }}</td>
                                    <td class="col-pos">{{ $classPos }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @elseif(isset($term) && $class_id && $section_id)
                    <p class="text-muted mb-0">No students in this class/section for the selected term.</p>
                @else
                    <p class="text-muted mb-0">Select class, section, and term to view aggregated totals and generate ranks.</p>
                @endif
            @endif
        </div>
    </div>
@endsection
