@extends('layouts.master')
@section('page_title', ($user->user_type === 'parent' ? 'Parent Profile' : 'User Profile') . ' - ' . $user->name)
@section('content')
<style>
    .user-profile-table { border-collapse: separate; border-spacing: 0; }
    .user-profile-table tbody tr { border-bottom: 1px solid #e5e7eb; }
    .user-profile-table tbody tr:last-child { border-bottom: 0; }
    .user-profile-table td { padding: 0.95rem 1.25rem; vertical-align: middle; }
    .user-profile-table td:first-child { font-weight: 700; color: #6b7280; width: 36%; min-width: 140px; background-color: #f9fafb; }
    .user-profile-table td:last-child { color: #1A1A1A; background-color: #fff; }
    .user-profile-table .profile-link { color: #1A1A1A !important; font-weight: bold; }
    .user-profile-table .profile-link:hover { color: #D32F2F !important; text-decoration: underline; }
    .user-profile-table .child-item { display: block; margin-bottom: 0.65rem; }
    .user-profile-table .child-item:last-child { margin-bottom: 0; }
    .user-profile-table .child-item .icon-user { margin-right: 0.35rem; opacity: 0.85; }
</style>
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                @if(Qs::userIsTeamSA())
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">{{ $user->user_type === 'parent' ? 'Parent Profile' : 'User Profile' }}</h6>
                    <a href="{{ route('users.edit', Qs::hash($user->id)) }}" class="btn btn-sm btn-light border"><i class="icon-pencil mr-1"></i> Edit</a>
                </div>
                @else
                <div class="card-header py-3">
                    <h6 class="card-title mb-0">{{ $user->user_type === 'parent' ? 'Parent Profile' : 'User Profile' }}</h6>
                </div>
                @endif
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-highlight">
                        <li class="nav-item">
                            <a href="#" class="nav-link active">{{ $user->name }}</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        {{--Basic Info--}}
                        <div class="tab-pane fade show active" id="basic-info">
                            <table class="table table-bordered user-profile-table">
                                <tbody>
                                <tr>
                                    <td>User Type</td>
                                    <td>{{ ucfirst($user->user_type) }}</td>
                                </tr>
                                <tr>
                                    <td>Email</td>
                                    <td>{{ $user->email ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td>Address</td>
                                    <td>{{ isset($display_address) && trim($display_address ?? '') !== '' ? $display_address : '—' }}</td>
                                </tr>
                                @if($user->phone)
                                    <tr>
                                        <td>Phone</td>
                                        <td>{{ trim($user->phone.' '.$user->phone2) }}</td>
                                    </tr>
                                @endif
                                @if($user->user_type == 'parent')
                                    <tr>
                                        <td>Children/Ward</td>
                                        <td>
                                            @foreach(Qs::findMyChildren($user->id) as $sr)
                                                <a class="profile-link child-item" href="{{ route('students.show', Qs::hash($sr->id)) }}"><i class="icon-user"></i> {{ $sr->user->name }} — {{ $sr->my_class->name }} {{ $sr->section->name }}</a>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endif

                                @if($user->user_type == 'teacher')
                                    <tr>
                                        <td>My Subjects</td>
                                        <td>
                                            @foreach(Qs::findTeacherSubjects($user->id) as $sub)
                                                <span class="d-block mb-1">{{ $sub->name }} ({{ $sub->my_class->name }})</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endif

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


    {{--User Profile Ends--}}

@endsection
