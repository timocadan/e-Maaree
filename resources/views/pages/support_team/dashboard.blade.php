@extends('layouts.master')
@section('page_title', 'My Dashboard')
@section('content')
    <style>
        .attendance-overview-card {
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 0.125rem 0.75rem rgba(15, 23, 42, 0.08);
            border-radius: 12px;
        }
        .attendance-overview-card .card-body {
            padding: 1.5rem;
        }
        .attendance-profile-header {
            padding: 0.1rem 0 0.35rem;
        }
        .attendance-profile-header__top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .attendance-profile-header__name {
            font-size: 1.4rem;
            color: #1A1A1A;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            line-height: 1.2;
        }
        .attendance-profile-header__name i {
            color: #D32F2F;
            font-size: 1.1rem;
        }
        .attendance-profile-header__badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.65rem;
        }
        .attendance-profile-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.65rem;
            background: #f8f9fa;
            color: #343a40;
            border-radius: 4px;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .attendance-profile-divider {
            opacity: 0.05;
            margin: 1rem 0 1.35rem;
        }
        .attendance-overview-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: #6c757d;
            letter-spacing: 1px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .attendance-overview-meta {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .attendance-overview-percent {
            font-size: 2rem;
            font-weight: 800;
            color: #000;
            line-height: 1;
        }
        .attendance-overview-empty {
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #6c757d;
        }
        .attendance-overview-chart-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.85rem;
        }
        .attendance-overview-chart-box {
            position: relative;
            width: 100%;
            max-width: 220px;
            height: 220px;
            margin: 0 auto;
        }
        .attendance-overview-message {
            font-size: 0.95rem;
            font-weight: 600;
            text-align: center;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            background: #f8f9fa;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .attendance-overview-message.text-success {
            background: rgba(40, 167, 69, 0.1);
        }
        .attendance-overview-message.text-warning {
            background: rgba(255, 152, 0, 0.12);
        }
        .attendance-overview-message.text-danger {
            background: rgba(211, 47, 47, 0.1);
        }
        .attendance-overview-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(120px, 1fr));
            gap: 0.75rem;
        }
        .attendance-stat-box {
            border-radius: 12px;
            padding: 0.9rem 1rem;
            border-left: 4px solid transparent;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }
        .attendance-stat-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.4rem 1rem rgba(15, 23, 42, 0.08);
        }
        .attendance-stat-box--present {
            background: rgba(40, 167, 69, 0.08);
            border-left-color: #28a745;
        }
        .attendance-stat-box--absent {
            background: rgba(211, 47, 47, 0.08);
            border-left-color: #d32f2f;
        }
        .attendance-stat-box--late {
            background: rgba(255, 152, 0, 0.1);
            border-left-color: #ff9800;
        }
        .attendance-stat-box--excused {
            background: rgba(2, 136, 209, 0.08);
            border-left-color: #0288d1;
        }
        .attendance-stat-box__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }
        .attendance-stat-box__icon {
            font-size: 1rem;
        }
        .attendance-stat-box__label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
        }
        .attendance-stat-box__value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #000;
        }
        .attendance-stat-box__icon--present,
        .attendance-stat-box__value--present {
            color: #28a745;
        }
        .attendance-stat-box__icon--absent,
        .attendance-stat-box__value--absent {
            color: #d32f2f;
        }
        .attendance-stat-box__icon--late,
        .attendance-stat-box__value--late {
            color: #ff9800;
        }
        .attendance-stat-box__icon--excused,
        .attendance-stat-box__value--excused {
            color: #0288d1;
        }
        .attendance-overview-summary {
            border-radius: 12px;
            padding: 1rem 1.1rem;
            background: #fafbfc;
            margin-bottom: 0.9rem;
        }
        .attendance-overview-summary__label {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 0.35rem;
        }
        .attendance-overview-summary__value {
            font-size: 1.8rem;
            font-weight: 800;
            color: #000;
            line-height: 1;
        }
    </style>

    @if(Qs::userIsTeamSA())
       @php $users = $users ?? collect(); @endphp
       <div class="row">
           <div class="col-sm-6 col-xl-3">
               <div class="card card-body dashboard-stat-card bg-brand-red has-bg-image">
                   <div class="media">
                       <div class="media-body">
                           <h3 class="mb-0">{{ $users->where('user_type', 'student')->count() }}</h3>
                           <span class="text-uppercase font-size-xs font-weight-bold">Total Students</span>
                       </div>
                       <div class="ml-3 align-self-center">
                           <i class="icon-users4 icon-3x opacity-75 text-white"></i>
                       </div>
                   </div>
               </div>
           </div>

           <div class="col-sm-6 col-xl-3">
               <div class="card card-body dashboard-stat-card bg-brand-black has-bg-image">
                   <div class="media">
                       <div class="media-body">
                           <h3 class="mb-0">{{ $users->where('user_type', 'teacher')->count() }}</h3>
                           <span class="text-uppercase font-size-xs font-weight-bold">Total Teachers</span>
                       </div>
                       <div class="ml-3 align-self-center">
                           <i class="icon-users2 icon-3x opacity-75 text-white"></i>
                       </div>
                   </div>
               </div>
           </div>

           <div class="col-sm-6 col-xl-3">
               <div class="card card-body dashboard-stat-card bg-brand-red has-bg-image">
                   <div class="media">
                       <div class="mr-3 align-self-center">
                           <i class="icon-pointer icon-3x opacity-75 text-white"></i>
                       </div>
                       <div class="media-body text-right">
                           <h3 class="mb-0">{{ $users->where('user_type', 'admin')->count() }}</h3>
                           <span class="text-uppercase font-size-xs font-weight-bold">Total Admins</span>
                       </div>
                   </div>
               </div>
           </div>

           <div class="col-sm-6 col-xl-3">
               <div class="card card-body dashboard-stat-card bg-brand-black has-bg-image">
                   <div class="media">
                       <div class="mr-3 align-self-center">
                           <i class="icon-user icon-3x opacity-75 text-white"></i>
                       </div>
                       <div class="media-body text-right">
                           <h3 class="mb-0">{{ $users->where('user_type', 'parent')->count() }}</h3>
                           <span class="text-uppercase font-size-xs font-weight-bold">Total Parents</span>
                       </div>
                   </div>
               </div>
           </div>
       </div>
       @elseif(Qs::userIsStudent() || Qs::userIsParent())
       <div class="row">
           <div class="col-12">
               <div class="card attendance-overview-card">
                   <div class="card-body">
                       <div class="attendance-profile-header">
                           <div class="attendance-profile-header__top d-flex justify-content-between align-items-start">
                               <div>
                                   @if($attendance_overview)
                                       <div class="attendance-profile-header__name">
                                           <i class="icon-user-check mr-2"></i>
                                           <span>{{ $attendance_overview['student_name'] }}</span>
                                       </div>
                                       <div class="attendance-profile-header__badges">
                                           <span class="attendance-profile-badge">ADM No: {{ $attendance_overview['adm_no'] }}</span>
                                           @if($attendance_overview['class_name'] || $attendance_overview['section_name'])
                                               <span class="attendance-profile-badge">{{ trim(($attendance_overview['class_name'] ?? '') . ' ' . ($attendance_overview['section_name'] ?? '')) }}</span>
                                           @endif
                                       </div>
                                   @endif
                               </div>
                               <div class="d-flex flex-column align-items-md-end align-items-start">
                                   <div class="attendance-overview-title mb-2">Attendance Overview</div>
                                   @if(Qs::userIsParent() && $attendance_children->count() > 0)
                                       <form method="get" action="{{ route('dashboard') }}" class="mt-2 mt-md-0">
                                           <select name="child_id" class="form-control select-search" onchange="this.form.submit()">
                                               @foreach($attendance_children as $child)
                                                   <option value="{{ $child->user_id }}" {{ (int) $selected_attendance_child_id === (int) $child->user_id ? 'selected' : '' }}>
                                                       {{ $child->user->name }} ({{ $child->adm_no }})
                                                   </option>
                                               @endforeach
                                           </select>
                                       </form>
                                   @endif
                               </div>
                           </div>
                       </div>
                       <hr class="my-3 attendance-profile-divider">

                       @if($attendance_overview && $attendance_overview['total'] > 0)
                           <div class="row align-items-center">
                               <div class="col-lg-5">
                                   <div class="attendance-overview-chart-wrap">
                                       <div class="attendance-overview-chart-box">
                                           <canvas id="attendance-overview-chart"></canvas>
                                       </div>
                                       <div class="attendance-overview-message {{ $attendance_overview['message_class'] }}">
                                           {{ $attendance_overview['message'] }}
                                       </div>
                                   </div>
                               </div>
                               <div class="col-lg-7">
                                   <div class="attendance-overview-summary">
                                       <div class="attendance-overview-summary__label">Current Attendance Rate</div>
                                       <div class="attendance-overview-summary__value">{{ number_format($attendance_overview['percentage'], 1) }}%</div>
                                   </div>

                                   <div class="attendance-overview-stats">
                                       <div class="attendance-stat-box attendance-stat-box--present">
                                           <div class="attendance-stat-box__head">
                                               <div class="attendance-stat-box__label">Present</div>
                                               <i class="icon-checkmark3 attendance-stat-box__icon attendance-stat-box__icon--present"></i>
                                           </div>
                                           <div class="attendance-stat-box__value attendance-stat-box__value--present">{{ $attendance_overview['present'] }}</div>
                                       </div>
                                       <div class="attendance-stat-box attendance-stat-box--absent">
                                           <div class="attendance-stat-box__head">
                                               <div class="attendance-stat-box__label">Absent</div>
                                               <i class="icon-cross2 attendance-stat-box__icon attendance-stat-box__icon--absent"></i>
                                           </div>
                                           <div class="attendance-stat-box__value attendance-stat-box__value--absent">{{ $attendance_overview['absent'] }}</div>
                                       </div>
                                       <div class="attendance-stat-box attendance-stat-box--late">
                                           <div class="attendance-stat-box__head">
                                               <div class="attendance-stat-box__label">Late</div>
                                               <i class="icon-watch2 attendance-stat-box__icon attendance-stat-box__icon--late"></i>
                                           </div>
                                           <div class="attendance-stat-box__value attendance-stat-box__value--late">{{ $attendance_overview['late'] }}</div>
                                       </div>
                                       <div class="attendance-stat-box attendance-stat-box--excused">
                                           <div class="attendance-stat-box__head">
                                               <div class="attendance-stat-box__label">Excused</div>
                                               <i class="icon-info22 attendance-stat-box__icon attendance-stat-box__icon--excused"></i>
                                           </div>
                                           <div class="attendance-stat-box__value attendance-stat-box__value--excused">{{ $attendance_overview['excused'] }}</div>
                                       </div>
                                   </div>
                               </div>
                           </div>
                       @else
                           <div class="attendance-overview-empty">
                               <div>
                                   <h6 class="mb-2 text-dark">No attendance data available for this session yet.</h6>
                                   <p class="mb-0">Attendance trends will appear here once records are marked.</p>
                               </div>
                           </div>
                       @endif
                   </div>
               </div>
           </div>
       </div>
       @else
       <div class="row">
           <div class="col-12">
               <div class="card card-body border-0 shadow-sm">
                   <h5 class="text-dark mb-0">Welcome, {{ Auth::user()->name }}</h5>
                   <p class="text-muted mb-0">Use the sidebar to navigate.</p>
               </div>
           </div>
       </div>
       @endif

    {{-- Events Calendar – hidden for MVP
    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title">School Events Calendar</h5>
         {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            <div class="fullcalendar-basic"></div>
        </div>
    </div>
    --}}
@endsection

@section('scripts')
@if(($attendance_overview['total'] ?? 0) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        var canvas = document.getElementById('attendance-overview-chart');
        if (!canvas) return;

        var centerTextPlugin = {
            id: 'attendanceCenterText',
            afterDraw: function(chart) {
                var meta = chart.getDatasetMeta(0);
                if (!meta || !meta.data || !meta.data.length) return;

                var x = meta.data[0].x;
                var y = meta.data[0].y;
                var ctx = chart.ctx;

                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillStyle = '#000000';
                ctx.font = '700 26px sans-serif';
                ctx.fillText('{{ number_format($attendance_overview['percentage'], 1) }}%', x, y - 8);
                ctx.font = '500 11px sans-serif';
                ctx.fillStyle = '#6c757d';
                ctx.fillText('Attendance', x, y + 18);
                ctx.restore();
            }
        };

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Late', 'Excused'],
                datasets: [{
                    data: [
                        {{ (int) ($attendance_overview['present'] ?? 0) }},
                        {{ (int) ($attendance_overview['absent'] ?? 0) }},
                        {{ (int) ($attendance_overview['late'] ?? 0) }},
                        {{ (int) ($attendance_overview['excused'] ?? 0) }}
                    ],
                    backgroundColor: ['#28a745', '#d32f2f', '#ff9800', '#0288d1'],
                    borderWidth: 0
                }]
            },
            plugins: [centerTextPlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    })();
</script>
@endif
@endsection
