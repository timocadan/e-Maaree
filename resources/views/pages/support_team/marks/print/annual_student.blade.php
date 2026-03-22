@php
    $classLabel = strtoupper(trim($my_class->name . (optional($sr->section)->name ? ' · ' . $sr->section->name : '')));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Annual Result — {{ $sr->user->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/my_print.css') }}"/>
    @include('pages.support_team.marks.print.partials.master_print_styles')
</head>
<body class="emaaree-print-body">
<div class="emaaree-print-wrap">
    @include('pages.support_team.marks.print.partials.master_letterhead')

    <p class="emaaree-doc-title">Annual progress report</p>

    <div class="emaaree-meta-lines" role="presentation">
        <div class="meta-row">
            <div class="meta-cell meta-inline">
                <span class="meta-k">Name</span> {{ strtoupper($sr->user->name) }}
                <span class="meta-sep">|</span>
                <span class="meta-k">ID</span> {{ $sr->adm_no ?? '—' }}
                <span class="meta-sep">|</span>
                <span class="meta-k">Class</span> {{ $classLabel ?: '—' }}
            </div>
            <div class="meta-cell meta-inline">
                <span class="meta-k">Year</span> {{ $year }}
                <span class="meta-sep">|</span>
                <span class="meta-k">Term</span> Annual
                <span class="meta-sep">|</span>
                <span class="meta-k">Rank</span>
                <span class="emaaree-rank-final">{!! isset($summary['rank']) && $summary['rank'] !== null ? Mk::getSuffix((int) $summary['rank']) : '—' !!}</span>
            </div>
        </div>
    </div>

    <table class="emaaree-results-table">
        <thead>
        <tr>
            <th>Subject</th>
            <th>Term 1</th>
            <th>Term 2</th>
            <th>Total</th>
            <th>Average</th>
            <th>Rank</th>
        </tr>
        </thead>
        <tbody>
        @foreach($subject_rows as $r)
            <tr>
                <td class="td-subject">{{ strtoupper($r['subject']->name) }}</td>
                <td class="td-num">{{ $r['t1'] !== null ? $r['t1'] : '—' }}</td>
                <td class="td-num">{{ $r['t2'] !== null ? $r['t2'] : '—' }}</td>
                <td class="td-num">{{ isset($r['total']) && $r['total'] !== null ? $r['total'] : '—' }}</td>
                <td class="td-num">{{ $r['avg'] !== null ? $r['avg'] : '—' }}</td>
                <td class="td-num">{!! isset($r['sub_rank']) && $r['sub_rank'] !== null ? Mk::getSuffix((int) $r['sub_rank']) : '—' !!}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="emaaree-summary-bar">
        <div class="emaaree-summary-bar-inner">
            <span class="emaaree-summary-title">Annual result summary</span>
            <div class="emaaree-summary-metrics">
                <div><span class="sk">Total scores</span><span class="sv">{{ $cum_total ?? '—' }}</span></div>
                <div><span class="sk">Average</span><span class="sv">{{ $summary['annual_avg'] ?? '—' }}</span></div>
                <div><span class="sk">Grade</span><span class="sv">{{ optional($annual_grade)->name ?? '—' }}</span></div>
            </div>
        </div>
    </div>

    <table class="emaaree-sig-footer" role="presentation">
        <tr>
            <td style="width:55%;">&nbsp;</td>
            <td style="text-align:right;padding-top:8px;">
                <div class="emaaree-sig-line">Principal&rsquo;s signature</div>
            </td>
        </tr>
    </table>
</div>
<script>window.print();</script>
</body>
</html>
