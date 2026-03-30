<html>
<head>
    <title>FINALIZED FORM MASTER ROSTER</title>
</head>
<body>
<div style="background-color:#002147; padding:8px 14px; text-align:center; margin-bottom:10px;">
    <div style="color:#fff; font-weight:900; font-size:26px; text-transform:uppercase;">
        {{ strtoupper(Qs::getSetting('system_name')) }}
    </div>
</div>

<div style="margin-bottom:12px;">
    <div style="font-weight:700;">Finalized Form Master Roster</div>
    <div><strong>Class:</strong> {{ $my_class->name ?? '-' }} | <strong>Section:</strong> {{ $section->name ?? '-' }}</div>
    <div><strong>Term:</strong> {{ (int) $term }} | <strong>Academic Year:</strong> {{ $year }}</div>
</div>

<table style="width:100%; border-collapse:collapse;">
    <thead>
    <tr>
        <th style="background:#002147;color:#fff;border:1px solid #e5e7eb;padding:8px;">S/N</th>
        <th style="background:#002147;color:#fff;border:1px solid #e5e7eb;padding:8px;text-align:left;">Student Name</th>
        @foreach($subjects as $sub)
            <th style="background:#002147;color:#fff;border:1px solid #e5e7eb;padding:8px;">{{ strtoupper($sub->slug ?: $sub->name) }}</th>
        @endforeach
        <th style="background:#002147;color:#fff;border:1px solid #e5e7eb;padding:8px;">Grand Total</th>
        <th style="background:#002147;color:#fff;border:1px solid #e5e7eb;padding:8px;">Average</th>
        <th style="background:#002147;color:#fff;border:1px solid #e5e7eb;padding:8px;">Rank</th>
    </tr>
    </thead>
    <tbody>
    @foreach($students as $st)
        @php
            $uid = $st->user_id;
            $stat = $student_stats[$uid] ?? ['total' => 0, 'ave' => null, 'pos' => null];
        @endphp
        <tr>
            <td style="border:1px solid #e5e7eb;padding:8px;text-align:center;">{{ $loop->iteration }}</td>
            <td style="border:1px solid #e5e7eb;padding:8px;text-align:left;">{{ $st->user->name ?? '-' }}</td>
            @foreach($subjects as $sub)
                @php $v = $marks_index[$uid][$sub->id] ?? null; @endphp
                <td style="border:1px solid #e5e7eb;padding:8px;text-align:center;">{{ $v !== null ? $v : '-' }}</td>
            @endforeach
            <td style="border:1px solid #e5e7eb;padding:8px;text-align:right;font-weight:700;">{{ (int) ($stat['total'] ?? 0) }}</td>
            <td style="border:1px solid #e5e7eb;padding:8px;text-align:center;">{{ $stat['ave'] !== null ? $stat['ave'] : '-' }}</td>
            <td style="border:1px solid #e5e7eb;padding:8px;text-align:center;">{!! $stat['pos'] !== null ? Mk::getSuffix((int) $stat['pos']) : '-' !!}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<script>
    window.print();
</script>
</body>
</html>
