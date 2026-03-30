@php
    $classLabel = strtoupper(trim($my_class->name . (optional($sr->section)->name ? ' · ' . $sr->section->name : '')));
    $slots = $mark_slots ?? (new \App\Models\MarkTemplate())->slotsForDisplay();
    $termLabel = strip_tags(Mk::getSuffix($term));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Progress Report — {{ $sr->user->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/my_print.css') }}"/>
    @include('pages.support_team.marks.print.partials.master_print_styles')
    @include('pages.support_team.marks.print.partials.term_marksheet_polish')
</head>
<body class="emaaree-print-body term-progress-report">
@include('pages.support_team.marks.print.partials.draft_watermark', ['draft_watermark' => $draft_watermark ?? false])
<div class="emaaree-print-wrap">
    @include('pages.support_team.marks.print.partials.master_letterhead')

    <p class="emaaree-doc-title">Progress report</p>

    <div class="emaaree-meta-dashboard" role="region" aria-label="Student and term details">
        <div class="emaaree-meta-strip">
            <div class="emaaree-meta-left">
                <span class="meta-k">Name:</span> <span class="meta-v">{{ strtoupper($sr->user->name) }}</span>
                <span class="meta-sep">|</span>
                <span class="meta-k">ID:</span> <span class="meta-v">{{ $sr->adm_no ?? '—' }}</span>
                <span class="meta-sep">|</span>
                <span class="meta-k">Class:</span> <span class="meta-v">{{ $classLabel ?: '—' }}</span>
            </div>
            <div class="emaaree-meta-right">
                <span class="meta-k">Year:</span> <span class="meta-v">{{ $year }}</span>
                <span class="meta-sep">|</span>
                <span class="meta-k">Term:</span> <span class="meta-v">{{ $termLabel }}</span>
                <span class="meta-sep">|</span>
                <span class="meta-k">Rank:</span>
                <span class="emaaree-rank-final">{!! isset($exr->pos) && $exr->pos !== null ? Mk::getSuffix((int) $exr->pos) : '—' !!}</span>
            </div>
        </div>
    </div>

    <table class="emaaree-results-table">
        <thead>
        <tr>
            <th>Subject</th>
            @foreach($slots as $slot)
                <th>
                    {{ strtoupper($slot['label'] ?? 'Assessment') }}
                    <span class="emaaree-slot-sub">({{ (int)($slot['max'] ?? 0) }})</span>
                </th>
            @endforeach
            <th>Total<span class="emaaree-slot-sub">(100%)</span></th>
            <th>Grade</th>
        </tr>
        </thead>
        <tbody>
        @foreach($subjects as $sub)
            @php
                $mk = $marks->where('subject_id', $sub->id)->where('term', $term)->first();
            @endphp
            <tr>
                <td class="td-subject">{{ strtoupper($sub->name) }}</td>
                @foreach($slots as $slot)
                    @php $k = $slot['key'] ?? ''; @endphp
                    <td class="td-num">{{ ($mk && $k && isset($mk->$k) && $mk->$k !== null && $mk->$k !== '') ? $mk->$k : '—' }}</td>
                @endforeach
                <td class="td-num">{{ ($mk && isset($mk->$tex) && $mk->$tex !== null && $mk->$tex !== '') ? $mk->$tex : '—' }}</td>
                <td class="td-num">{{ ($mk && $mk->grade) ? $mk->grade->name : '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="emaaree-summary-bar">
        <div class="emaaree-summary-bar-inner">
            <span class="emaaree-summary-title">Term result summary</span>
            <div class="emaaree-summary-metrics">
                <div><span class="sk">Total scores</span><span class="sv">{{ $exr->total ?? '—' }}</span></div>
                <div><span class="sk">Average</span><span class="sv">{{ $exr->ave ?? '—' }}</span></div>
                <div><span class="sk">Grade</span><span class="sv">{{ optional($term_grade)->name ?? '—' }}</span></div>
            </div>
        </div>
    </div>

    <table class="emaaree-sig-footer" role="presentation" style="width:100%;">
        <tr>
            <td style="text-align:right;vertical-align:bottom;padding-top:12px;">
                <div class="emaaree-sig-line">Principal&rsquo;s signature</div>
            </td>
        </tr>
    </table>
</div>
<script>window.print();</script>
</body>
</html>
