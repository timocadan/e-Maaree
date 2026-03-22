{{--<!--NAME , CLASS AND OTHER INFO -->--}}
<table style="width:100%; border-collapse:collapse; margin-bottom:16px; font-family:system-ui,sans-serif; font-size:10pt; color:#374151;">
    <tbody>
    <tr>
        <td style="padding:4px 0;">
            <strong>Name:</strong> {{ strtoupper($sr->user->name) }}
            <span style="color:#e5e7eb;padding:0 8px;">|</span>
            <strong>ID:</strong> {{ $sr->adm_no }}
            <span style="color:#e5e7eb;padding:0 8px;">|</span>
            <strong>Class:</strong> {{ strtoupper($my_class->name) }}
        </td>
        <td style="padding:4px 0;text-align:right;">
            <strong>Year:</strong> {{ $year }}
            <span style="color:#e5e7eb;padding:0 8px;">|</span>
            <strong>Term:</strong> {!! strip_tags(Mk::getSuffix($term ?? 1)) !!}
            <span style="color:#e5e7eb;padding:0 8px;">|</span>
            <strong>Rank:</strong> <span style="color:#D32F2F;font-weight:600;">{!! isset($exr->pos) ? Mk::getSuffix((int)$exr->pos) : '—' !!}</span>
        </td>
    </tr>
    </tbody>
</table>


{{--Exam Table--}}
<table style="width:100%; border-collapse:collapse; margin:12px auto; font-family:system-ui,sans-serif; font-size:9pt;" class="emaaree-legacy-marks-table">
    <thead>
    <tr style="background:#002147;color:#fff;">
        <th rowspan="2" style="padding:6px 8px;font-weight:600;font-size:7pt;letter-spacing:0.06em;text-transform:uppercase;text-align:left;">Subjects</th>
        <th colspan="3" style="padding:6px 8px;font-weight:600;font-size:7pt;letter-spacing:0.06em;text-transform:uppercase;">Continuous assessment</th>
        <th rowspan="2" style="padding:6px 8px;font-weight:600;font-size:7pt;letter-spacing:0.06em;text-transform:uppercase;">Exam<br>(60)</th>
        <th rowspan="2" style="padding:6px 8px;font-weight:600;font-size:7pt;letter-spacing:0.06em;text-transform:uppercase;">Final<br>(100%)</th>
        <th rowspan="2" style="padding:6px 8px;font-weight:600;font-size:7pt;letter-spacing:0.06em;text-transform:uppercase;">Grade</th>
        <th rowspan="2" style="padding:6px 8px;font-weight:600;font-size:7pt;letter-spacing:0.06em;text-transform:uppercase;">Sub.<br>pos.</th>
    </tr>
    <tr style="background:#002147;color:#fff;">
        <th style="padding:5px 6px;font-size:7pt;">CA1(20)</th>
        <th style="padding:5px 6px;font-size:7pt;">CA2(20)</th>
        <th style="padding:5px 6px;font-size:7pt;">Total(40)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($subjects as $sub)
        <tr>
            <td style="font-weight:600;border:none;border-bottom:1px solid #eee;padding:10px 8px;text-align:left;">{{ $sub->name }}</td>
            @foreach($marks->where('subject_id', $sub->id)->where('term', $term ?? 1) as $mk)
                <td style="border:none;border-bottom:1px solid #eee;padding:10px 8px;text-align:center;">{{ $mk->t1 ?: '-' }}</td>
                <td style="border:none;border-bottom:1px solid #eee;padding:10px 8px;text-align:center;">{{ $mk->t2 ?: '-' }}</td>
                <td style="border:none;border-bottom:1px solid #eee;padding:10px 8px;text-align:center;">{{ $mk->tca ?: '-' }}</td>
                <td style="border:none;border-bottom:1px solid #eee;padding:10px 8px;text-align:center;">{{ $mk->exm ?: '-' }}</td>
                <td style="border:none;border-bottom:1px solid #eee;padding:10px 8px;text-align:center;">{{ $mk->$tex ?: '-'}}</td>
                <td style="border:none;border-bottom:1px solid #eee;padding:10px 8px;text-align:center;">{{ $mk->grade ? $mk->grade->name : '-' }}</td>
                <td style="border:none;border-bottom:1px solid #eee;padding:10px 8px;text-align:center;">{!! ($mk->grade) ? Mk::getSuffix($mk->sub_pos) : '-' !!}</td>
            @endforeach
        </tr>
    @endforeach
    <tr>
        <td colspan="8" style="border:none;border-top:1px solid #e5e7eb;padding:24px 8px 8px;text-align:center;font-size:9pt;">
            <span style="color:#9ca3af;font-size:7pt;letter-spacing:0.14em;text-transform:uppercase;">Term result summary</span><br><br>
            <span style="color:#6b7280;font-size:8pt;font-weight:500;letter-spacing:0.06em;text-transform:uppercase;">Total scores</span>
            <strong style="font-weight:600;margin-right:28px;">{{ $exr->total }}</strong>
            <span style="color:#6b7280;font-size:8pt;font-weight:500;letter-spacing:0.06em;text-transform:uppercase;">Average</span>
            <strong style="font-weight:600;">{{ $exr->ave }}</strong>
        </td>
    </tr>
    </tbody>
</table>
