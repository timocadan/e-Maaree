@php
    $slots = $slots ?? [];
    $slots = is_array($slots) ? $slots : [];
@endphp

<div class="table-responsive w-100">
<table class="table table-bordered w-100 text-center marksheet-web-table">
    <thead class="marksheet-web-head">
    <tr>
        <th style="width: 4%;">S/N</th>
        <th>SUBJECT</th>
        @foreach($slots as $slot)
            <th style="white-space: nowrap;">
                {{ strtoupper($slot['label'] ?? 'ASSESSMENT') }}
                <div style="font-size: 11px; opacity: 0.9;">({{ (int)($slot['max'] ?? 0) }})</div>
            </th>
        @endforeach
        <th style="white-space: nowrap;">TOTAL<div style="font-size: 11px; opacity: 0.9;">(100%)</div></th>
        <th style="white-space: nowrap;">GRADE</th>
    </tr>
    </thead>

    <tbody>
    @php $termNum = $termNum ?? $exr->term ?? 1; @endphp
    @foreach($subjects as $sub)
        @php
            $mk = $marks->where('subject_id', $sub->id)->where('term', $termNum)->first();
            $texField = 'tex' . (int) $termNum;
            $totalScore = ($mk && isset($mk->$texField) && $mk->$texField !== null && $mk->$texField !== '') ? $mk->$texField : null;
            $gradeName = ($mk && isset($computed_grade_by_mark_id[$mk->id])) ? $computed_grade_by_mark_id[$mk->id] : (($mk && $mk->grade) ? $mk->grade->name : 'N/A');
        @endphp
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td class="text-left" style="font-weight: 700;">{{ strtoupper($sub->name) }}</td>
            @foreach($slots as $slot)
                @php $k = $slot['key'] ?? ''; @endphp
                <td>
                    {{ ($mk && $k && isset($mk->$k) && $mk->$k !== null && $mk->$k !== '') ? $mk->$k : 'N/A' }}
                </td>
            @endforeach
            <td style="font-weight: 800;">{{ $totalScore !== null ? $totalScore : 'N/A' }}</td>
            <td>{{ $gradeName }}</td>
        </tr>
    @endforeach
    <tr class="marksheet-web-summary">
        <td colspan="{{ 3 + count($slots) }}" class="text-left pl-3">
            <strong>Total scores:</strong> {{ $exr->total ?? 'N/A' }}
            &nbsp;&nbsp;&nbsp;
            <strong>Average:</strong> {{ $exr->ave ?? 'N/A' }}
        </td>
    </tr>
    </tbody>
</table>
</div>

<style>
    .marksheet-web-table th,
    .marksheet-web-table td {
        vertical-align: middle !important;
        padding-top: 0.38rem !important;
        padding-bottom: 0.38rem !important;
        font-size: 13px;
    }
    .marksheet-web-head th {
        background: #002147 !important;
        color: #fff !important;
        font-weight: 700;
        border-color: #002147 !important;
    }
    .marksheet-web-summary td {
        background: #f1f3f5;
        font-weight: 700;
    }
</style>
