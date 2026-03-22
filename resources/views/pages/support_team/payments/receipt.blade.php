<html>
<head>
    <title>Receipt_{{ $pr->ref_no }}_{{ $sr->user->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/receipt.css') }}"/>
    <style>
        .receipt-body { font-family: Arial, sans-serif; font-size: 14px; color: #1a1a1a; max-width: 210mm; margin: 0 auto; padding: 20px; }
        .receipt-school-name { font-size: 28px; font-weight: bold; text-transform: uppercase; text-align: center; margin-bottom: 12px; text-decoration: underline; letter-spacing: 0.02em; }
        .receipt-navbar { background-color: #002147; color: #fff; padding: 12px 16px; text-align: center; font-size: 11px; margin-bottom: 16px; }
        .receipt-navbar span { margin: 0 8px; }
        .receipt-doc-title { font-size: 18px; font-weight: bold; text-align: center; margin: 16px 0 20px 0; }
        .receipt-meta { display: table; width: 100%; margin-bottom: 20px; font-size: 12px; }
        .receipt-meta-left { display: table-cell; text-align: left; }
        .receipt-meta-right { display: table-cell; text-align: right; }
        .receipt-section-title { background-color: #f5f5f5; padding: 8px 12px; font-weight: bold; font-size: 13px; margin-top: 16px; margin-bottom: 0; border: 1px solid #e0e0e0; }
        .receipt-table { width: 100%; border-collapse: collapse; margin-top: 0; margin-bottom: 16px; }
        .receipt-table th, .receipt-table td { border: 1px solid #dddddd; padding: 10px 12px; text-align: left; }
        .receipt-table th { background-color: #f9f9f9; font-weight: 600; font-size: 12px; }
        .receipt-table td { font-size: 13px; }
        .receipt-signature { margin-top: 32px; text-align: right; padding-right: 40px; }
        .receipt-signature-line { border-bottom: 1px solid #333; width: 220px; margin-left: auto; margin-top: 36px; margin-bottom: 4px; }
        .receipt-signature-label { font-size: 11px; color: #555; }
        .receipt-footer { margin-top: 40px; padding-top: 12px; text-align: center; font-size: 10px; color: #999999; border-top: 1px solid #eee; }
    </style>
</head>
<body>
<div class="container receipt-body">
    <div id="print">

        {{-- Official letterhead: School name in LARGE BOLD CAPS --}}
        <div class="receipt-school-name">{{ strtoupper(Qs::getSetting('system_name') ?: $s['system_name'] ?? 'School') }}</div>

        {{-- Navy Blue bar: Address, Tel 1, Tel 2, Email in white --}}
        @php
            $addr = Qs::getSetting('address') ?: ($s['address'] ?? '');
            $tel1 = Qs::getSetting('phone') ?: ($s['phone'] ?? '');
            $tel2 = Qs::getSetting('phone2') ?: ($s['phone2'] ?? '');
            $email = Qs::getSetting('system_email') ?: ($s['system_email'] ?? '');
            $telLine = array_filter([$tel1, $tel2]);
            $addressBarParts = array_filter([
                $addr,
                count($telLine) ? 'Tel: ' . implode(', ', $telLine) : '',
                $email
            ]);
            $addressBarText = implode(' | ', $addressBarParts) ?: ' ';
        @endphp
        <div class="receipt-navbar">{{ $addressBarText }}</div>

        {{-- Document info: OFFICIAL PAYMENT RECEIPT, Date, Receipt Number --}}
        <div class="receipt-doc-title">OFFICIAL PAYMENT RECEIPT</div>
        <div class="receipt-meta">
            <div class="receipt-meta-left"><strong>Date:</strong> {{ date('d M Y', strtotime($pr->updated_at ?? $pr->created_at)) }}</div>
            <div class="receipt-meta-right"><strong>Receipt No:</strong> {{ $pr->ref_no }}</div>
        </div>

        {{-- Student Information (no photo) --}}
        <div class="receipt-section-title">STUDENT INFORMATION</div>
        <table class="receipt-table">
            <tr><th style="width: 140px;">Name</th><td>{{ $sr->user->name }}</td></tr>
            <tr><th>ADM No</th><td>{{ $sr->adm_no }}</td></tr>
            <tr><th>Class</th><td>{{ $sr->my_class->name }}</td></tr>
        </table>

        {{-- Payment Information --}}
        <div class="receipt-section-title">PAYMENT INFORMATION</div>
        <table class="receipt-table">
            <tr><th style="width: 140px;">Reference</th><td>{{ $payment->ref_no }}</td></tr>
            <tr><th>Title</th><td>{{ $payment->title }}</td></tr>
            <tr><th>Amount ({{ Qs::getCurrency() }})</th><td>{{ $payment->amount }}</td></tr>
            @if($payment->description)
            <tr><th>Description</th><td>{{ $payment->description }}</td></tr>
            @endif
        </table>

        {{-- Payment history table: Date, Amount Paid (ETB), Balance (ETB) --}}
        <div class="receipt-section-title">PAYMENT HISTORY</div>
        <table class="receipt-table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Amount Paid ({{ Qs::getCurrency() }})</th>
                <th>Balance ({{ Qs::getCurrency() }})</th>
            </tr>
            </thead>
            <tbody>
            @foreach($receipts as $r)
                <tr>
                    <td>{{ date('d M Y', strtotime($r->created_at)) }}</td>
                    <td>{{ $r->amt_paid }}</td>
                    <td>{{ $r->balance }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{-- Status --}}
        <table class="receipt-table">
            <tr>
                <th style="width: 140px;">Status</th>
                <td><strong>{{ $pr->paid ? 'CLEARED' : 'Balance Due: ' . ($pr->balance ?? $payment->amount) . ' ' . Qs::getCurrency() }}</strong></td>
            </tr>
        </table>

        {{-- Accountant's Signature (bottom right) --}}
        <div class="receipt-signature">
            <div class="receipt-signature-line"></div>
            <div class="receipt-signature-label">Accountant's Signature</div>
        </div>

        {{-- Footer --}}
        <div class="receipt-footer">Generated by e-maaree</div>
    </div>
</div>
<script>
window.print();
</script>
</body>
</html>
