<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta id="csrf-token" name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="ABQO Technology">

    <title> @yield('page_title') | {{ config('app.name') }} </title>

    @include('partials.inc_top')
    <style>
        html,
        body {
            height: 100%;
            overflow: hidden;
        }
        .page-content {
            display: block !important;
            position: relative;
            min-height: 100vh;
        }
        .emaa-topbar {
            background: #D32F2F;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            min-height: 64px;
            padding: 0 1.25rem;
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1020;
        }
        .emaa-topbar__inner {
            min-height: 64px;
            position: relative;
        }
        .emaa-topbar__right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 220px;
        }
        .emaa-topbar__right {
            justify-content: flex-end;
        }
        .emaa-topbar__center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            max-width: calc(100% - 280px);
            text-align: center;
            pointer-events: none;
        }
        .emaa-sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            min-height: 64px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
            overflow: visible;
            position: sticky;
            top: 0;
            z-index: 1020;
            background-color: #1f1f1f;
        }
        .emaa-brand {
            color: #ffffff !important;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-decoration: none !important;
            white-space: nowrap;
            overflow: hidden;
        }
        .brand-text-logo {
            display: inline-block;
            white-space: nowrap;
        }
        .emaa-sidebar-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            color: #ffffff !important;
            border-radius: 6px;
            text-decoration: none !important;
            flex-shrink: 0;
            position: relative;
            z-index: 20;
        }
        .emaa-sidebar-toggle:hover,
        .emaa-sidebar-toggle:focus {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff !important;
        }
        .emaa-school-name {
            margin: 0;
            color: #ffffff;
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .emaa-user-toggle {
            display: inline-flex !important;
            align-items: center;
            gap: 0.75rem;
            color: #ffffff !important;
            padding: 0.5rem 0 !important;
            text-decoration: none !important;
        }
        .emaa-user-toggle:hover,
        .emaa-user-toggle:focus {
            color: #ffffff !important;
        }
        .emaa-user-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            font-size: 1rem;
        }
        .emaa-user-toggle .emaa-user-icon {
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
        }
        .emaa-user-meta {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
            color: #ffffff;
        }
        .emaa-user-role {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.85);
            opacity: 1;
        }
        .emaa-topbar .dropdown-toggle::after,
        .emaa-topbar .dropdown-toggle,
        .emaa-topbar .dropdown-toggle i,
        .emaa-topbar .navbar-nav-link {
            color: #ffffff !important;
        }
        .sidebar {
            position: fixed !important;
            top: 0;
            bottom: 0;
            left: 0;
            width: 260px !important;
            min-height: 100vh;
            height: 100vh !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            scrollbar-width: thin;
            scrollbar-color: #D32F2F transparent;
            z-index: 1030;
            transition: all 0.3s ease;
        }
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background-color: #D32F2F !important;
            border-radius: 4px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background-color: #b71c1c !important;
        }
        .sidebar-main .sidebar-content {
            height: auto !important;
            overflow: visible !important;
        }
        /* Prevent icon + label overlap on top-level sidebar links (e.g. parent "My Children") */
        .sidebar .nav-sidebar > .nav-item:not(.nav-item-submenu) > .nav-link {
            display: flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            flex-wrap: nowrap !important;
        }
        .sidebar .nav-sidebar > .nav-item:not(.nav-item-submenu) > .nav-link > i {
            flex-shrink: 0 !important;
        }
        .sidebar .nav-sidebar > .nav-item:not(.nav-item-submenu) > .nav-link > span {
            flex: 1 1 auto !important;
            min-width: 0 !important;
            line-height: 1.25 !important;
        }
        .content-wrapper {
            display: flex !important;
            flex-direction: column;
            position: relative;
            margin-left: 260px !important;
            width: calc(100% - 260px);
            min-height: 100vh;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            transition: all 0.3s ease;
        }
        .content {
            padding-top: 0;
            flex: 1 0 auto;
        }
        .sidebar-xs .brand-text-logo {
            display: none !important;
        }
        .sidebar-xs .sidebar {
            width: 60px !important;
        }
        .sidebar-xs .content-wrapper {
            margin-left: 60px !important;
            width: calc(100% - 60px);
        }
        .sidebar-xs .emaa-sidebar-header {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .sidebar-xs .emaa-brand {
            width: 0 !important;
            min-width: 0 !important;
            margin: 0 !important;
            overflow: hidden !important;
        }
        .sidebar-xs .emaa-sidebar-toggle {
            margin: 0 auto !important;
            position: relative;
            left: 0;
            right: 0;
        }
        @media (max-width: 767.98px) {
            html,
            body {
                overflow-y: auto;
            }
            .sidebar {
                position: fixed !important;
                width: 260px !important;
            }
            .content-wrapper {
                margin-left: 0 !important;
                width: 100%;
                height: auto;
                min-height: 100vh;
                overflow: visible;
            }
            .emaa-topbar {
                padding: 0 0.75rem;
            }
            .emaa-topbar__right {
                min-width: 0;
                gap: 0.5rem;
            }
            .emaa-topbar__center {
                max-width: calc(100% - 170px);
            }
            .emaa-school-name {
                font-size: 1rem;
            }
            .emaa-user-meta {
                display: none;
            }
        }
    </style>
</head>

<body class="{{ in_array(Route::currentRouteName(), ['payments.invoice', 'marks.tabulation', 'marks.show', 'ttr.manage', 'ttr.show']) ? 'sidebar-xs' : '' }}">
<div class="page-content">
    @include('partials.menu')
    <div class="content-wrapper">
        @include('partials.top_menu')
        @include('partials.header')

        <div class="content">
            {{--Error Alert Area--}}
            @if($errors->any())
                <div class="alert alert-danger border-0 alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>

                        @foreach($errors->all() as $er)
                            <span><i class="icon-arrow-right5"></i> {{ $er }}</span> <br>
                        @endforeach

                </div>
            @endif
            <div id="ajax-alert" style="display: none"></div>

            @yield('content')
        </div>

        @include('partials.footer')
    </div>
</div>

@include('partials.inc_bottom')
@yield('scripts')
</body>
</html>
