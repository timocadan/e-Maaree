@php
    $row = $roster_row;
    $rankFinal = $row['rank'] ?? null;
    $rankTerm1 = $row['rank_term1'] ?? null;
    $rankTerm2 = $row['rank_term2'] ?? null;
    $annualAvg = $row['annual_avg'] ?? null;
    $term1Total = $row['term1_total'] ?? null;
    $term2Total = $row['term2_total'] ?? null;
    $term1Avg = $row['term1_avg'] ?? null;
    $term2Avg = $row['term2_avg'] ?? null;
    $cumTotal = ($term1Total !== null || $term2Total !== null) ? ((int) ($term1Total ?? 0) + (int) ($term2Total ?? 0)) : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Annual Student Progress Report — {{ $row['name'] }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/my_print.css') }}"/>
    <style>
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            html, body.emaaree-print-body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            #annual-roster,
            #annual-roster thead th,
            #annual-roster tbody td,
            #annual-roster tr.annual-summary-row td,
            .emaaree-navy-bar {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }

        body.emaaree-print-body {
            margin: 0;
            padding: 12px 16px 24px;
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #111827;
            background: #fff;
        }

        /* Master letterhead — large bold caps + navy bar (aligned with print stack) */
        body.emaaree-print-body .emaaree-school-title {
            margin: 0;
            padding: 0 0 10px;
            font-size: 28pt;
            font-weight: 900;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            text-align: center;
            color: #111827;
            border-bottom: 3px solid #002147;
            line-height: 1.08;
        }
        body.emaaree-print-body .emaaree-navy-bar {
            background: #002147;
            color: #fff;
            text-align: center;
            font-weight: 600;
            font-size: 8pt;
            padding: 6px 14px;
            line-height: 1.5;
            letter-spacing: 0.03em;
        }

        .annual-doc-title {
            text-align: center;
            font-weight: 900;
            font-size: 14pt;
            letter-spacing: 0.06em;
            margin: 18px 0 16px 0;
            color: #111827;
        }

        .annual-meta-row {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            gap: 10px;
            flex-wrap: wrap;
        }
        .annual-meta-row .blk {
            font-size: 11pt;
            font-weight: 800;
            color: #000000;
        }
        .annual-meta-row .blk--form-master {
            flex: 1.2;
            min-width: 240px;
            text-align: right;
        }
        .annual-form-master-name {
            font-weight: 900;
            color: #000000;
        }
        .annual-form-master-placeholder {
            display: inline-block;
            min-width: 220px;
            margin-left: 6px;
            vertical-align: baseline;
            border-bottom: 1px dotted #374151;
            padding-bottom: 3px;
            min-height: 1.1em;
        }

        #annual-roster {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #FFFFFF;
            table-layout: fixed;
            font-size: 9pt;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        #annual-roster th, #annual-roster td {
            border: 1px solid #dee2e6;
            padding: 8px 6px;
            text-align: center !important;
            vertical-align: middle !important;
            color: #111827;
            word-break: break-word;
        }

        #annual-roster thead th.head-zone-identity {
            background-color: #343a40 !important;
            color: #FFFFFF !important;
            font-weight: 800 !important;
            border: 1px solid #FFFFFF !important;
            vertical-align: middle !important;
        }

        #annual-roster thead th.head-zone-subject {
            background-color: #002147 !important;
            color: #FFFFFF !important;
            font-weight: 800 !important;
            border: 1px solid #FFFFFF !important;
            white-space: nowrap;
            vertical-align: middle !important;
        }

        #annual-roster thead th.head-zone-results {
            background-color: #8b0000 !important;
            color: #FFFFFF !important;
            font-weight: 800 !important;
            border: 1px solid #FFFFFF !important;
            vertical-align: middle !important;
            line-height: 1.2;
        }
        #annual-roster thead th.head-zone-results.th-results-wrap {
            white-space: normal;
            max-width: 56px;
            padding: 6px 4px;
            font-size: 7.5pt;
        }
        /* AVERAGE: one line only (do not split the word) */
        #annual-roster thead th.head-zone-results.th-results-average {
            white-space: nowrap;
            padding: 6px 8px;
            font-size: 7.5pt;
        }

        #annual-roster tbody tr { background-color: #fff !important; }

        #annual-roster .identity-cell {
            font-weight: 500;
            vertical-align: middle !important;
        }

        #annual-roster td.identity-cell.identity-name {
            text-align: left !important;
            padding-left: 8px;
        }

        #annual-roster tr.term-row td {
            font-weight: 400;
        }

        #annual-roster tr.term-row .period-cell {
            background-color: #f3f4f6;
            font-weight: 500;
        }

        #annual-roster tr.term-row .mark-low {
            color: #D32F2F;
            font-weight: 400;
        }

        #annual-roster tr.term-row .mark-high {
            color: #0B3D2E;
            font-weight: 400;
        }

        #annual-roster tr.annual-summary-row td {
            background-color: #ebedef !important;
            font-weight: 800 !important;
            text-align: center !important;
            vertical-align: middle !important;
            border-top: 1px solid #002147 !important;
            border-bottom: 2px solid #adb5bd !important;
            color: #111827 !important;
        }
        #annual-roster tr.annual-summary-row .period-cell {
            font-weight: 800 !important;
        }
        #annual-roster td.cell-numeric {
            text-align: center !important;
        }
        #annual-roster tr.term-row td.cell-rank {
            text-align: center !important;
            font-weight: 600;
        }

        #annual-roster tr.annual-summary-row .mark-low {
            color: #D32F2F;
            font-weight: 800 !important;
        }

        #annual-roster tr.annual-summary-row .mark-high {
            color: #0B3D2E;
            font-weight: 800 !important;
        }

        /* Final annual rank only: bold red (spec) */
        #annual-roster .annual-rank-final {
            font-weight: 800 !important;
            color: #D32F2F !important;
        }

        .annual-sig-wrap {
            margin-top: 28px;
            width: 100%;
        }
        .annual-sig-wrap td { vertical-align: bottom; }
        .annual-sig-line {
            display: inline-block;
            min-width: 220px;
            border-top: 1px solid #111827;
            padding-top: 8px;
            font-weight: 700;
            font-size: 8pt;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            text-align: center;
            color: #374151;
        }
        .print-gen-footer {
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
            margin-top: 16px;
            padding-top: 6px;
            letter-spacing: 0.04em;
        }
    </style>
