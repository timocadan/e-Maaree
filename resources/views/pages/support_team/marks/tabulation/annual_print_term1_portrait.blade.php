@php
    $slotCount = max(0, count($slots ?? []));
    $colspanSubj = $slotCount + 1;
@endphp
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TERM 1 CLASS PROGRESS — {{ $my_class->name ?? '' }}</title>
</head>
<body style="margin:0;padding:12px;font-family:'DejaVu Sans',Helvetica,Arial,sans-serif;">
@include('pages.support_team.marks.print.partials.draft_watermark', ['draft_watermark' => $draft_watermark ?? false])
<div class="container">
    <div id="print">
        <style>
            @media print {
                @page { size: A4 portrait; margin: 8mm; }
                html, body {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                    color-adjust: exact !important;
                }
                #t1-portrait-roster th, #t1-portrait-roster td {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
            }
            #t1-portrait-roster {
                width: 100%;
                border-collapse: collapse;
                font-size: 6.5pt;
                table-layout: fixed;
            }
            #t1-portrait-roster th, #t1-portrait-roster td {
                border: 1px solid #dee2e6;
                padding: 4px 3px;
                text-align: center !important;
                vertical-align: middle !important;
                word-break: break-word;
            }
            #t1-portrait-roster thead th.head-zone-identity {
                background-color: #343a40 !important;
                color: #fff !important;
                font-weight: 800 !important;
                border: 1px solid #fff !important;
            }
            #t1-portrait-roster thead th.head-zone-subject {
                background-color: #002147 !important;
                color: #fff !important;
                font-weight: 700 !important;
                border: 1px solid #fff !important;
                font-size: 6pt;
            }
            #t1-portrait-roster thead th.head-zone-results {
                background-color: #8b0000 !important;
                color: #fff !important;
                font-weight: 800 !important;
                border: 1px solid #fff !important;
                font-size: 6.5pt;
            }
            #t1-portrait-roster tbody tr:nth-child(even) { background: #fafafa; }
            #t1-portrait-roster .identity-name { text-align: left !important; padding-left: 4px !important; font-weight: 600; }
            #t1-portrait-roster .mark-low { color: #D32F2F; font-weight: 600; }
            #t1-portrait-roster .mark-high { color: #0B3D2E; }
        </style>

        <div style="background-color:#002147; padding:6px 14px; text-align:center; margin-bottom:10px;">
            <div style="color:#FFFFFF; font-weight:900; font-size:26px; text-transform:uppercase; line-height:1.05; display:inline-block; padding-bottom:4px; border-bottom:3px solid #FFFFFF;">
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
            <div style="color:#FFFFFF; font-weight:600; font-size:10pt; margin-top:6px;">
                <span style="opacity:0.95;">{{ $address }}</span>
                @if($telLine)<span> &nbsp;|&nbsp; </span><span>Tel: {{ $telLine }}</span>@endif
                @if($email)<span> &nbsp;|&nbsp; </span><span>{{ $email }}</span>@endif
            </div>
        </div>

        <div style="text-align:center; font-weight:900; font-size:12pt; margin:0 0 12px 0;">
            TERM 1 CLASS PROGRESS ROSTER <span style="font-weight:600;font-size:9pt;color:#374151;">(detailed assessments)</span>
        </div>

        <div style="width:100%; display:flex; justify-content:space-between; margin-bottom:10px; gap:10px; font-size:10pt; font-weight:800;">
            <div style="flex:1;">
                <div><strong>Class:</strong> {{ $my_class->name }}</div>
                <div><strong>Section:</strong> {{ $section->name }}</div>
                <div><strong>Academic Year:</strong> {{ $year }}</div>
            </div>
            <div style="flex:1; text-align:right;">
                <div><strong>Form Master:</strong> {{ optional($my_class->teacher)->name ?? '—' }}</div>
            </div>
        </div>

        <table id="t1-portrait-roster">
            <thead>
            <tr>
                <th class="head-zone-identity" rowspan="2" style="width:28px;">S/N</th>
                <th class="head-zone-identity" rowspan="2" style="min-width:72px;">Student</th>
                <th class="head-zone-identity" rowspan="2" style="width:28px;">Sex</th>
                <th class="head-zone-identity" rowspan="2" style="min-width:52px;">ADM</th>
                @foreach($subjects as $sub)
                    <th class="head-zone-subject" colspan="{{ $colspanSubj }}">{{ strtoupper($sub->slug ?: $sub->name) }}</th>
                @endforeach
                <th class="head-zone-results" rowspan="2"><span style="white-space:nowrap;">GRAND</span><br>TOTAL</th>
                <th class="head-zone-results" rowspan="2">AVERAGE</th>
                <th class="head-zone-results" rowspan="2">RANK</th>
            </tr>
            <tr>
                @foreach($subjects as $sub)
                    @foreach($slots as $slot)
                        <th class="head-zone-subject">{{ strtoupper($slot['label'] ?? '') }}<span style="font-size:5pt;font-weight:600;">({{ (int)($slot['max'] ?? 0) }})</span></th>
                    @endforeach
                    <th class="head-zone-subject">Total</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($students as $st)
                @php
                    $stId = $st->user_id;
                    $stat = $student_stats[$stId] ?? [];
                    $grandTotal = $stat['total'] ?? null;
                    $avg = $stat['ave'] ?? null;
                    $pos = $stat['pos'] ?? null;
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="identity-name">{{ $st->user->name }}</td>
                    @php
                        $g = $st->user->gender ?? '';
                        if (stripos((string) $g, 'female') !== false) {
                            $sexShort = 'F';
                        } elseif (stripos((string) $g, 'male') !== false) {
                            $sexShort = 'M';
                        } elseif ($g !== '') {
                            $sexShort = strtoupper(substr(trim((string) $g), 0, 1));
                        } else {
                            $sexShort = '-';
                        }
                    @endphp
                    <td>{{ $sexShort }}</td>
                    <td>{{ ($st->adm_no !== null && $st->adm_no !== '') ? $st->adm_no : '-' }}</td>
                    @foreach($subjects as $sub)
                        @php $mk = $marks_index[$stId][$sub->id] ?? null; @endphp
                        @foreach($slots as $slot)
                            @php
                                $k = $slot['key'] ?? '';
                                $raw = $mk && $k ? ($mk->$k ?? null) : null;
                                $v = ($raw !== null && $raw !== '') ? $raw : null;
                            @endphp
                            <td class="{{ $v !== null && is_numeric($v) && (float)$v < 50 ? 'mark-low' : ($v !== null && is_numeric($v) && (float)$v > 90 ? 'mark-high' : '') }}">{{ $v !== null ? $v : '—' }}</td>
                        @endforeach
                        @php
                            $tex1 = $mk ? ($mk->tex1 ?? null) : null;
                        @endphp
                        <td style="font-weight:700;">{{ $tex1 !== null && $tex1 !== '' ? $tex1 : '—' }}</td>
                    @endforeach
                    <td style="font-weight:700;">{{ $grandTotal !== null ? $grandTotal : '—' }}</td>
                    <td>{{ $avg !== null ? $avg : '—' }}</td>
                    <td style="font-weight:700;">{!! $pos !== null ? Mk::getSuffix((int) $pos) : '—' !!}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>window.print();</script>
</body>
</html>
