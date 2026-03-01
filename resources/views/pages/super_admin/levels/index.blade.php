@extends('layouts.master')
@section('page_title', 'Manage Levels')
@section('content')

@push('styles')
<style>
.levels-card .card-header { border-bottom: 1px solid #e5e7eb; }
/* Add Level: clean outline, solid red on hover */
.levels-card .btn-add-level { background: #fff; border: 1px solid #D32F2F; color: #D32F2F; padding: 0.375rem 0.75rem; font-size: 0.875rem; border-radius: 6px; }
.levels-card .btn-add-level:hover { background: #D32F2F; border-color: #D32F2F; color: #fff; }
/* Table: borderless with thin row border */
.levels-card .levels-table { border: none !important; }
.levels-card .levels-table thead th { font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; font-weight: 500; border: none !important; border-bottom: 1px solid #e5e7eb !important; padding: 0.625rem 0 1rem 0; vertical-align: middle; }
.levels-card .levels-table thead th:first-child { padding-left: 0; }
.levels-card .levels-table tbody td { border: none !important; border-bottom: 1px solid #e5e7eb !important; padding: 0 0; vertical-align: middle; font-size: 0.9375rem; color: #111827; min-height: 48px; }
.levels-card .levels-table tbody td:first-child { padding: 0.875rem 1.5rem 0.875rem 0; }
.levels-card .levels-table tbody td:last-child { padding-left: 0.5rem; padding-right: 0; }
.levels-card .levels-table tbody tr:last-child td { border-bottom: none !important; }
/* Consistent row height */
.levels-card .levels-table tbody tr { height: 52px; }
.levels-card .levels-table tbody tr td { height: 52px; box-sizing: border-box; }
.levels-card .levels-table .d-flex form { margin: 0; display: inline; }
</style>
@endpush

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
                            <th>Name</th>
                            <th class="text-right" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($levels as $level)
                            <tr>
                                <td class="align-middle">{{ $level->name }}</td>
                                <td class="text-right align-middle" style="padding-right: 25px;">
                                    <div class="d-flex justify-content-end align-items-center">
                                        <a href="#" class="action-link edit-link" title="Edit" data-toggle="modal" data-target="#editModal{{ $level->id }}">Edit</a>
                                        <span class="separator">|</span>
                                        <a href="{{ request()->root() . '/super_admin/levels/' . $level->id . '/delete' }}" class="action-link delete-link" title="Delete" onclick="return confirm('Are you sure?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-muted text-center py-5" style="border-bottom: none !important;">No levels yet. Click Add Level to create one.</td>
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
