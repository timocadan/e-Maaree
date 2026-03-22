@extends('layouts.master')
@section('page_title', 'Student Profile - '.$sr->user->name)
@section('content')
<style>
    .student-profile-table { border-collapse: separate; border-spacing: 0; }
    .student-profile-table td { padding: 0.9rem 1.25rem; vertical-align: middle; }
    .student-profile-table td:first-child { font-weight: 600; color: #6b7280; width: 36%; background-color: #f9fafb; }
    .student-profile-table td:last-child { color: #1A1A1A; background-color: #fff; }
    .student-profile-table .parent-link { color: #1A1A1A !important; font-weight: bold; }
    .student-profile-table .parent-link:hover { color: #D32F2F !important; text-decoration: underline; }
</style>
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-highlight">
                    <li class="nav-item">
                        <a href="#" class="nav-link active">{{ $sr->user->name }}</a>
                    </li>
                </ul>

                <div class="tab-content">
                    {{--Basic Info--}}
                    <div class="tab-pane fade show active" id="basic-info">
                        <table class="table table-bordered student-profile-table">
                            <tbody>
                            <tr>
                                <td>Name</td>
                                <td>{{ $sr->user->name }}</td>
                            </tr>
                            <tr>
                                <td>ADM_NO</td>
                                <td>{{ $sr->adm_no }}</td>
                            </tr>
                            <tr>
                                <td>Class</td>
                                <td>{{ $sr->my_class->name }}</td>
                            </tr>
                            <tr>
                                <td>Section</td>
                                <td>{{ $sr->section->name }}</td>
                            </tr>
                            @if($sr->my_parent_id)
                                <tr>
                                    <td>Parent</td>
                                    <td>
                                        <a class="parent-link" target="_blank" href="{{ route('users.show', Qs::hash($sr->my_parent_id)) }}">{{ $sr->my_parent->name ?? '—' }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Parent Phone</td>
                                    <td>{{ $sr->my_parent->phone ?? '—' }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td>Year Admitted</td>
                                <td>{{ $sr->year_admitted }}</td>
                            </tr>
                            <tr>
                                <td>Gender</td>
                                <td>{{ $sr->user->gender }}</td>
                            </tr>
                            <tr>
                                <td>Address</td>
                                <td>{{ $sr->user->address }}</td>
                            </tr>
                            @if($sr->user->email)
                            <tr>
                                <td>Email</td>
                                <td>{{$sr->user->email }}</td>
                            </tr>
                            @endif
                            @if($sr->user->phone)
                                <tr>
                                    <td>Phone</td>
                                    <td>{{$sr->user->phone.' '.$sr->user->phone2 }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td>Birthday</td>
                                <td>{{$sr->user->dob }}</td>
                            </tr>
                            @if($sr->user->bg_id)
                            <tr>
                                <td>Blood Group</td>
                                <td>{{$sr->user->blood_group->name }}</td>
                            </tr>
                            @endif
                            @if($sr->user->nal_id)
                            <tr>
                                <td>Nationality</td>
                                <td>{{$sr->user->nationality->name }}</td>
                            </tr>
                            @endif
                            @if($sr->user->state_id)
                            <tr>
                                <td>State</td>
                                <td>{{$sr->user->state->name }}</td>
                            </tr>
                            @endif
                            @if($sr->user->lga_id)
                            <tr>
                                <td>LGA</td>
                                <td>{{$sr->user->lga->name }}</td>
                            </tr>
                            @endif
                            @if($sr->dorm_id)
                                <tr>
                                    <td>Dormitory</td>
                                    <td>{{$sr->dorm->name.' '.$sr->dorm_room_no }}</td>
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


    {{--Student Profile Ends--}}

@endsection
