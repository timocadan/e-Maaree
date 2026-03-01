@extends('layouts.master')
@section('page_title', 'My Dashboard')
@section('content')

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
