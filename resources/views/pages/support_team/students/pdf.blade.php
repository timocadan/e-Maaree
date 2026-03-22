<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Class List - {{ $my_class->name ?? '' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; font-size: 11px; color: #1a1a1a; }
        .header { text-align: center; margin-bottom: 16px; }
        .school-name { font-size: 18px; font-weight: bold; color: #1a1a1a; margin-bottom: 6px; }
        .doc-title { font-size: 12px; font-weight: bold; color: #333; margin-bottom: 4px; letter-spacing: 0.5px; }
        .class-session { font-size: 11px; color: #555; }
        .header-line { height: 3px; background-color: #D32F2F; margin: 12px 0 16px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; }
        th { background-color: #6b7280; color: #fff; font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.3px; }
        td { vertical-align: middle; }
        tr:nth-child(even) { background-color: #fafafa; }
        .col-sn { width: 40px; text-align: center; }
        .col-signature { width: 120px; min-width: 120px; }
        .footer-line { height: 1px; background-color: #1a1a1a; margin-top: 20px; margin-bottom: 8px; }
        .footer { font-size: 9px; color: #555; text-align: center; }
        .footer span { margin: 0 6px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="school-name">{{ $settings['school_name'] }}</div>
        <div class="doc-title">STUDENT CLASS LIST</div>
        <div class="class-session">{{ $my_class->name ?? '' }} — Session: {{ $session ?? '' }}</div>
    </div>
    <div class="header-line"></div>

    <table>
        <thead>
            <tr>
                <th class="col-sn">S/N</th>
                <th>Full Name</th>
                <th>Admission Number</th>
                <th>Section</th>
                <th class="col-signature">Signature / Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $s)
                <tr>
                    <td class="col-sn">{{ $index + 1 }}</td>
                    <td>{{ $s->user->name ?? '—' }}</td>
                    <td>{{ $s->adm_no ?? '—' }}</td>
                    <td>{{ $s->section->name ?? '—' }}</td>
                    <td class="col-signature"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">No students in this class.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-line"></div>
    <div class="footer">
        @if(!empty($settings['address']))
            <span>{{ $settings['address'] }}</span> |
        @endif
        <span>Tel: {{ trim(implode(', ', array_filter([$settings['phone'], $settings['phone2']]))) ?: '—' }}</span>
        @if(!empty($settings['email']))
            | <span>{{ $settings['email'] }}</span>
        @endif
    </div>

</body>
</html>
