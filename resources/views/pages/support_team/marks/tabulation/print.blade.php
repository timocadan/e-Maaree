<html>
<head>
    <title>OFFICIAL CLASS MASTER TABULATION SHEET</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/print_tabulation.css') }}" />
</head>
<body>
<div class="container">
    <div id="print" xmlns:margin-top="http://www.w3.org/1999/xhtml">
        {{-- Navy branding bar + center title --}}
        <div style="background-color:#002147; padding:6px 14px; text-align:center; margin-bottom:8px;">
            <div style="color:#FFFFFF; font-weight:900; font-size:32px; text-transform:uppercase; line-height:1.05; display:inline-block; padding-bottom:4px; border-bottom:3px solid #FFFFFF;">
                {{ strtoupper(Qs::getSetting('system_name')) }}
            </div>
        </div>

        <div style="text-align:center; margin:0 0 20px 0;">
            <div style="font-weight:800; font-size:14pt;">
                OFFICIAL CLASS MASTER TABULATION SHEET
            </div>
        </div>

        {{-- Sub-headers: Class, Section, Academic Year, Form Master --}}
        <div style="width:100%; display:flex; justify-content:space-between; margin-bottom:8px; gap:10px;">
            <div style="flex:1;">
                <div style="font-size:12pt; color:#000000; font-weight:800;"><strong>Class:</strong> {{ $my_class->name }}</div>
                <div style="font-size:12pt; color:#000000; font-weight:800;"><strong>Section:</strong> {{ $section->name }}</div>
                <div style="font-size:12pt; color:#000000; font-weight:800;"><strong>Academic Year:</strong> {{ $year }}</div>
            </div>
            <div style="flex:1; text-align:right;">
                <div style="font-size:12pt; color:#000000; font-weight:800;"><strong>Form Master:</strong> {{ optional($my_class->teacher)->name ?? '-' }}</div>
            </div>
        </div>

        {{--Background Logo (Watermark)--}}
        <div style="position: relative;  text-align: center; ">
            <img src="{{ $s['logo'] }}"
                 style="max-width: 500px; max-height:600px; margin-top: 60px; position:absolute ; opacity: 0.2; margin-left: auto;margin-right: auto; left: 0; right: 0;" />
        </div>

        {{-- Master Tabulation Begins (Subject Totals Only) --}}
        @php
            $texKey = 'tex' . ($term ?? 1);
            $subjectsCount = count($subjects ?? []);
        @endphp

        <style>
            @media print {
                @page { size: A4 landscape; margin: 10mm; }
                body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            }
            #master-tabulation-print th, #master-tabulation-print td{
                padding: 8px 6px;
                border: 1px solid #d1d5db !important;
                text-align: center;
                vertical-align: middle;
                font-size: 10pt;
            }
            #master-tabulation-print thead th{
                padding: 10px 6px;
                font-weight: 700;
            }
            #master-tabulation-print tbody tr:nth-child(even){
                background-color:#f3f4f6;
            }
        </style>

        <table id="master-tabulation-print" style="width:100%; border-collapse:collapse; border:1px solid #d1d5db; margin: 10px auto 0 auto;" border="1">
            <thead>
                <tr>
                    <th style="background-color:#002147;color:#FFFFFF;font-weight:700;border:1px solid #d1d5db;width:60px;">#</th>
                    <th style="background-color:#002147;color:#FFFFFF;font-weight:700;border:1px solid #d1d5db;min-width:160px;">Student Name</th>

                    @foreach($subjects as $sub)
                        <th style="background-color:#002147;color:#FFFFFF;font-weight:700;border:1px solid #d1d5db;white-space:nowrap;">
                            {{ strtoupper($sub->slug ?: $sub->name) }}
                        </th>
                    @endforeach

                    <th style="background-color:#002147;color:#FFFFFF;font-weight:700;border:1px solid #d1d5db;white-space:nowrap;">GRAND TOTAL</th>
                    <th style="background-color:#002147;color:#FFFFFF;font-weight:700;border:1px solid #d1d5db;white-space:nowrap;">AVERAGE</th>
                    <th style="background-color:#002147;color:#FFFFFF;font-weight:700;border:1px solid #d1d5db;white-space:nowrap;">RANK</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $s)
                    @php
                        $stat = $student_stats[$s->user_id] ?? null;
                        $grandTotal = $stat['total'] ?? 0;
                        $pos = $stat['pos'] ?? null;
                        $hasAny = false;
                    @endphp
                    <tr>
                        <td style="border:1px solid #d1d5db;text-align:center;">{{ $loop->iteration }}</td>
                        <td style="border:1px solid #d1d5db;text-align:center;">{{ $s->user->name }}</td>

                        @foreach($subjects as $sub)
                            @php
                                $mk = $marks_index[$s->user_id][$sub->id] ?? null;
                                $val = $mk ? ($mk->{$texKey} ?? null) : null;
                            @endphp
                            @if($val !== null) @php $hasAny = true; @endphp @endif
                        <td style="border:1px solid #d1d5db;text-align:center;">{{ $val !== null ? $val : '-' }}</td>
                        @endforeach

                        @php
                            $avg = ($subjectsCount > 0 && $hasAny)
                                ? round(((float)$grandTotal / (float)$subjectsCount), 1)
                                : null;
                        @endphp

                        <td style="border:1px solid #d1d5db;text-align:center;font-weight:700;color:darkred;">
                            {{ $hasAny ? $grandTotal : '-' }}
                        </td>
                        <td style="border:1px solid #d1d5db;text-align:center;color:darkblue;">
                            {{ ($hasAny && $avg !== null) ? $avg : '-' }}
                        </td>
                        <td style="border:1px solid #d1d5db;text-align:center;color:darkgreen;">
                            {!! ($hasAny && $pos !== null) ? Mk::getSuffix((int)$pos) : '-' !!}
                        </td>
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
