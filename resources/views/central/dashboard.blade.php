@extends('central.layout')

@section('title', 'Landlord Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0 text-dark font-weight-bold">SaaS Control Center</h1>
    <button type="button" class="btn btn-brand" data-toggle="modal" data-target="#createSchoolModal">
        <i class="fas fa-plus mr-1"></i> Add New School
    </button>
</div>

{{-- Stat Cards --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="background: #1A1A1A; color: #fff;">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-dark p-3 mr-3"><i class="fas fa-school fa-lg text-white"></i></div>
                <div>
                    <div class="text-white-50 small">Total Schools</div>
                    <div class="h4 mb-0 font-weight-bold">{{ $totalSchools ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="background: #2E7D32; color: #fff;">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-dark p-3 mr-3"><i class="fas fa-check-circle fa-lg text-white"></i></div>
                <div>
                    <div class="text-white-50 small">Active Schools</div>
                    <div class="h4 mb-0 font-weight-bold">{{ $activeSchools ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="background: #555; color: #fff;">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-dark p-3 mr-3"><i class="fas fa-pause-circle fa-lg text-white"></i></div>
                <div>
                    <div class="text-white-50 small">Inactive Schools</div>
                    <div class="h4 mb-0 font-weight-bold">{{ $inactiveSchools ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="background: #D32F2F; color: #fff;">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-dark p-3 mr-3"><i class="fas fa-chart-line fa-lg text-white"></i></div>
                <div>
                    <div class="text-white-50 small">Total Revenue</div>
                    <div class="h4 mb-0 font-weight-bold">—</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex flex-wrap align-items-center justify-content-between">
        <span class="font-weight-semibold">Schools (Tenants)</span>
        @if(!$tenants->isEmpty())
        <div class="mt-2 mt-md-0" style="min-width: 220px;">
            <div class="input-group input-group-sm">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input type="text" class="form-control border-left-0 schools-search" id="schoolsSearch" placeholder="Search by name, ID or domain..." aria-label="Search schools">
            </div>
        </div>
        @endif
    </div>
    <div class="card-body p-0">
        @if($tenants->isEmpty())
            <p class="text-muted p-4 mb-0">No schools yet. Click "Add New School" to create one.</p>
        @else
            @php $schoolCount = $tenants->count(); @endphp
            <div class="schools-table-wrapper">
            <div id="schoolsTableNoMatch" class="alert alert-light border-0 mb-0 rounded-0 text-muted text-center d-none" role="status">No schools match your search.</div>
            <div class="table-responsive {{ $schoolCount > 10 ? 'schools-table-scroll' : 'schools-table-no-scroll' }}" @if($schoolCount > 10) style="max-height: 520px; overflow-y: auto;" @endif>
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>School Name</th>
                            <th>Domain(s)</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="schoolsTableBody">
                        @foreach($tenants as $tenant)
                        @php $status = $tenant->getAttribute('status') ?? 'active'; $isActive = ($status === 'active'); @endphp
                        <tr class="school-row" data-school-id="{{ $tenant->id }}" data-school-name="{{ strtolower(e($tenant->getAttribute('name') ?? $tenant->id)) }}" data-school-domains="{{ strtolower(e($tenant->domains->pluck('domain')->implode(' '))) }}">
                            <td><code>{{ $tenant->id }}</code></td>
                            <td>{{ $tenant->getAttribute('name') ?? $tenant->id }}</td>
                            <td>
                                @forelse($tenant->domains as $domain)
                                    <span class="badge badge-secondary">{{ $domain->domain }}</span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td>
                                @if($isActive)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-warning text-dark">Suspended</span>
                                @endif
                            </td>
                            <td>{{ $tenant->created_at->format('M j, Y') }}</td>
                            <td class="text-right">
                                <div class="dropdown d-inline-block" data-boundary="viewport">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>
                                    <div class="dropdown-menu dropdown-menu-right schools-actions-menu" style="min-width: 11rem; overflow: visible; max-height: none;">
                                        <a class="dropdown-item" href="{{ route('landlord.schools.show', $tenant->id) }}"><i class="fas fa-cog text-muted mr-2"></i>Manage</a>
                                        <form method="POST" action="{{ route('landlord.schools.toggle', $tenant->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="dropdown-item border-0 bg-transparent w-100 text-left">
                                                @if($isActive)
                                                    <i class="fas fa-pause text-warning mr-2"></i>Suspend
                                                @else
                                                    <i class="fas fa-play text-success mr-2"></i>Activate
                                                @endif
                                            </button>
                                        </form>
                                        <div class="dropdown-divider"></div>
                                        <button type="button" class="dropdown-item text-danger" data-toggle="modal" data-target="#deleteModal" data-tenant-id="{{ $tenant->id }}" data-tenant-name="{{ $tenant->getAttribute('name') ?? $tenant->id }}">
                                            <i class="fas fa-trash-alt mr-2"></i>Delete
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            </div>
        @endif
    </div>
</div>

{{-- Create School Modal --}}
<div class="modal fade" id="createSchoolModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ url('/dashboard/schools') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New School</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="school_id">School ID <small class="text-muted">(e.g. hormuud)</small></label>
                        <input type="text" class="form-control @error('school_id') is-invalid @enderror" id="school_id" name="school_id" value="{{ old('school_id') }}" placeholder="Lowercase letters, numbers, hyphens only" required>
                        @error('school_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="school_name">School Name</label>
                        <input type="text" class="form-control @error('school_name') is-invalid @enderror" id="school_name" name="school_name" value="{{ old('school_name') }}" placeholder="e.g. Hormuud School" required>
                        @error('school_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="domain">Domain</label>
                        <input type="text" class="form-control @error('domain') is-invalid @enderror" id="domain" name="domain" value="{{ old('domain') }}" placeholder="e.g. hormuud.localhost" required>
                        @error('domain')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand">Create School</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete confirmation modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Delete School</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="mb-0">This will permanently remove the school <strong id="deleteTenantName"></strong> and its database. This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteTenantForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete School</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.schools-actions-menu { overflow: visible !important; max-height: none !important; }
</style>
@endpush

@push('scripts')
<script>
(function() {
    $('#deleteModal').on('show.bs.modal', function(e) {
        var id = $(e.relatedTarget).data('tenant-id');
        var name = $(e.relatedTarget).data('tenant-name');
        $('#deleteTenantName').text(name);
        $('#deleteTenantForm').attr('action', '{{ url("/dashboard/schools") }}/' + encodeURIComponent(id));
    });

    $('#schoolsSearch').on('input', function() {
        var q = $(this).val().trim().toLowerCase();
        var $rows = $('#schoolsTableBody .school-row');
        var visible = 0;
        $rows.each(function() {
            var $row = $(this);
            var name = ($row.data('school-name') || '').toLowerCase();
            var id = ($row.data('school-id') || '').toLowerCase();
            var domains = ($row.data('school-domains') || '').toLowerCase();
            var match = !q || name.indexOf(q) !== -1 || id.indexOf(q) !== -1 || domains.indexOf(q) !== -1;
            $row.toggle(match);
            if (match) visible++;
        });
        $('#schoolsTableNoMatch').toggleClass('d-none', visible > 0 || !q);
    });
})();
</script>
@endpush
@endsection
