@extends('layouts.master')
@section('page_title', 'Student Marksheet')
@section('content')

    <div class="card">
        <div class="card-header text-center">
            <h4 class="card-title font-weight-bold">Student marksheet — {{ $sr->user->name }}</h4>
        </div>
    </div>

    @foreach($terms ?? [1 => 'Term 1', 2 => 'Term 2'] as $termNum => $termLabel)
        @foreach($exam_records->where('term', $termNum) as $exr)

                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="font-weight-bold">{{ $termLabel.' - '.$year }}</h6>
                        {!! Qs::getPanelOptions() !!}
                    </div>

                    <div class="card-body collapse">

                        {{--Sheet Table--}}
                        @include('pages.support_team.marks.show.sheet', ['termNum' => $termNum])

                        {{--Print Button--}}
                        <div class="text-center mt-3">
                            <a target="_blank" href="{{ route('marks.print', [Qs::hash($student_id), $termNum, $year]) }}" class="btn btn-secondary btn-lg">Print Marksheet <i class="icon-printer ml-2"></i></a>
                        </div>

                    </div>

                </div>

        @endforeach
    @endforeach

@endsection
