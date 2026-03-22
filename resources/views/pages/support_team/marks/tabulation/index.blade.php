@extends('layouts.master')
@section('page_title', 'Tabulation Sheet')
@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title"><i class="icon-books mr-2"></i> Tabulation Sheet</h5>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
        <form method="post" action="{{ route('marks.tabulation_select') }}">
                    @csrf
                    <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="my_class_id" class="col-form-label font-weight-bold">Class:</label>
                                            <select onchange="getClassSections(this.value)" required id="my_class_id" name="my_class_id" class="form-control select" data-placeholder="Select Class">
                                                <option value=""></option>
                                                @foreach($my_classes as $c)
                                                    <option {{ ($selected && $my_class_id == $c->id) ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="section_id" class="col-form-label font-weight-bold">Section:</label>
                                <select required id="section_id" name="section_id" data-placeholder="Select Class First" class="form-control select">
                                    @if($selected)
                                        @foreach($sections->where('my_class_id', $my_class_id) as $s)
                                            <option {{ $section_id == $s->id ? 'selected' : '' }} value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>


                        <div class="col-md-4 mt-4">
                            <div class="text-right mt-1">
                                <button type="submit" class="btn btn-primary">View Official Roster <i class="icon-paperplane ml-2"></i></button>
                            </div>
                        </div>

                    </div>

                </form>
        </div>
    </div>

    {{--if Selction Has Been Made --}}

    @if($selected)
        <div class="card">
            <div class="card-header">
                <h6 class="card-title font-weight-bold">
                    Official Annual Progress Roster - {{ $my_class->name.' '.$section->name.' ('.$year.')' }}
                </h6>
            </div>
            <div class="card-body">
                <style>
                    .tabulation-sheet { border-collapse: separate; border-spacing: 0; }
                    @media print {
                        @page { size: A4 landscape; margin: 10mm; }
                        .tabulation-sheet,
                        .tabulation-sheet thead th,
                        .tabulation-sheet tbody td,
                        .tabulation-sheet tr.annual-summary-row td {
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                            color-adjust: exact !important;
                        }
                    }
                    /* Zone 1 — Identity (Charcoal) */
                    .tabulation-sheet thead th.head-zone-identity{
                        background-color:#343a40 !important;
                        color:#FFFFFF !important;
                        font-weight:800 !important;
                        text-align:center;
                        vertical-align:middle !important;
                        border:1px solid #FFFFFF !important;
                    }
                    /* Zone 2 — Subjects (Navy — focus on marks) */
                    .tabulation-sheet thead th.head-zone-subject{
                        background-color:#002147 !important;
                        color:#FFFFFF !important;
                        font-weight:800 !important;
                        text-align:center;
                        vertical-align:middle !important;
                        white-space:nowrap;
                        border:1px solid #FFFFFF !important;
                    }
                    /* Zone 3 — Results (Brand red) */
                    .tabulation-sheet thead th.head-zone-results{
                        background-color:#8b0000 !important;
                        color:#FFFFFF !important;
                        font-weight:800 !important;
                        text-align:center;
                        vertical-align:middle !important;
                        white-space:nowrap;
                        border:1px solid #FFFFFF !important;
                    }
                    .tabulation-sheet th{
                        vertical-align:middle !important;
                    }
                    .tabulation-sheet td{
                        vertical-align:middle !important;
                        border:1px solid #dee2e6;
                        text-align:center !important;
                    }
                    .tabulation-sheet th,
                    .tabulation-sheet td{
                        padding: 12px 8px;
                    }
                    .tabulation-sheet tbody tr { background-color: #fff !important; }
                    .tabulation-sheet .identity-cell{
                        text-align: center !important;
                        vertical-align: middle !important;
                        font-weight: 500;
                        color: #1f2937;
                    }
                    .tabulation-sheet .identity-cell.identity-name{
                        text-align: left !important;
                        font-weight: 500;
                        padding-left: 0.75rem;
                    }
                    .tabulation-sheet .period-cell{
                        font-weight: 500;
                        text-align:center !important;
                        vertical-align:middle !important;
                        padding-left:12px;
                        padding-right:12px;
                        white-space:nowrap;
                        min-width:88px;
                        background-color:#fff;
                    }
                    /* Term rows: no bold — normal weight for readability */
                    .tabulation-sheet tr.term-row td{
                        font-weight: 400; color: #333;
                        text-align:center !important;
                        vertical-align:middle !important;
                    }
                    .tabulation-sheet tr.term-row td.period-cell{
                        background-color:#f3f4f6;
                    }
                    .tabulation-sheet tr.term-row .mark-low{
                        color:#D32F2F;
                        font-weight: 400;
                    }
                    .tabulation-sheet tr.term-row .mark-high{
                        color:#0B3D2E;
                        font-weight: 400;
                    }
                    /* Average row (3rd): separator grey + extrabold + navy “result” rule */
                    .tabulation-sheet tr.annual-summary-row td{
                        background-color: #ebedef !important;
                        font-weight: 800 !important;
                        color: #111827 !important;
                        text-align:center !important;
                        vertical-align:middle !important;
                        border-top: 1px solid #002147 !important;
                        border-bottom: 2px solid #adb5bd !important;
                    }
                    .tabulation-sheet tr.annual-summary-row .mark-low{
                        color:#D32F2F;
                        font-weight: 800 !important;
                    }
                    .tabulation-sheet tr.annual-summary-row .mark-high{
                        color:#0B3D2E;
                        font-weight: 800 !important;
                    }
                    .tabulation-sheet .annual-rank-good{
                        font-weight: 800 !important;
                        color: #15803d !important;
                    }
                    .tabulation-sheet .annual-rank-risk{
                        font-weight: 800 !important;
                        color: #D32F2F !important;
                    }
                    .tabulation-sheet .annual-rank-neutral{
                        font-weight: 800 !important;
                        color: #374151 !important;
                    }
                    .tabulation-roster-wrap{
                        overflow: visible !important;
                    }
                    .tabulation-roster-wrap table.table-responsive.tabulation-sheet{
                        overflow: visible !important;
                    }
                    .tabulation-sheet .action-cell{
                        background:#fff !important;
                        vertical-align:middle !important;
                        text-align:center !important;
                        min-width:112px;
                    }
                    .tabulation-sheet .action-cell .tabulation-action-dd{
                        min-height:100%;
                    }
                    .roster-print-dd-btn{
                        background-color:#D32F2F !important;
                        border-color:#D32F2F !important;
                        color:#fff !important;
                        font-weight:700 !important;
                        font-size:0.8rem;
                        padding:0.35rem 0.6rem;
                        border-radius:4px;
                        box-shadow:0 1px 2px rgba(0,0,0,.08);
                    }
                    .roster-print-dd-btn:hover,
                    .roster-print-dd-btn:focus{
                        background-color:#b71c1c !important;
                        border-color:#b71c1c !important;
                        color:#fff !important;
                    }
                    .roster-print-dd-btn.dropdown-toggle::after{
                        margin-left:0.25rem;
                    }
                    .tabulation-sheet .action-cell .dropdown-menu{
                        z-index:1055;
                        min-width:13rem;
                        font-size:0.8125rem;
                        border-radius:4px;
                        box-shadow:0 4px 16px rgba(0,0,0,.12);
                    }
                    .tabulation-sheet .action-cell .dropdown-item{
                        color:#212529 !important;
                        font-weight:500;
                        padding-top:0.45rem;
                        padding-bottom:0.45rem;
                    }
                    .tabulation-sheet .action-cell .dropdown-item:hover,
                    .tabulation-sheet .action-cell .dropdown-item:focus{
                        background:#f1f5f9 !important;
                        color:#002147 !important;
                    }
                    .tabulation-sheet .action-cell .dropdown-header{
                        font-size:0.65rem;
                        letter-spacing:.06em;
                    }
                </style>
                {{-- Print CTA: top-right of the table --}}
                <div class="d-flex justify-content-end mb-3" style="width:100%;">
                    <a target="_blank"
                       href="{{ route('marks.print_tabulation', [$term, $my_class_id, $section_id]) }}"
                       class="btn btn-lg"
                       style="background-color:#D32F2F !important; border-color:#D32F2F !important; color:#FFFFFF !important; opacity:1 !important; font-weight:800; min-width:280px; box-shadow:none; border-radius:2px; padding-left:18px; padding-right:18px;">
                        <i class="icon-printer mr-2" style="color:#FFFFFF;"></i>
                        Print Annual Report
                    </a>
                </div>
                <div class="tabulation-roster-wrap w-100">
                <table class="table table-responsive tabulation-sheet" style="width:100%;">
                    <thead>
                    <tr>
                        <th class="head-zone-identity" style="width:48px;">S/N</th>
                        <th class="head-zone-identity" style="min-width:160px;">Student Name</th>
                        <th class="head-zone-identity" style="width:52px;">Sex</th>
                        <th class="head-zone-identity" style="min-width:96px;">ADM_No</th>
                        <th class="head-zone-identity" style="width:90px;">Period</th>
                        @foreach($subjects as $sub)
                            <th class="head-zone-subject subject-head">{{ strtoupper($sub->slug ?: $sub->name) }}</th>
                        @endforeach
                        <th class="head-zone-results">GRAND TOTAL</th>
                        <th class="head-zone-results">AVERAGE</th>
                        <th class="head-zone-results">RANK</th>
                        <th class="head-zone-results" style="min-width:110px;">PRINT</th>
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
                            <td rowspan="3" class="action-cell">
                                <div class="d-flex align-items-center justify-content-center tabulation-action-dd py-2">
                                    <div class="dropdown text-center">
                                        <button type="button"
                                                class="btn btn-sm dropdown-toggle roster-print-dd-btn"
                                                data-toggle="dropdown"
                                                aria-haspopup="true"
                                                aria-expanded="false"
                                                title="Print result card"
                                                aria-label="Print result card">
                                            <i class="icon-printer" aria-hidden="true"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right border-0 py-1">
                                            <h6 class="dropdown-header text-uppercase text-muted mb-0 px-3 py-2">Result card</h6>
                                            <a class="dropdown-item" target="_blank" rel="noopener noreferrer"
                                               href="{{ route('marks.print', [Qs::hash($row['student_id']), 1, $year]) }}">Term 1 result</a>
                                            <a class="dropdown-item" target="_blank" rel="noopener noreferrer"
                                               href="{{ route('marks.print', [Qs::hash($row['student_id']), 2, $year]) }}">Term 2 result</a>
                                            <div class="dropdown-divider my-1"></div>
                                            <a class="dropdown-item font-weight-bold" target="_blank" rel="noopener noreferrer"
                                               href="{{ route('marks.print_annual', [Qs::hash($row['student_id']), $year]) }}">Annual result (combined)</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
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
                {{--Print Button moved to top-right above the table--}}
            </div>
        </div>
    @endif
@endsection
