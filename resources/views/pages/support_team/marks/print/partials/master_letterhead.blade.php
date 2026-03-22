@php
    $schoolName = strtoupper(Qs::getSetting('system_name') ?: Qs::getSystemName());
    $phones = array_values(array_filter([trim($s['phone'] ?? ''), trim($s['phone2'] ?? '')]));
    $contactBits = [];
    if ($phones) {
        $contactBits[] = 'Tel: ' . implode(' · ', $phones);
    }
    if (!empty(trim($s['system_email'] ?? ''))) {
        $contactBits[] = trim($s['system_email']);
    }
    if (!empty(trim($s['website'] ?? ''))) {
        $contactBits[] = trim($s['website']);
    }
    if (!empty(trim($s['address'] ?? ''))) {
        $contactBits[] = trim(preg_replace('/\s+/', ' ', $s['address']));
    }
    $contactLine = implode(' &nbsp;|&nbsp; ', $contactBits);
@endphp
<h1 class="emaaree-school-title">{{ $schoolName }}</h1>
<div class="emaaree-navy-bar">
    @if($contactLine)
        {!! $contactLine !!}
    @else
        Official school contact on file
    @endif
</div>
