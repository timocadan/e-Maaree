@extends('layouts.master')
@section('page_title', 'Class Master Dashboard')
@section('content')
    <style>
        .table a { text-decoration: none !important; color: inherit !important; }
        .table td { vertical-align: middle !important; text-align: center !important; }
        .table td.text-left { text-align: left !important; }
        .tabulation-sheet { border-collapse: separate; border-spacing: 0; }
        .tabulation-sheet thead th.head-zone-identity{
            background-color:#343a40 !important;
            color:#FFFFFF !important;
            font-weight:800 !important;
            text-align:center !important;
            vertical-align:middle !important;
            border:1px solid #FFFFFF !important;
        }
        .tabulation-sheet thead th.head-zone-subject{
            background-color:#002147 !important;
            color:#FFFFFF !important;
            font-weight:800 !important;
            text-align:center !important;
            vertical-align:middle !important;
            white-space:nowrap;
            border:1px solid #FFFFFF !important;
        }
        .tabulation-sheet thead th.head-zone-results{
            background-color:#8b0000 !important;
            color:#FFFFFF !important;
            font-weight:800 !important;
            text-align:center !important;
            vertical-align:middle !important;
            white-space:nowrap;
            border:1px solid #FFFFFF !important;
        }
        .tabulation-sheet th,
        .tabulation-sheet td{
            padding:12px 8px;
            text-align:center !important;
            vertical-align:middle !important;
        }
        .tabulation-sheet td{
            border:1px solid #dee2e6;
        }
        .tabulation-sheet tbody tr{
            background-color:#fff !important;
        }
        .tabulation-sheet tbody td,
        .tabulation-sheet tbody td *,
        .tabulation-sheet tbody td a{
            text-decoration:none !important;
        }
        .tabulation-sheet .identity-cell{
            text-align:center !important;
            vertical-align:middle !important;
            font-weight:500;
            color:#1f2937;
        }
        .tabulation-sheet .identity-cell.identity-name{
            padding-left:0.5rem;
            padding-right:0.5rem;
        }
        .tabulation-sheet .period-cell{
            font-weight:500;
            padding-left:12px;
            padding-right:12px;
            white-space:nowrap;
            min-width:88px;
            background-color:#fff;
        }
        .tabulation-sheet tr.term-row td{
            font-weight:400;
            color:#333;
        }
        .tabulation-sheet tr.term-row td.period-cell{
            background-color:#f3f4f6;
        }
        .tabulation-sheet tr.term-row .mark-low{
            color:#D32F2F;
            font-weight:400;
        }
        .tabulation-sheet tr.term-row .mark-high{
            color:#0B3D2E;
            font-weight:400;
        }
        .tabulation-sheet tr.annual-summary-row td{
            background-color:#ebedef !important;
            font-weight:800 !important;
            color:#111827 !important;
            border-top:1px solid #002147 !important;
            border-bottom:2px solid #adb5bd !important;
        }
        .tabulation-sheet tr.annual-summary-row .mark-low{
            color:#D32F2F;
            font-weight:800 !important;
        }
        .tabulation-sheet tr.annual-summary-row .mark-high{
            color:#0B3D2E;
            font-weight:800 !important;
        }
        .tabulation-sheet .annual-rank-good{
            font-weight:800 !important;
            color:#15803d !important;
        }
        .tabulation-sheet .annual-rank-risk{
            font-weight:800 !important;
            color:#D32F2F !important;
        }
        .tabulation-sheet .annual-rank-neutral{
            font-weight:800 !important;
            color:#374151 !important;
        }
        .datatable-header{
            display:flex;
            justify-content:flex-end;
            align-items:center;
            margin-bottom:10px;
        }
        .datatable-scroll-wrap{
            max-height:600px;
            overflow:auto;
            border:1px solid #ddd;
        }
        .datatable-footer{
            margin-top:10px;
        }
        .tabulation-sheet .action-cell{
            background:#fff !important;
            min-width:98px;
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
            text-decoration:none !important;
        }
    </style>
    <div class="card">
        <div class="card-header header-elements-inline" style="background-color: #002147;">
            <h6 class="card-title font-weight-bold text-white"><i class="icon-users4 mr-2 text-white"></i> Class Master Dashboard</h6>
            {!! Qs::getPanelOptions() !!}
        </div>
        @php
            /** @var \Illuminate\Support\Collection|\App\Models\Subject[] $subjects */
            /** @var \App\Models\Subject $sub */
            /** @var \App\Models\MyClass $my_class */
            /** @var \App\Models\Section $section */
        @endphp
        <div class="card-body">
            @if($form_master_classes->isEmpty())
                <p class="text-muted mb-0">You are not assigned as Form Master to any section. Contact admin to assign your section.</p>
            @else
                <form method="get" action="{{ route('class_master.dashboard') }}" class="mb-3 row align-items-end">
                    <div class="col-md-3 form-group mb-0">
                        <label class="font-weight-semibold">Class</label>
                        <select name="class_id" class="form-control form-control-sm select" onchange="this.form.submit()">
                            @foreach($form_master_classes as $c)
                                <option value="{{ $c->id }}" {{ (isset($class_id) && $class_id == $c->id) ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-0">
                        <label class="font-weight-semibold">Section</label>
                        <select name="section_id" class="form-control form-control-sm select" onchange="this.form.submit()">
                            <option value="">—</option>
                            @foreach($sections as $sec)
                                <option value="{{ $sec->id }}" {{ (isset($section_id) && $section_id == $sec->id) ? 'selected' : '' }}>{{ $sec->name ?? 'Section' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="year" value="{{ $year ?? '' }}">
                </form>

                @if($class_id && $section_id && $students->isNotEmpty())
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small">{{ $my_class->name ?? '' }} · Annual Summary · {{ $year ?? '' }}</span>
                        <a target="_blank" rel="noopener noreferrer"
                           href="{{ route('marks.print_tabulation', ['annual', $my_class->id, $section_id]) }}"
                           class="btn btn-danger btn-sm"
                           style="background-color:#D32F2F !important; border-color:#D32F2F !important; color:#FFFFFF !important;">
                            <i class="icon-printer mr-2"></i>
                            Print Annual Report
                        </a>
                    </div>
                    <table class="table datatable-button-html5-columns tabulation-sheet" style="width:100%;">
                        <thead>
                            <tr>
                                <th class="head-zone-identity" style="width:48px;">S/N</th>
                                <th class="head-zone-identity" style="min-width:160px;">Student Name</th>
                                <th class="head-zone-identity" style="width:52px;">Sex</th>
                                <th class="head-zone-identity" style="min-width:96px;">ADM_No</th>
                                <th class="head-zone-identity" style="width:90px;">Period</th>
                                @foreach($subjects as $sub)
                                    <th class="head-zone-subject">{{ strtoupper($sub->slug ?: $sub->name) }}</th>
                                @endforeach
                                <th class="head-zone-results">GRAND TOTAL</th>
                                <th class="head-zone-results">AVERAGE</th>
                                <th class="head-zone-results">RANK</th>
                                <th class="head-zone-results no-export" style="min-width:110px;">PRINT</th>
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
                                        <td class="{{ $v !== null && $v < 50 ? 'mark-low' : ($v !== null && $v > 90 ? 'mark-high' : '') }}">
                                            @if($v !== null)
                                                <a class="mark-link" href="{{ route('marks.manage', [1, $class_id, $section_id, $sub->id]) }}">{{ $v }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endforeach
                                    <td>{{ $term1Total !== null ? $term1Total : '-' }}</td>
                                    <td>{{ $term1Avg !== null ? $term1Avg : '-' }}</td>
                                    <td>{!! $rank1 !== null ? Mk::getSuffix((int)$rank1) : '-' !!}</td>
                                    <td rowspan="3" class="action-cell">
                                        <div class="d-flex align-items-center justify-content-center py-2">
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
                                    <td class="period-cell"><span class="d-none">{{ $row['name'] }} {{ $row['sex'] ?? '' }} {{ $row['adm_no'] ?? '' }}</span>Term 2</td>
                                    @foreach($subjects as $sub)
                                        @php $v = $row['sem2'][$sub->id] ?? null; @endphp
                                        <td class="{{ $v !== null && $v < 50 ? 'mark-low' : ($v !== null && $v > 90 ? 'mark-high' : '') }}">
                                            @if($v !== null)
                                                <a class="mark-link" href="{{ route('marks.manage', [2, $class_id, $section_id, $sub->id]) }}">{{ $v }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endforeach
                                    <td>{{ $term2Total !== null ? $term2Total : '-' }}</td>
                                    <td>{{ $term2Avg !== null ? $term2Avg : '-' }}</td>
                                    <td>{!! $rank2 !== null ? Mk::getSuffix((int)$rank2) : '-' !!}</td>
                                </tr>
                                <tr class="annual-summary-row">
                                    <td class="period-cell"><span class="d-none">{{ $row['name'] }} {{ $row['sex'] ?? '' }} {{ $row['adm_no'] ?? '' }}</span>Average</td>
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
                @elseif($class_id && $section_id)
                    <p class="text-muted mb-0">No students found in this assigned section.</p>
                @else
                    <p class="text-muted mb-0">Select class and section to view the annual roster matrix.</p>
                @endif
            @endif
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(function () {
    if (!$.fn.DataTable || !$('.datatable-button-html5-columns').length) {
        return;
    }

    if ($.fn.DataTable.isDataTable('.datatable-button-html5-columns')) {
        $('.datatable-button-html5-columns').DataTable().destroy();
    }

    $('.datatable-button-html5-columns').DataTable({
        dom: '<"datatable-header"B><"datatable-scroll-wrap"t><"datatable-footer"ip>',
        autoWidth: false,
        ordering: false,
        searching: false,
        buttons: [
            { extend: 'excelHtml5', className: 'btn btn-light', title: 'Roster' },
            { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', className: 'btn btn-light' }
        ]
    });
});
</script>
@endsection
