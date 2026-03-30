@extends('layouts.master')
@section('page_title', 'Attendance Grid')

@section('content')
    <style>
        .attendance-card-header {
            background: #1a1a1a;
            color: #fff;
        }
        .attendance-table thead th {
            background: #002147;
            color: #fff;
            border-color: #002147;
            white-space: nowrap;
        }
        .attendance-table th,
        .attendance-table td {
            vertical-align: middle !important;
        }
        .attendance-accent {
            background: #D32F2F;
            border-color: #D32F2F;
            color: #fff;
        }
        .attendance-table {
            min-width: 880px;
        }
        .attendance-table th,
        .attendance-table td {
            white-space: nowrap;
        }
        .attendance-col-sn {
            width: 60px;
            min-width: 60px;
        }
        .attendance-col-name {
            width: 220px;
            min-width: 220px;
        }
        .attendance-col-adm {
            width: 120px;
            min-width: 120px;
        }
        .attendance-col-day {
            width: 110px;
            min-width: 110px;
        }
        .attendance-status-select {
            min-width: 96px;
            width: 96px;
        }
        .attendance-grid-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .attendance-grid-meta__title {
            font-weight: 700;
            color: #000000;
            font-size: 1.1rem;
        }
        .attendance-grid-meta__week {
            color: #6c757d;
            white-space: nowrap;
        }
        .bg-present {
            background-color: #28a745 !important;
            color: white !important;
            font-weight: bold;
        }
        .bg-absent {
            background-color: #d32f2f !important;
            color: white !important;
            font-weight: bold;
        }
        .bg-late {
            background-color: #ff9800 !important;
            color: white !important;
            font-weight: bold;
        }
        .bg-excused {
            background-color: #0288d1 !important;
            color: white !important;
            font-weight: bold;
        }
        .attendance-status-select.bg-present option,
        .attendance-status-select.bg-absent option,
        .attendance-status-select.bg-late option,
        .attendance-status-select.bg-excused option {
            color: #212529;
        }
        .attendance-status-select option {
            color: #212529;
        }
        select.form-control:disabled {
            background: #e9ecef !important;
            color: #6c757d !important;
            cursor: not-allowed;
            opacity: 0.7;
            border: 1px solid #ced4da !important;
        }
        .attendance-status-select:disabled {
            font-weight: 600;
        }
        @media (max-width: 767.98px) {
            .attendance-grid-meta__week {
                white-space: normal;
            }
        }
    </style>

    <div class="card">
        <div class="card-header attendance-card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold text-white mb-0">
                <i class="icon-calendar3 mr-2"></i> Attendance Marking
            </h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="attendance-grid-meta">
                <div class="attendance-grid-meta__title">{{ $my_class->name ?? 'Class' }} - Section {{ $section->name ?? 'Section' }}</div>
                <div class="attendance-grid-meta__week">Week of {{ \Carbon\Carbon::parse($week_days[0]['date'] ?? $attendance_date)->format('M j') }} - {{ \Carbon\Carbon::parse($week_days[4]['date'] ?? $attendance_date)->format('M j, Y') }}</div>
            </div>

            <form method="post" action="{{ route('attendance.store') }}">
                @csrf
                <input type="hidden" name="my_class_id" value="{{ $my_class_id }}">
                <input type="hidden" name="section_id" value="{{ $section_id }}">
                <input type="hidden" name="date" value="{{ $attendance_date }}">

                <div class="table-responsive">
                    <table class="table table-bordered attendance-table datatable-attendance-grid">
                        <thead>
                        <tr>
                            <th class="attendance-col-sn">S/N</th>
                            <th class="attendance-col-name">Student Name</th>
                            <th class="attendance-col-adm">ADM No</th>
                            @foreach($week_days as $weekDay)
                                <th class="attendance-col-day">{{ $weekDay['label'] }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td class="attendance-col-sn">{{ $loop->iteration }}</td>
                                <td class="attendance-col-name">{{ $student->user->name ?? 'Unknown Student' }}</td>
                                <td class="attendance-col-adm">{{ $student->adm_no ?? '—' }}</td>
                                @foreach($week_days as $weekDay)
                                    @php
                                        $selectedStatus = data_get($attendance_records, $student->user_id . '.' . $weekDay['date'] . '.status', 'present');
                                    @endphp
                                    <td class="attendance-col-day">
                                        <select name="attendance[{{ $student->user_id }}][{{ $weekDay['date'] }}]" class="form-control attendance-status-select" {{ \Carbon\Carbon::parse($weekDay['date'])->isFuture() ? 'disabled' : '' }}>
                                            <option value="present" {{ $selectedStatus === 'present' ? 'selected' : '' }}>Present</option>
                                            <option value="absent" {{ $selectedStatus === 'absent' ? 'selected' : '' }}>Absent</option>
                                            <option value="late" {{ $selectedStatus === 'late' ? 'selected' : '' }}>Late</option>
                                            <option value="excused" {{ $selectedStatus === 'excused' ? 'selected' : '' }}>Excused</option>
                                        </select>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 3 + count($week_days) }}" class="text-center text-muted">No active students found for this class and section.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <a href="{{ route('attendance.index') }}" class="btn btn-light">Back</a>
                    <button type="submit" class="btn attendance-accent" {{ $students->count() ? '' : 'disabled' }}>
                        Save Attendance <i class="icon-paperplane ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(function () {
        if ($.fn.DataTable && $('.datatable-attendance-grid').length) {
            $('.datatable-attendance-grid').DataTable({
                autoWidth: false,
                ordering: false,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
                language: {
                    search: '<span>Filter:</span> _INPUT_',
                    searchPlaceholder: 'Type to filter...',
                    lengthMenu: '<span>Show:</span> _MENU_',
                    paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' }
                }
            });
        }

        function applyAttendanceStatusColor($select) {
            var value = $select.val();
            var classes = 'bg-present bg-absent bg-late bg-excused';

            $select.removeClass(classes);

            if ($select.is(':disabled')) {
                return;
            }

            if (value === 'present') {
                $select.addClass('bg-present');
            } else if (value === 'absent') {
                $select.addClass('bg-absent');
            } else if (value === 'late') {
                $select.addClass('bg-late');
            } else if (value === 'excused') {
                $select.addClass('bg-excused');
            }
        }

        $('.attendance-status-select').each(function () {
            applyAttendanceStatusColor($(this));
        }).on('change', function () {
            applyAttendanceStatusColor($(this));
        });
    });
</script>
@endsection
