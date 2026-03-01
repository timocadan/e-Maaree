@extends('layouts.master')
@section('page_title', 'My Dashboard')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="text-dark mb-0">Welcome, {{ Auth::user()->name }}</h5>
            <p class="text-muted mb-0">Use the sidebar to navigate.</p>
        </div>
    </div>
@endsection