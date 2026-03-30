@extends('layouts.master')
@section('page_title', 'Attendance Report Results')

@section('content')
    <style>
        .attendance-report-results thead th {
            background: #002147;
            color: #fff;
            border-color: #002147;
            white-space: nowrap;
        }
        .attendance-report-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .attendance-report-summary__title {
            font-weight: 700;
            color: #000;
            font-size: 1.1rem;
        }
        .attendance-report-summary__meta {
            color: #6c757d;
        }
    </style>

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">
                <i class="icon-stats-bars2 mr-2"></i> Monthly Attendance Report
            </h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="attendance-report-summary">
                <div class="attendance-report-summary__title">{{ $my_class->name ?? 'Class' }} - Section {{ $section->name ?? 'Section' }}</div>
                <div class="attendance-report-summary__meta">
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $report_month)->format('F Y') }} | Working Days: {{ $total_working_days }}
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered datatable-attendance-report attendance-report-results">
                    <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Student Name</th>
                        <th>ADM No</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Late</th>
                        <th>Excused</th>
                        <th>Attendance %</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($report_rows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $row['adm_no'] }}</td>
                            <td>{{ $row['present'] }}</td>
                            <td>{{ $row['absent'] }}</td>
                            <td>{{ $row['late'] }}</td>
                            <td>{{ $row['excused'] }}</td>
                            <td>{{ number_format($row['attendance_percent'], 1) }}%</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="text-right mt-3">
                <a href="{{ route('attendance.report') }}" class="btn btn-light">Back to Report Selection</a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
(function () {
    $(function () {
        if (!$().DataTable || !$('.datatable-attendance-report').length) {
            return;
        }

        var exportLabel = 'Monthly Attendance Report';
        var exportFilename = exportLabel.replace(/[/\\:*?"<>|]/g, '-');

        $('.datatable-attendance-report').DataTable({
            autoWidth: false,
            ordering: false,
            dom: '<"datatable-header"fBl><"datatable-scroll"t><"datatable-footer"ip>',
            language: {
                search: '<span>Filter:</span> _INPUT_',
                searchPlaceholder: 'Type to filter...',
                lengthMenu: '<span>Show:</span> _MENU_',
                paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' }
            },
            buttons: {
                dom: { button: { className: 'btn btn-light' } },
                buttons: [
                    { extend: 'excelHtml5', className: 'btn btn-light', filename: exportFilename, title: exportLabel },
                    { extend: 'pdfHtml5', className: 'btn btn-light', filename: exportFilename, title: exportLabel, orientation: 'landscape', pageSize: 'A4' },
                    { extend: 'colvis', text: '<i class="icon-three-bars"></i> Visibility', className: 'btn bg-blue btn-icon dropdown-toggle' }
                ]
            }
        });

        $('.dataTables_length select').select2 && $('.dataTables_length select').select2({ minimumResultsForSearch: Infinity, width: 'auto' });
    });
})();
</script>
@endsection
