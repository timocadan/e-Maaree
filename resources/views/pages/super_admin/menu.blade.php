{{--Manage Settings--}}
<li class="nav-item">
    <a href="{{ route('settings') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['settings',]) ? 'active' : '' }}"><i class="icon-gear"></i> <span>Settings</span></a>
</li>

{{-- Manage Levels (Class Types) --}}
<li class="nav-item">
    <a href="{{ route('levels.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['levels.index']) ? 'active' : '' }}"><i class="icon-graduation2"></i> <span>Manage Levels</span></a>
</li>

{{-- Pins (Result PINs) – hidden for MVP --}}
{{--
<li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['pins.create', 'pins.index']) ? 'nav-item-expanded nav-item-open' : '' }} ">
    <a href="#" class="nav-link"><i class="icon-lock2"></i> <span> Pins</span></a>
    <ul class="nav nav-group-sub" data-submenu-title="Manage Pins">
        <li class="nav-item">
            <a href="{{ route('pins.create') }}"
               class="nav-link {{ (Route::is('pins.create')) ? 'active' : '' }}">Generate Pins</a>
        </li>
        <li class="nav-item">
            <a href="{{ route('pins.index') }}"
               class="nav-link {{ (Route::is('pins.index')) ? 'active' : '' }}">View Pins</a>
        </li>
    </ul>
</li>
--}}