</head>
<body class="emaaree-print-body">
@include('pages.support_team.marks.print.partials.draft_watermark', ['draft_watermark' => $draft_watermark ?? false])
<div class="emaaree-print-wrap">
    @include('pages.support_team.marks.print.partials.master_letterhead')

    <div class="annual-doc-title">ANNUAL STUDENT PROGRESS REPORT</div>

    <div class="annual-meta-row">
        <div class="blk" style="flex:1;">
            <div><strong>Class:</strong> {{ $my_class->name }}</div>
            <div><strong>Section:</strong> {{ optional($section)->name ?? '—' }}</div>
            <div><strong>Academic Year:</strong> {{ $year }}</div>
        </div>
        <div class="blk blk--form-master">
            <strong>Form Master:</strong>
            @if(optional($my_class->teacher)->name)
                <span class="annual-form-master-name">{{ $my_class->teacher->name }}</span>
            @else
                <span class="annual-form-master-placeholder" title="Sign manually if needed">&nbsp;</span>
            @endif
        </div>
    </div>

    <table id="annual-roster">
        <thead>
        <tr>
            <th class="head-zone-identity" style="width:40px;">S/N</th>
            <th class="head-zone-identity" style="min-width:120px;">Student Name</th>
            <th class="head-zone-identity" style="width:44px;">Sex</th>
            <th class="head-zone-identity" style="min-width:80px;">ADM_No</th>
            <th class="head-zone-identity" style="width:72px;">Period</th>
            @foreach($subjects as $sub)
                <th class="head-zone-subject">{{ strtoupper($sub->slug ?: $sub->name) }}</th>
            @endforeach
            <th class="head-zone-results th-results-wrap">GRAND<br>TOTAL</th>
            <th class="head-zone-results th-results-average">AVERAGE</th>
            <th class="head-zone-results th-results-wrap">RANK</th>
        </tr>
        </thead>
        <tbody>
        <tr class="term-row">
            <td rowspan="3" class="identity-cell">1</td>
            <td rowspan="3" class="identity-cell identity-name">{{ $row['name'] }}</td>
            <td rowspan="3" class="identity-cell">{{ $row['sex'] ?? '-' }}</td>
            <td rowspan="3" class="identity-cell">{{ $row['adm_no'] ?? '-' }}</td>
            <td class="period-cell">Term 1</td>
            @foreach($subjects as $sub)
                @php $v = $row['sem1'][$sub->id] ?? null; @endphp
                <td class="cell-numeric {{ $v !== null && $v < 50 ? 'mark-low' : ($v !== null && $v > 90 ? 'mark-high' : '') }}">{{ $v !== null ? $v : '-' }}</td>
            @endforeach
            <td class="cell-numeric">{{ $term1Total !== null ? $term1Total : '-' }}</td>
            <td class="cell-numeric">{{ $term1Avg !== null ? $term1Avg : '-' }}</td>
            <td class="cell-numeric cell-rank">{!! $rankTerm1 !== null ? Mk::getSuffix((int) $rankTerm1) : '—' !!}</td>
        </tr>
        <tr class="term-row">
            <td class="period-cell">Term 2</td>
            @foreach($subjects as $sub)
                @php $v = $row['sem2'][$sub->id] ?? null; @endphp
                <td class="cell-numeric {{ $v !== null && $v < 50 ? 'mark-low' : ($v !== null && $v > 90 ? 'mark-high' : '') }}">{{ $v !== null ? $v : '-' }}</td>
            @endforeach
            <td class="cell-numeric">{{ $term2Total !== null ? $term2Total : '-' }}</td>
            <td class="cell-numeric">{{ $term2Avg !== null ? $term2Avg : '-' }}</td>
            <td class="cell-numeric cell-rank">{!! $rankTerm2 !== null ? Mk::getSuffix((int) $rankTerm2) : '—' !!}</td>
        </tr>
        <tr class="annual-summary-row">
            <td class="period-cell">Average</td>
            @foreach($subjects as $sub)
                @php $v = $row['avg'][$sub->id] ?? null; @endphp
                <td class="cell-numeric {{ $v !== null && $v < 50 ? 'mark-low' : ($v !== null && $v > 90 ? 'mark-high' : '') }}">{{ $v !== null ? $v : '-' }}</td>
            @endforeach
            <td class="cell-numeric">{{ $cumTotal !== null ? $cumTotal : '-' }}</td>
            <td class="cell-numeric">{{ $annualAvg !== null ? $annualAvg : '-' }}</td>
            <td class="cell-numeric annual-rank-final">{!! $rankFinal !== null ? Mk::getSuffix((int) $rankFinal) : '—' !!}</td>
        </tr>
        </tbody>
    </table>

    <table class="annual-sig-wrap" role="presentation">
        <tr>
            <td style="width:58%;">&nbsp;</td>
            <td style="text-align:right;padding-top:12px;">
                <div class="annual-sig-line">Principal&rsquo;s signature</div>
            </td>
        </tr>
    </table>

    @include('pages.support_team.marks.print.partials.print_footer')
</div>
<script>window.print();</script>
</body>
</html>
