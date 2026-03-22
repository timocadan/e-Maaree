@extends('layouts.master')
@section('page_title', 'Edit User')
@section('content')
<style>
    .user-edit-form .card { max-width: 100%; }
    .user-edit-form .form-group label { font-weight: 600; color: #374151; }
    .user-edit-form .btn-brand-update { background-color: #D32F2F; color: #fff; border: 1px solid #D32F2F; font-weight: 500; padding: 0.5rem 1.25rem; }
    .user-edit-form .btn-brand-update:hover { background-color: #b71c1c; border-color: #b71c1c; color: #fff; }
</style>

    <div class="row user-edit-form">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title">Edit User Details</h6>
                    {!! Qs::getPanelOptions() !!}
                </div>

                <div class="card-body py-4 px-4 px-md-5">
                    <form method="post" enctype="multipart/form-data" class="ajax-update" action="{{ route('users.update', Qs::hash($user->id)) }}">
                        @csrf
                        @method('PUT')

                        {{-- Row 1: User Type (read-only), Full Name, Address — same layout as Create --}}
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>User Type:</label>
                                    <input type="text" class="form-control bg-light" value="{{ ucfirst($user->user_type) }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Full Name: <span class="text-danger">*</span></label>
                                    <input value="{{ old('name', $user->name) }}" required type="text" name="name" placeholder="Full Name" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Address: <span class="text-danger">*</span></label>
                                    <input value="{{ old('address', $user->address ?? '') }}" class="form-control" placeholder="Address" name="address" type="text" required>
                                </div>
                            </div>
                        </div>

                        {{-- Row 2: Email, Phone, Gender — same as Create --}}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Email address:</label>
                                    <input value="{{ old('email', $user->email ?? '') }}" type="email" name="email" class="form-control" placeholder="your@email.com">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Phone:</label>
                                    <input value="{{ old('phone', $user->phone ?? '') }}" type="text" name="phone" class="form-control" placeholder="+2341234567">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="gender">Gender:</label>
                                    <select class="select form-control" id="gender" name="gender" data-fouc data-placeholder="Choose..">
                                        <option value=""></option>
                                        <option value="Male" {{ old('gender', $user->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender', $user->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Row 3: Date of Employment, Password — same as Create --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date of Employment:</label>
                                    <input autocomplete="off" name="emp_date" value="{{ old('emp_date', optional(optional($user->staff)->first())->emp_date ?? '') }}" type="text" class="form-control date-pick" placeholder="Select Date...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Password:</label>
                                    <input id="password" type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                                </div>
                            </div>
                        </div>

                        {{-- Hidden: optional fields for update (validation nullable) --}}
                        <input type="hidden" name="phone2" value="{{ $user->phone2 ?? '' }}">
                        <input type="hidden" name="nal_id" value="{{ $user->nal_id ?? '' }}">
                        <input type="hidden" name="state_id" value="{{ $user->state_id ?? '' }}">
                        <input type="hidden" name="lga_id" value="{{ $user->lga_id ?? '' }}">
                        <input type="hidden" name="bg_id" value="{{ $user->bg_id ?? '' }}">

                        <div class="d-flex justify-content-end mt-4 pt-3">
                            <button type="submit" class="btn btn-brand-update">
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
