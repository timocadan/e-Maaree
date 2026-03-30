{{-- My Payments / Fees --}}
<li class="nav-item">
    <a href="{{ route('payments.invoice', Qs::hash(Auth::user()->id)) }}" class="nav-link {{ in_array(Route::currentRouteName(), ['payments.invoice', 'payments.receipts']) ? 'active' : '' }}"><i class="icon-cash2"></i> <span>My Payments</span></a>
</li>

{{--Marksheet--}}
<li class="nav-item">
    <a href="{{ route('marks.year_selector') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.show', 'marks.year_selector']) ? 'active' : '' }}"><i class="icon-book"></i> <span>Marksheet</span></a>
</li>
