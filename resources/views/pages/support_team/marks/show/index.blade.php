@extends('layouts.master')
@section('page_title', 'Student Marksheet')
@section('content')

    @php
        $schoolName = strtoupper(Qs::getSetting('system_name') ?: Qs::getSystemName());
        $phones = array_values(array_filter([trim(Qs::getSetting('phone') ?? ''), trim(Qs::getSetting('phone2') ?? '')]));
        $contactBits = [];
        if ($phones) { $contactBits[] = 'Tel: ' . implode(' · ', $phones); }
        if (!empty(trim(Qs::getSetting('system_email') ?? ''))) { $contactBits[] = trim(Qs::getSetting('system_email')); }
        if (!empty(trim(Qs::getSetting('website') ?? ''))) { $contactBits[] = trim(Qs::getSetting('website')); }
        if (!empty(trim(Qs::getSetting('address') ?? ''))) { $contactBits[] = trim(preg_replace('/\s+/', ' ', Qs::getSetting('address'))); }
        $contactLine = implode(' | ', array_filter($contactBits));
        $classLabel = strtoupper(trim(($my_class->name ?? '') . (optional($sr->section)->name ? ' · ' . $sr->section->name : '')));
    @endphp

    <style>
        /* Keep marksheet page clean (no breadcrumb chrome). */
        .page-header,
        .breadcrumb-line,
        .breadcrumb,
        .content-header {
            display: none !important;
        }
        .emaa-web-school-title {
            font-weight: 800;
            letter-spacing: 0.03em;
            color: #111;
            text-transform: uppercase;
            text-align: center;
            margin: 0 0 6px;
        }
        .emaa-web-navy-bar {
            background: #002147;
            color: #fff;
            font-size: 12px;
            letter-spacing: 0.04em;
            padding: 6px 12px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 14px;
        }
        .emaa-web-term-card {
            border-radius: 12px !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }
        .emaa-web-identity {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fafbfc;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            font-size: 13px;
        }
        .emaa-web-identity .k { font-weight: 700; color: #64748b; }
        .emaa-web-identity .v { color: #111; }
        .emaa-web-identity .rank { color: #D32F2F; font-weight: 800; }
        .btn-marksheet-print {
            background: #D32F2F !important;
            border-color: #D32F2F !important;
            color: #fff !important;
            font-weight: 700;
            padding: 0.7rem 1.25rem;
        }
        .btn-marksheet-print:hover {
            background: #b71c1c !important;
            border-color: #b71c1c !important;
            color: #fff !important;
        }
        .emaa-web-summary-box {
            background: #f1f3f5;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px;
            max-width: 720px;
            margin: 10px auto 0;
            text-align: center;
        }
        .emaa-web-summary-title {
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #64748b;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .emaa-web-summary-metrics {
            display: flex;
            justify-content: center;
            gap: 26px;
            flex-wrap: wrap;
        }
        .emaa-web-summary-metrics .sk { display: block; color: #6b7280; font-weight: 600; font-size: 11px; letter-spacing: 0.04em; }
        .emaa-web-summary-metrics .sv { display: block; color: #000000; font-weight: 800; font-size: 24px; line-height: 1.1; }
        .emaa-web-sig {
            margin-top: 16px;
            display: flex;
            justify-content: flex-end;
        }
        .emaa-web-sig-line {
            min-width: 220px;
            border-top: 1px solid #94a3b8;
            padding-top: 6px;
            font-weight: 700;
            letter-spacing: 0.12em;
            color: #64748b;
            text-transform: uppercase;
            font-size: 11px;
            text-align: center;
        }
    </style>

    <h4 class="emaa-web-school-title">{{ $schoolName }}</h4>
    <div class="emaa-web-navy-bar">
        {{ $contactLine ?: 'Official school contact on file' }}
    </div>

    @foreach($terms ?? [1 => 'Term 1', 2 => 'Term 2'] as $termNum => $termLabel)
        @foreach($exam_records->where('term', $termNum) as $exr)

                @php
                    $termGrade = $term_grade_by_term[$termNum] ?? null;
                    $slots = $mark_slots_by_term[$termNum] ?? [];
                    $rank = isset($exr->pos) && $exr->pos !== null ? Mk::getSuffix((int)$exr->pos) : 'N/A';
                @endphp

                <div class="card emaa-web-term-card">
                    <div class="card-header header-elements-inline">
                        <h6 class="font-weight-bold">{{ $termLabel.' - '.$year }}</h6>
                        {!! Qs::getPanelOptions() !!}
                    </div>

                    <div class="card-body collapse">

                        <div class="emaa-web-identity mb-3">
                            <div><span class="k">Name:</span> <span class="v font-weight-bold text-dark">{{ strtoupper($sr->user->name) }}</span></div>
                            <div><span class="k">ID:</span> <span class="v">{{ $sr->adm_no ?? 'N/A' }}</span></div>
                            <div><span class="k">Class:</span> <span class="v">{{ $classLabel ?: 'N/A' }}</span></div>
                            <div><span class="k">Year:</span> <span class="v">{{ $year }}</span></div>
                            <div><span class="k">Term:</span> <span class="v">{{ strip_tags(Mk::getSuffix($termNum)) }}</span></div>
                            <div><span class="k">Rank:</span> <span class="rank">{!! $rank !!}</span></div>
                        </div>

                        {{--Sheet Table--}}
                        @include('pages.support_team.marks.show.sheet', ['termNum' => $termNum, 'slots' => $slots])

                        {{--Print Button--}}
                        <div class="text-center mt-2">
                            <a target="_blank" href="{{ route('marks.print', [Qs::hash($student_id), $termNum, $year]) }}" class="btn btn-lg btn-marksheet-print">Print Marksheet <i class="icon-printer ml-2"></i></a>
                        </div>

                        <div class="emaa-web-summary-box">
                            <div class="emaa-web-summary-title">Term Result Summary</div>
                            <div class="emaa-web-summary-metrics">
                                <div><span class="sk">TOTAL SCORES</span><span class="sv">{{ $exr->total ?? 'N/A' }}</span></div>
                                <div><span class="sk">AVERAGE</span><span class="sv">{{ $exr->ave ?? 'N/A' }}</span></div>
                                <div><span class="sk">GRADE</span><span class="sv">{{ optional($termGrade)->name ?? 'N/A' }}</span></div>
                            </div>
                            @if(Qs::userIsTeamSA() && !optional($termGrade)->name)
                                <div class="text-muted mt-2" style="font-size: 12px;">
                                    Grade is N/A — please verify Grade ranges for this level/session.
                                </div>
                            @endif
                        </div>

                        <div class="emaa-web-sig">
                            <div class="emaa-web-sig-line">Principal's Signature</div>
                        </div>

                    </div>

                </div>

        @endforeach
    @endforeach

@endsection
