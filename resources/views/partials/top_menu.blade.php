<div class="navbar navbar-expand-md navbar-light emaa-topbar">
    <div class="d-flex justify-content-between align-items-center w-100 emaa-topbar__inner">
        <div class="emaa-topbar__center">
            <h4 class="emaa-school-name">{{ $sysName ?? 'School Name' }}</h4>
        </div>

        <div class="emaa-topbar__right ml-auto">
            <div class="nav-item dropdown dropdown-user">
                <a href="#" class="navbar-nav-link dropdown-toggle emaa-user-toggle" data-toggle="dropdown">
                    <span class="emaa-user-icon"><i class="icon-user"></i></span>
                    <span class="emaa-user-meta text-left">
                        <span class="font-weight-semibold">{{ Auth::user()->name }}</span>
                        <span class="emaa-user-role">{{ ucwords(str_replace('_', ' ', Auth::user()->user_type)) }}</span>
                    </span>
                </a>

                <div class="dropdown-menu dropdown-menu-right">
                    <a href="{{ Qs::userIsStudent() ? route('students.my_record') : route('users.show', Qs::hash(Auth::user()->id)) }}" class="dropdown-item"><i class="icon-user-plus"></i> My profile</a>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('my_account') }}" class="dropdown-item"><i class="icon-cog5"></i> Account settings</a>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item"><i class="icon-switch2"></i> Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
