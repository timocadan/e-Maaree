@extends('central.layout')

@section('title', ($tenant->getAttribute('name') ?? $tenant->id) . ' – Manage')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('landlord.dashboard') }}">Schools</a></li>
            <li class="breadcrumb-item active">{{ $tenant->getAttribute('name') ?? $tenant->id }}</li>
        </ol>
    </nav>
    <a href="{{ route('landlord.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back to dashboard</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white font-weight-semibold"><i class="fas fa-edit text-muted mr-2"></i>School details</div>
            <div class="card-body">
                <form method="POST" action="{{ route('landlord.schools.update', $tenant->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="school_name">School Name</label>
                        <input type="text" class="form-control @error('school_name') is-invalid @enderror" id="school_name" name="school_name" value="{{ old('school_name', $tenant->getAttribute('name') ?? $tenant->id) }}" required>
                        @error('school_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="domain">Domain</label>
                        <input type="text" class="form-control @error('domain') is-invalid @enderror" id="domain" name="domain" value="{{ old('domain', $primaryDomain->domain ?? '') }}" placeholder="e.g. hormuud.localhost" required>
                        @error('domain')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-brand"><i class="fas fa-save mr-1"></i>Save changes</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white font-weight-semibold"><i class="fas fa-user-shield text-muted mr-2"></i>Super Admin</div>
            <div class="card-body">
                @if($superAdmin)
                    <p class="mb-2"><strong>Email</strong></p>
                    <p class="text-muted mb-3">{{ $superAdmin->email }}</p>
                    <hr>
                    <p class="small text-muted mb-2">Reset this school's super admin password:</p>
                    <form method="POST" action="{{ route('landlord.schools.reset-password', $tenant->id) }}">
                        @csrf
                        <div class="form-group">
                            <label for="password" class="small">New password</label>
                            <input type="password" class="form-control form-control-sm @error('password') is-invalid @enderror" id="password" name="password" required minlength="6">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation" class="small">Confirm password</label>
                            <input type="password" class="form-control form-control-sm" id="password_confirmation" name="password_confirmation" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-brand btn-sm"><i class="fas fa-key mr-1"></i>Reset password</button>
                    </form>
                @else
                    <p class="text-muted mb-0">No super admin user found in this school's database.</p>
                @endif
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="mb-2"><strong>School ID</strong></p>
                <p class="text-muted mb-0"><code>{{ $tenant->id }}</code></p>
                @php $schoolStatus = $tenant->getAttribute('status') ?? 'active'; @endphp
                <p class="mb-2 mt-3"><strong>Status</strong></p>
                <p class="mb-0">
                    @if($schoolStatus === 'active')
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-warning text-dark">Suspended</span>
                    @endif
                </p>
                <hr>
                <form method="POST" action="{{ route('landlord.schools.toggle', $tenant->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm {{ $schoolStatus === 'suspended' ? 'btn-success' : 'btn-warning' }}">
                        @if($schoolStatus === 'suspended')
                            <i class="fas fa-play mr-1"></i>Activate School
                        @else
                            <i class="fas fa-pause mr-1"></i>Suspend School
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
