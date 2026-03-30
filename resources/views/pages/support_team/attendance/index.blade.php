@extends('layouts.master')
@section('page_title', 'Attendance')

@section('content')
    <style>
        .attendance-card-header {
            background: #1a1a1a;
            color: #fff;
        }
        .attendance-section-title {
            color: #002147;
            font-weight: 700;
        }
        .attendance-accent {
            background: #D32F2F;
            border-color: #D32F2F;
            color: #fff;
        }
    </style>

    <div class="card">
        <div class="card-header attendance-card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold text-white mb-0">
                <i class="icon-calendar3 mr-2"></i> Attendance Selection
            </h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <p class="text-muted mb-4">Select the class, section, and date to open the attendance marking foundation for this session.</p>

            <form method="get" action="{{ route('attendance.show_marking_grid') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-semibold">Class</label>
                            <select id="attendance_class_id" name="my_class_id" class="form-control select-search" required>
                                <option value="">Choose class</option>
                                @foreach($my_classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-semibold">Section</label>
                            <select id="attendance_section_id" name="section_id" class="form-control select-search" required disabled>
                                <option value="">Choose class first</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-semibold">Date</label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn attendance-accent">
                        Open Attendance <i class="icon-arrow-right14 ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(function () {
        var $classSelect = $('#attendance_class_id');
        var $sectionSelect = $('#attendance_section_id');

        function resetSections(placeholder) {
            $sectionSelect.prop('disabled', true).empty().append(
                $('<option>', {
                    value: '',
                    text: placeholder
                })
            ).trigger('change.select2');
        }

        $classSelect.on('change', function () {
            var classId = $(this).val();
            if (!classId) {
                resetSections('Choose class first');
                return;
            }

            $sectionSelect.prop('disabled', false).empty().append(
                $('<option>', {
                    value: '',
                    text: 'Loading sections...'
                })
            ).trigger('change.select2');

            getClassSections(classId, '#attendance_section_id');
        });

        resetSections('Choose class first');
    });
</script>
@endsection
