@extends('layouts.master')
@section('page_title', 'Manage Levels')
@section('content')

<style>
/* Manage Levels – scoped overrides (inline so they always load) */
.levels-card .card-header { border-bottom: 1px solid #e5e7eb; }
.levels-card .btn-add-level { background: #fff; border: 1px solid #D32F2F; color: #D32F2F; padding: 0.375rem 0.75rem; font-size: 0.875rem; border-radius: 6px; }
.levels-card .btn-add-level:hover { background: #D32F2F; border-color: #D32F2F; color: #fff; }

.levels-card .levels-table { border: none !important; border-collapse: collapse !important; }
.levels-card .levels-table thead th {
    background-color: #002147 !important;
    color: #FFFFFF !important;
    border: none !important;
    border-bottom: 1px solid #002147 !important;
    font-weight: 600;
    font-size: 0.8125rem;
    vertical-align: middle;
    padding: 10px 20px;
    line-height: 1.3;
}
.levels-card .levels-table thead th:nth-child(1) { text-align: center !important; padding-left: 8px; padding-right: 8px; width: 40px; min-width: 40px; }
.levels-card .levels-table thead th:nth-child(2) { text-align: left !important; padding-left: 20px; }
.levels-card .levels-table thead th:nth-child(3) { text-align: right !important; padding-right: 20px; }

.levels-card .levels-table tbody tr { line-height: 1.35; }
.levels-card .levels-table tbody td {
    border: none !important;
    border-bottom: 1px solid #d1d5db !important;
    vertical-align: middle;
    font-size: 0.9375rem;
    color: #111827;
    padding: 10px 20px !important;
    line-height: 1.35 !important;
}
.levels-card .levels-table tbody td:nth-child(1) { text-align: center !important; padding: 10px 8px !important; width: 40px; min-width: 40px; }
.levels-card .levels-table tbody td:nth-child(2) { text-align: left !important; padding-left: 20px !important; }
.levels-card .levels-table tbody td:nth-child(3) { text-align: right !important; padding-right: 20px !important; padding-left: 20px !important; }
.levels-card .levels-table tbody tr:last-child td { border-bottom: none !important; }

/* Hamburger action button – minimalist, no boxy borders */
.levels-card .levels-table .levels-action-trigger {
    background: none !important;
    border: none !important;
    color: #8b6914;
    padding: 4px 8px;
}
.levels-card .levels-table .levels-action-trigger:hover { color: #6b5010; background: transparent !important; }
.levels-card .levels-table .dropdown-menu { min-width: 120px; padding: 0.25rem 0; }
.levels-card .levels-table .dropdown-item { padding: 0.4rem 1rem; font-size: 0.875rem; }
.levels-card .levels-table .dropdown-item.text-danger:hover { background-color: rgba(211, 47, 47, 0.08); }
</style>

    <div class="card border-0 shadow-sm levels-card">
        <div class="card-header header-elements-inline bg-white py-4 px-4">
            <p class="card-title mb-0 text-muted" style="font-size: 0.9375rem;">Define the educational levels of your school.</p>
            <div class="header-elements">
                <button type="button" class="btn btn-add-level" data-toggle="modal" data-target="#addLevelModal">Add Level</button>
            </div>
        </div>

        <div class="card-body px-4 pb-4 pt-0">
            @if(session('flash_success'))
                <div class="alert alert-success border-0 alert-dismissible mb-3">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    {{ session('flash_success') }}
                </div>
            @endif
            @if(session('flash_danger'))
                <div class="alert alert-danger border-0 alert-dismissible mb-3">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    {{ session('flash_danger') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-borderless levels-table mb-0">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Name</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($levels as $level)
                            <tr>
                                <td class="align-middle text-center">{{ $loop->iteration }}</td>
                                <td class="align-middle">{{ $level->name }}</td>
                                <td class="text-right align-middle">
                                    <div class="list-icons">
                                        <div class="dropdown">
                                            <a href="#" class="list-icons-item levels-action-trigger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="icon-menu9"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a href="#" class="dropdown-item" title="Edit" data-toggle="modal" data-target="#editModal{{ $level->id }}"><i class="icon-pencil mr-2"></i> Edit</a>
                                                <a href="#" id="{{ $level->id }}" onclick="confirmDelete(this.id); return false;" class="dropdown-item text-danger" title="Delete"><i class="icon-trash mr-2"></i> Delete</a>
                                                <form method="get" id="item-delete-{{ $level->id }}" action="{{ request()->root() . '/super_admin/levels/' . $level->id . '/delete' }}" class="hidden"></form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-muted text-center py-5" style="border-bottom: none !important;">No levels yet. Click Add Level to create one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Per-row Edit modals (no JS: each form action is fixed to POST /levels/{id}/update) --}}
    @foreach($levels as $level)
    <div class="modal fade" id="editModal{{ $level->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ request()->root() . '/super_admin/levels/' . $level->id . '/update' }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Level</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_name_{{ $level->id }}">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name_{{ $level->id }}" name="name" value="{{ old('name', $level->name) }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Add Level Modal --}}
    <div class="modal fade" id="addLevelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('levels.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Level</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="add_name">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_name" name="name" value="{{ old('name') }}" placeholder="e.g. Primary, Level 1" required>
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Level</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
