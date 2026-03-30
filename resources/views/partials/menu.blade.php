<div class="sidebar sidebar-dark sidebar-main sidebar-expand-md">
    <!-- Sidebar content -->
    <div class="sidebar-content">
        <div class="emaa-sidebar-header">
            <a href="{{ route('dashboard') }}" class="emaa-brand"><span class="brand-text-logo">e-maaree</span></a>
            <a href="#" class="emaa-sidebar-toggle sidebar-control sidebar-main-toggle d-none d-md-inline-flex" aria-label="Toggle sidebar">
                <i class="icon-paragraph-justify3"></i>
            </a>
            <a href="#" class="emaa-sidebar-toggle sidebar-mobile-main-toggle d-inline-flex d-md-none" aria-label="Toggle sidebar">
                <i class="icon-paragraph-justify3"></i>
            </a>
        </div>

        <!-- Main navigation -->
        <div class="card card-sidebar-mobile">
            <ul class="nav nav-sidebar" data-nav-type="accordion">

                <!-- Main -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ (Route::is('dashboard')) ? 'active' : '' }}">
                        <i class="icon-home4"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{--Payments--}}
                @if(Qs::userIsAdministrative() && Qs::userIsTeamAccount())
                    <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['payments.index', 'payments.create', 'payments.invoice', 'payments.receipts', 'payments.edit', 'payments.manage', 'payments.show']) ? 'nav-item-expanded nav-item-open' : '' }}">
                        <a href="#" class="nav-link {{ Route::is('payments.*') ? 'active' : '' }}"><i class="icon-cash2"></i> <span>Payments</span></a>
                        <ul class="nav nav-group-sub" data-submenu-title="Payments">
                            <li class="nav-item"><a href="{{ route('payments.create') }}" class="nav-link {{ Route::is('payments.create') ? 'active' : '' }}">Create Payment</a></li>
                            <li class="nav-item"><a href="{{ route('payments.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['payments.index', 'payments.edit', 'payments.show']) ? 'active' : '' }}">Manage Payments</a></li>
                            <li class="nav-item"><a href="{{ route('payments.manage') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['payments.manage', 'payments.invoice', 'payments.receipts']) ? 'active' : '' }}">Student Payments</a></li>
                        </ul>
                    </li>
                @endif

                {{--Manage Students--}}
                @if(Qs::userIsTeamSAT())
                    <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.create', 'students.list', 'students.edit', 'students.show', 'students.promotion', 'students.promotion_manage', 'students.graduated']) ? 'nav-item-expanded nav-item-open' : '' }} ">
                        <a href="#" class="nav-link"><i class="icon-users"></i> <span> Students</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Manage Students">
                            {{--Admit Student--}}
                            @if(Qs::userIsTeamSA())
                                <li class="nav-item">
                                    <a href="{{ route('students.create') }}"
                                       class="nav-link {{ (Route::is('students.create')) ? 'active' : '' }}">Admit Student</a>
                                </li>
                            @endif

                            {{--Student Information--}}
                            <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.list', 'students.edit', 'students.show']) ? 'nav-item-expanded' : '' }}">
                                <a href="#" class="nav-link {{ in_array(Route::currentRouteName(), ['students.list', 'students.edit', 'students.show']) ? 'active' : '' }}">Student Information</a>
                                <ul class="nav nav-group-sub">
                                    @foreach(App\Models\MyClass::orderBy('name')->get() as $c)
                                        <li class="nav-item"><a href="{{ route('students.list', $c->id) }}" class="nav-link ">{{ $c->name }}</a></li>
                                    @endforeach
                                </ul>
                            </li>

                            @if(Qs::userIsTeamSA())

                            {{--Student Promotion--}}
                            <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.promotion', 'students.promotion_manage']) ? 'nav-item-expanded' : '' }}"><a href="#" class="nav-link {{ in_array(Route::currentRouteName(), ['students.promotion', 'students.promotion_manage' ]) ? 'active' : '' }}">Student Promotion</a>
                            <ul class="nav nav-group-sub">
                                <li class="nav-item"><a href="{{ route('students.promotion') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.promotion']) ? 'active' : '' }}">Promote Students</a></li>
                                <li class="nav-item"><a href="{{ route('students.promotion_manage') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.promotion_manage']) ? 'active' : '' }}">Manage Promotions</a></li>
                            </ul>

                            </li>

                            {{--Student Graduated--}}
                            <li class="nav-item"><a href="{{ route('students.graduated') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.graduated' ]) ? 'active' : '' }}">Students Graduated</a></li>
                                @endif

                        </ul>
                    </li>
                @endif

                @if(Qs::userIsTeamSA())
                    {{--Manage Users--}}
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['users.index', 'users.show', 'users.edit']) ? 'active' : '' }}">
                            <i class="icon-users4"></i> <span>Users</span>
                        </a>
                    </li>
                    @if(Qs::userIsSuperAdmin())
                        <li class="nav-item">
                            <a href="{{ route('users.parents') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['users.parents']) ? 'active' : '' }}">
                                <i class="icon-users"></i> <span>Manage Parents</span>
                            </a>
                        </li>
                    @endif

                    {{--Manage Classes--}}
                    <li class="nav-item">
                        <a href="{{ route('classes.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['classes.index','classes.edit']) ? 'active' : '' }}"><i class="icon-windows2"></i> <span> Classes</span></a>
                    </li>

                    {{-- Dormitories (Jiifka) – hidden for MVP --}}
                    {{--
                    <li class="nav-item">
                        <a href="{{ route('dorms.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['dorms.index','dorms.edit']) ? 'active' : '' }}"><i class="icon-home9"></i> <span> Dormitories</span></a>
                    </li>
                    --}}

                    {{--Manage Sections--}}
                    <li class="nav-item">
                        <a href="{{ route('sections.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['sections.index','sections.edit',]) ? 'active' : '' }}"><i class="icon-fence"></i> <span>Sections</span></a>
                    </li>

                    {{--Manage Subjects--}}
                    <li class="nav-item">
                        <a href="{{ route('subjects.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['subjects.index','subjects.edit',]) ? 'active' : '' }}"><i class="icon-pin"></i> <span>Subjects</span></a>
                    </li>
                @endif

                {{-- Marks & Results (term-based; no Exam list) --}}
                @if(Qs::userIsTeamSAT())
                <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['grades.index', 'grades.edit', 'marks.index', 'marks.manage', 'marks.tabulation', 'marks.show']) ? 'nav-item-expanded nav-item-open' : '' }} ">
                    <a href="#" class="nav-link"><i class="icon-books"></i> <span> Marks & Results</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Marks & Results">
                        @if(Qs::userIsTeamSAT())
                            {{--Grades list--}}
                            <li class="nav-item">
                                    <a href="{{ route('grades.index') }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), ['grades.index', 'grades.edit']) ? 'active' : '' }}">Grades</a>
                            </li>
                        @endif

                        @if(Qs::userIsTeamSA())
                            {{--Tabulation Sheet--}}
                            <li class="nav-item">
                                <a href="{{ route('marks.tabulation') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.tabulation']) ? 'active' : '' }}">Tabulation Sheet</a>
                            </li>
                        @endif

                        @if(Qs::userIsTeamSAT())
                            {{--Marks Manage--}}
                            <li class="nav-item">
                                <a href="{{ route('marks.index') }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), ['marks.index']) ? 'active' : '' }}">Marks</a>
                            </li>

                            {{--Class Master Dashboard (Teacher mapped to a Section only) --}}
                            @if(Qs::userIsTeacher() && \App\Models\Section::where('teacher_id', Auth::user()->id)->exists())
                            <li class="nav-item">
                                <a href="{{ route('class_master.dashboard') }}" class="nav-link {{ Route::is('class_master.dashboard') ? 'active' : '' }}">Class Master Dashboard</a>
                            </li>
                            @endif

                            @endif

                    </ul>
                </li>
                @endif

                {{-- Attendance --}}
                @if(Qs::userIsTeamSAT())
                    <li class="nav-item nav-item-submenu {{ Route::is('attendance.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                        <a href="#" class="nav-link {{ Route::is('attendance.*') ? 'active' : '' }}">
                            <i class="icon-calendar3"></i> <span>Attendance</span>
                        </a>
                        <ul class="nav nav-group-sub" data-submenu-title="Attendance">
                            <li class="nav-item">
                                <a href="{{ route('attendance.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['attendance.index', 'attendance.show_marking_grid']) ? 'active' : '' }}">
                                    <i class="icon-calendar3 mr-2"></i> Mark Attendance
                                </a>
                            </li>
                            @if(Qs::userIsTeamSA())
                                <li class="nav-item">
                                    <a href="{{ route('attendance.report') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['attendance.report', 'attendance.report_show']) ? 'active' : '' }}">
                                        <i class="icon-stats-bars mr-2"></i> Attendance Report
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif


                {{--End Exam--}}

                @include('pages.'.Qs::getUserType().'.menu')

                {{--Manage Account--}}
                <li class="nav-item">
                    <a href="{{ route('my_account') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['my_account']) ? 'active' : '' }}"><i class="icon-user"></i> <span>My Account</span></a>
                </li>

                </ul>
            </div>
        </div>
</div>
