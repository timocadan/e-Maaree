<table class="table table-bordered table-responsive text-center">
    <thead>
    <tr>
        <th rowspan="2">S/N</th>
        <th rowspan="2">SUBJECTS</th>
        <th rowspan="2">CA1<br>(20)</th>
        <th rowspan="2">CA2<br>(20)</th>
        <th rowspan="2">EXAMS<br>(60)</th>
        <th rowspan="2">TOTAL<br>(100)</th>

        {{--@if($ex->term == 3) --}}{{-- 3rd Term --}}{{--
        <th rowspan="2">TOTAL <br>(100%) 3<sup>RD</sup> TERM</th>
        <th rowspan="2">1<sup>ST</sup> <br> TERM</th>
        <th rowspan="2">2<sup>ND</sup> <br> TERM</th>
        <th rowspan="2">CUM (300%) <br> 1<sup>ST</sup> + 2<sup>ND</sup> + 3<sup>RD</sup></th>
        <th rowspan="2">CUM AVE</th>
        @endif--}}

        <th rowspan="2">GRADE</th>
        <th rowspan="2">SUBJECT <br> POSITION</th>
    </tr>
    </thead>

    <tbody>
    @php $termNum = $termNum ?? $exr->term ?? 1; @endphp
    @foreach($subjects as $sub)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $sub->name }}</td>
            @foreach($marks->where('subject_id', $sub->id)->where('term', $termNum) as $mk)
                <td>{{ ($mk->t1) ?: '-' }}</td>
                <td>{{ ($mk->t2) ?: '-' }}</td>
                <td>{{ ($mk->exm) ?: '-' }}</td>
                <td>
                    @if($termNum == 1) {{ ($mk->tex1) }}
                    @elseif($termNum == 2) {{ ($mk->tex2) }}
                    @elseif($termNum == 3) {{ ($mk->tex3) }}
                    @else {{ '-' }}
                    @endif
                </td>

                {{--3rd Term--}}
                {{-- @if($ex->term == 3)
                     <td>{{ $mk->tex3 ?: '-' }}</td>
                     <td>{{ Mk::getSubTotalTerm($student_id, $sub->id, 1, $mk->my_class_id, $year) }}</td>
                     <td>{{ Mk::getSubTotalTerm($student_id, $sub->id, 2, $mk->my_class_id, $year) }}</td>
                     <td>{{ $mk->cum ?: '-' }}</td>
                     <td>{{ $mk->cum_ave ?: '-' }}</td>
                 @endif--}}

                <td>{{ ($mk->grade) ? $mk->grade->name : '-' }}</td>
                <td>{!! ($mk->grade) ? Mk::getSuffix($mk->sub_pos) : '-' !!}</td>
            @endforeach
        </tr>
    @endforeach
    <tr>
        <td colspan="8" class="text-left pl-3">
            <strong>Total scores:</strong> {{ $exr->total }}
            &nbsp;&nbsp;&nbsp;
            <strong>Average:</strong> {{ $exr->ave }}
        </td>
    </tr>
    </tbody>
</table>
