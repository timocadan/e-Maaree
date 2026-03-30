<html>
<head>
    <title>OFFICIAL ANNUAL PROGRESS ROSTER</title>
</head>
<body>
@include('pages.support_team.marks.print.partials.draft_watermark', ['draft_watermark' => $draft_watermark ?? false])
<div class="container">
    <div id="print">
        <style>
            @media print {
                @page { size: A4 landscape; margin: 10mm; }
                html, body {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                    color-adjust: exact !important;
                }
                #annual-roster,
                #annual-roster thead th,
                #annual-roster tbody td,
                #annual-roster tr.annual-summary-row td {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                    color-adjust: exact !important;
                }
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

            /* Zone 1 — Identity (Charcoal) */
            #annual-roster thead th.head-zone-identity {
                background-color: #343a40 !important;
                color: #FFFFFF !important;
                font-weight: 800 !important;
                border: 1px solid #FFFFFF !important;
                vertical-align: middle !important;
            }

            /* Zone 2 — Subjects (Navy) */
            #annual-roster thead th.head-zone-subject {
                background-color: #002147 !important;
                color: #FFFFFF !important;
                font-weight: 800 !important;
                border: 1px solid #FFFFFF !important;
                white-space: nowrap;
                vertical-align: middle !important;
            }

            /* Zone 3 — Results */
            #annual-roster thead th.head-zone-results {
                background-color: #8b0000 !important;
                color: #FFFFFF !important;
                font-weight: 800 !important;
                border: 1px solid #FFFFFF !important;
                white-space: nowrap;
                vertical-align: middle !important;
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
            }

            #annual-roster tr.annual-summary-row .mark-low {
                color: #D32F2F;
                font-weight: 800 !important;
            }

            #annual-roster tr.annual-summary-row .mark-high {
                color: #0B3D2E;
                font-weight: 800 !important;
            }

            #annual-roster .annual-rank-good {
                font-weight: 800 !important;
                color: #15803d !important;
            }

            #annual-roster .annual-rank-risk {
                font-weight: 800 !important;
                color: #D32F2F !important;
            }

            #annual-roster .annual-rank-neutral {
                font-weight: 800 !important;
                color: #374151 !important;
            }
        </style>

        <div style="background-color:#002147; padding:6px 14px; text-align:center; margin-bottom:10px;">
            <div style="color:#FFFFFF; font-weight:900; font-size:32px; text-transform:uppercase; line-height:1.05; display:inline-block; padding-bottom:4px; border-bottom:3px solid #FFFFFF;">
                {{ strtoupper(Qs::getSystemName()) }}
            </div>
            @php
                $address = Qs::getSetting('address') ?? '';
                $phone = Qs::getSetting('phone') ?? '';
                $phone2 = Qs::getSetting('phone2') ?? '';
                $email = Qs::getSetting('system_email') ?? '';
                $telParts = array_values(array_filter([$phone, $phone2], function ($v) {
                    return $v !== null && $v !== '';
                }));
                $telLine = implode(', ', $telParts);
            @endphp
            <div style="color:#FFFFFF; font-weight:600; font-size:12pt; margin-top:6px;">
                <span style="opacity:0.95;">{{ $address }}</span>
                <span> &nbsp;|&nbsp; </span>
                <span>Tel: {{ $telLine }}</span>
                <span> &nbsp;|&nbsp; </span>
                <span>{{ $email }}</span>
            </div>
        </div>

        <div style="text-align:center; font-weight:900; font-size:14pt; margin:0 0 16px 0;">
            OFFICIAL ANNUAL PROGRESS ROSTER
        </div>

        <div style="width:100%; display:flex; justify-content:space-between; margin-bottom:10px; gap:10px;">
            <div style="flex:1;">
                <div style="font-size:12pt; font-weight:800; color:#000000;"><strong>Class:</strong> {{ $my_class->name }}</div>
                <div style="font-size:12pt; font-weight:800; color:#000000;"><strong>Section:</strong> {{ $section->name }}</div>
                <div style="font-size:12pt; font-weight:800; color:#000000;"><strong>Academic Year:</strong> {{ $year }}</div>
            </div>
            <div style="flex:1; text-align:right;">
                <div style="font-size:12pt; font-weight:800; color:#000000;"><strong>Form Master:</strong> {{ optional($my_class->teacher)->name ?? '-' }}</div>
            </div>
        </div>

        <div style="position: relative; text-align: center;">
            @if(isset($s['logo']))
                <img src="{{ $s['logo'] }}"
                     style="max-width: 500px; max-height:600px; margin-top: 60px; position:absolute; opacity: 0.2; margin-left: auto; margin-right: auto; left: 0; right: 0;" />
            @endif
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
                <th class="head-zone-results">GRAND TOTAL</th>
                <th class="head-zone-results">AVERAGE</th>
                <th class="head-zone-results">RANK</th>
            </tr>
            </thead>
            <tbody>
            @foreach(($roster_rows ?? []) as $row)
                @php
                    $rank1 = $row['rank_term1'] ?? null;
                    $rank2 = $row['rank_term2'] ?? null;
                    $rankFinal = $row['rank'] ?? null;
                    $annualAvg = $row['annual_avg'] ?? null;
                    $term1Total = $row['term1_total'] ?? null;
                    $term2Total = $row['term2_total'] ?? null;
                    $term1Avg = $row['term1_avg'] ?? null;
                    $term2Avg = $row['term2_avg'] ?? null;
                    $cumTotal = ($term1Total !== null || $term2Total !== null) ? ((int)($term1Total ?? 0) + (int)($term2Total ?? 0)) : null;
                    if ($annualAvg !== null) {
                        $rankPerfClass = $annualAvg >= 50 ? 'annual-rank-good' : 'annual-rank-risk';
                    } else {
                        $rankPerfClass = 'annual-rank-neutral';
                    }
                @endphp
                <tr class="term-row">
                    <td rowspan="3" class="identity-cell">{{ $loop->iteration }}</td>
                    <td rowspan="3" class="identity-cell identity-name">{{ $row['name'] }}</td>
                    <td rowspan="3" class="identity-cell">{{ $row['sex'] ?? '-' }}</td>
                    <td rowspan="3" class="identity-cell">{{ $row['adm_no'] ?? '-' }}</td>
                    <td class="period-cell">Term 1</td>
                    @foreach($subjects as $sub)
                        @php $v = $row['sem1'][$sub->id] ?? null; @endphp
                        <td class="{{ $v !== null && $v < 50 ? 'mark-low' : ($v !== null && $v > 90 ? 'mark-high' : '') }}">{{ $v !== null ? $v : '-' }}</td>
                    @endforeach
                    <td>{{ $term1Total !== null ? $term1Total : '-' }}</td>
                    <td>{{ $term1Avg !== null ? $term1Avg : '-' }}</td>
                    <td>{!! $rank1 !== null ? Mk::getSuffix((int)$rank1) : '-' !!}</td>
                </tr>
                <tr class="term-row">
                    <td class="period-cell">Term 2</td>
                    @foreach($subjects as $sub)
                        @php $v = $row['sem2'][$sub->id] ?? null; @endphp
                        <td class="{{ $v !== null && $v < 50 ? 'mark-low' : ($v !== null && $v > 90 ? 'mark-high' : '') }}">{{ $v !== null ? $v : '-' }}</td>
                    @endforeach
                    <td>{{ $term2Total !== null ? $term2Total : '-' }}</td>
                    <td>{{ $term2Avg !== null ? $term2Avg : '-' }}</td>
                    <td>{!! $rank2 !== null ? Mk::getSuffix((int)$rank2) : '-' !!}</td>
                </tr>
                <tr class="annual-summary-row">
                    <td class="period-cell">Average</td>
                    @foreach($subjects as $sub)
                        @php $v = $row['avg'][$sub->id] ?? null; @endphp
                        <td class="{{ $v !== null && $v < 50 ? 'mark-low' : ($v !== null && $v > 90 ? 'mark-high' : '') }}">{{ $v !== null ? $v : '-' }}</td>
                    @endforeach
                    <td>{{ $cumTotal !== null ? $cumTotal : '-' }}</td>
                    <td>{{ $annualAvg !== null ? $annualAvg : '-' }}</td>
                    <td class="{{ $rankPerfClass }}">{!! $rankFinal !== null ? Mk::getSuffix((int)$rankFinal) : '-' !!}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>
</div>

<script>
    window.print();
</script>
</body>
</html>
