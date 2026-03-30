@extends('layouts.master')
@section('page_title', 'Manage Levels')
@section('content')
<div class="level-hub">
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

    <div class="card border-0 shadow-sm scheme-section">
        <div class="card-body p-2 pt-0 pb-1 scheme-body-compact">
            <div class="scheme-toolbar">
                <button type="button" class="btn btn-sm btn-red scheme-toolbar-btn" data-toggle="modal" data-target="#modalAddScheme">
                    <i class="icon-plus2 mr-1"></i> New Scheme
                </button>
            </div>
            <p class="text-muted small text-center mb-0 scheme-intro">Create reusable grading schemes like <strong>Standard 60/40</strong> and map them to levels below.</p>
            <div class="scheme-grid">
                @forelse($templates as $t)
                    @php
                        $total = 0;
                        $slots = $t->configuration ?? [];
                        foreach($slots as $slot){ $total += (int)($slot['max'] ?? 0); }
                    @endphp
                    <div class="scheme-card">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="scheme-name pr-2">{{ $t->name }}</div>
                            <div class="dropdown">
                                <a href="#" class="scheme-menu-trigger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="icon-menu9"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="#"
                                       class="dropdown-item edit-scheme"
                                       data-id="{{ $t->id }}"
                                       data-name="{{ $t->name }}"
                                       data-config="{{ json_encode($t->configuration ?? []) }}">
                                        <i class="icon-pencil mr-2"></i>Edit
                                    </a>
                                    <a href="#"
                                       id="template-{{ $t->id }}"
                                       onclick="confirmDelete(this.id); return false;"
                                       class="dropdown-item text-danger delete-confirm">
                                        <i class="icon-trash mr-2"></i>Delete
                                    </a>
                                    <form id="item-delete-template-{{ $t->id }}" method="POST" action="{{ route('levels.template.delete', $t->id) }}" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="scheme-meta mt-auto">{{ count($slots) }} Components &middot; {{ $total }}/100</div>
                    </div>
                @empty
                    <div class="text-muted">No scheme templates yet. Create one to start mapping levels.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mapping-section">
            <div class="card-header level-header d-flex justify-content-between align-items-center py-2 px-3">
            <h6 class="card-title text-white mb-0">Levels &amp; Grading Mapping</h6>
            <div class="d-flex align-items-center action-toolbar" style="align-items:center;">
                <button type="button" class="btn btn-sm btn-red" data-toggle="modal" data-target="#addLevelModal" style="height:34px; display:inline-flex; align-items:center;">Add Level</button>
            </div>
        </div>
        <div class="card-body p-2 pt-1">
            <form method="POST" action="{{ route('levels.mapping.save') }}" id="mappingForm">
                @csrf
                <input type="hidden" name="school_year" value="{{ $school_year }}">

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 hub-table">
                        <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Level Name</th>
                            <th>Term 1 Scheme</th>
                            <th>Term 2 Scheme</th>
                            <th class="text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($levels as $level)
                            <tr class="mapping-row">
                                <td>{{ $loop->iteration }}</td>
                                <td class="font-weight-semibold">{{ $level->name }}</td>
                                <td>
                                    <select class="form-control form-control-sm term-select" name="mapping[{{ $level->id }}][1]" data-level-id="{{ $level->id }}" data-term="1">
                                        <option value="">-- None --</option>
                                        @foreach($templates as $t)
                                            <option value="{{ $t->id }}" {{ (($configs[$level->id][1] ?? null) == $t->id) ? 'selected' : '' }}>{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control form-control-sm term-select" name="mapping[{{ $level->id }}][2]" data-level-id="{{ $level->id }}" data-term="2">
                                        <option value="">-- None --</option>
                                        @foreach($templates as $t)
                                            <option value="{{ $t->id }}" {{ (($configs[$level->id][2] ?? null) == $t->id) ? 'selected' : '' }}>{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-right">
                                    <div class="dropdown">
                                        <a href="#" class="table-action-menu" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#editLevelModal{{ $level->id }}"><i class="icon-pencil mr-2"></i>Edit</a>
                                            <a href="#" onclick="confirmDelete('{{ $level->id }}'); return false;" class="dropdown-item text-danger"><i class="icon-trash mr-2"></i>Delete</a>
                                            <form method="get" id="item-delete-{{ $level->id }}" action="{{ route('levels.delete', $level->id) }}" class="d-none"></form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No levels yet. Add your first level.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="text-right mt-2">
                    <button type="submit" class="btn btn-red">Save Mapping</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($levels as $level)
<div class="modal fade" id="editLevelModal{{ $level->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('levels.update', $level->id) }}">
                @csrf
                <div class="modal-header level-header">
                    <h6 class="modal-title text-white">Edit Level</h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Level Name</label>
                        <input type="text" class="form-control" name="name" value="{{ $level->name }}" required>
                    </div>
                    <div class="form-group">
                        <label>Term 1 Scheme</label>
                        <select class="form-control" name="term_1_template_id">
                            <option value="">-- None --</option>
                            @foreach($templates as $t)
                                <option value="{{ $t->id }}" {{ (($configs[$level->id][1] ?? null) == $t->id) ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label>Term 2 Scheme</label>
                        <select class="form-control" name="term_2_template_id">
                            <option value="">-- None --</option>
                            @foreach($templates as $t)
                                <option value="{{ $t->id }}" {{ (($configs[$level->id][2] ?? null) == $t->id) ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-red">Update Level</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<div class="modal fade" id="addLevelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('levels.store') }}">
                @csrf
                <div class="modal-header level-header">
                    <h6 class="modal-title text-white">Add Level</h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Level Name</label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Primary" required>
                    </div>
                    <div class="form-group">
                        <label>Term 1 Scheme</label>
                        <select class="form-control" name="term_1_template_id">
                            <option value="">-- None --</option>
                            @foreach($templates as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label>Term 2 Scheme</label>
                        <select class="form-control" name="term_2_template_id">
                            <option value="">-- None --</option>
                            @foreach($templates as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-red">Create Level</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddScheme" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('levels.template.store') }}" id="formAddScheme">
                @csrf
                <div class="modal-header level-header">
                    <h6 class="modal-title text-white">Create Scheme Template</h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" style="padding:0.75rem 1rem;">
                    <div class="form-group" style="margin-bottom:1.25rem;">
                        <label>Template Name</label>
                        <input type="text" name="name" class="form-control" required maxlength="120">
                    </div>
                    <div class="form-group mb-0">
                        <div style="display:flex; font-weight:bold; margin-bottom:10px; border-bottom: 1px solid #eee; padding-bottom: 5px; font-size:0.8rem; color:#4b5563;">
                            <div style="width:60%; padding-right:10px;">Assessment Title</div>
                            <div style="width:30%; text-align:center;">Weight</div>
                            <div style="width:10%;"></div>
                        </div>
                        <div id="levelsSchemeAddRows"></div>
                        <div style="display:flex; gap:10px; align-items:center; margin-top:6px;">
                            <div style="width:60%;"></div>
                            <div style="width:30%; text-align:center; font-weight:700; color:#374151;">
                                Total: <span id="addLiveTotal">0</span> / 100
                            </div>
                            <div style="width:10%;"></div>
                        </div>
                        <button type="button" id="levelsSchemeAddSlotBtn" class="btn btn-sm btn-outline-secondary mt-2">+ Add Assessment</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-red">Save Scheme</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditScheme" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" id="formEditScheme">
                @csrf
                @method('PUT')
                <div class="modal-header level-header">
                    <h6 class="modal-title text-white">Edit Scheme Template</h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" style="padding:0.75rem 1rem;">
                    <div class="form-group" style="margin-bottom:1.25rem;">
                        <label>Template Name</label>
                        <input type="text" name="name" id="editSchemeName" class="form-control" required maxlength="120">
                    </div>
                    <div class="form-group mb-0">
                        <div style="display:flex; font-weight:bold; margin-bottom:10px; border-bottom: 1px solid #eee; padding-bottom: 5px; font-size:0.8rem; color:#4b5563;">
                            <div style="width:60%; padding-right:10px;">Assessment Title</div>
                            <div style="width:30%; text-align:center;">Weight</div>
                            <div style="width:10%;"></div>
                        </div>
                        <div id="levelsSchemeEditRows"></div>
                        <div style="display:flex; gap:10px; align-items:center; margin-top:6px;">
                            <div style="width:60%;"></div>
                            <div style="width:30%; text-align:center; font-weight:700; color:#374151;">
                                Total: <span id="editLiveTotal">0</span> / 100
                            </div>
                            <div style="width:10%;"></div>
                        </div>
                        <button type="button" id="levelsSchemeEditSlotBtn" class="btn btn-sm btn-outline-secondary mt-2">+ Add Assessment</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-red">Update Scheme</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .level-hub .level-header { background: #002147; border: none; }
    .level-hub { margin-top: -12px; }
    .level-hub .scheme-section { margin-bottom: 8px; }
    .level-hub .btn-red { background: #D32F2F; color: #fff; border-color: #D32F2F; }
    .level-hub .btn-red:hover { background: #b71c1c; border-color: #b71c1c; color: #fff; }
    .level-hub .scheme-toolbar {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        margin-bottom: 0;
    }
    .level-hub .scheme-body-compact {
        margin-top: -8px;
    }
    .level-hub .scheme-toolbar-btn {
        flex: 0 0 auto;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        padding: 0.35rem 0.7rem;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .level-hub .scheme-intro {
        line-height: 1.25;
        margin-top: 0 !important;
        transform: translateY(-12px);
    }
    .level-hub .scheme-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap: 10px;
        margin-top: -16px;
    }
    .level-hub .scheme-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 10px;
        min-height: 92px;
        height: 92px;
        display: flex;
        flex-direction: column;
    }
    .level-hub .scheme-name {
        font-weight: 700;
        font-size: 0.84rem;
        line-height: 1.25;
        color: #111827;
    }
    .level-hub .scheme-meta {
        font-size: 11px;
        line-height: 1.25;
        color: #6b7280;
        margin-top: 4px;
    }
    .level-hub .scheme-menu-trigger {
        color: #6b7280;
        line-height: 1;
        padding: 1px 4px;
        border-radius: 4px;
        display: inline-block;
        font-size: 0.82rem;
    }
    .level-hub .scheme-menu-trigger:hover { color: #002147; background: #f8fafc; }
    .level-hub .hub-table thead th { background: #002147; color: #fff; border: none; font-weight: 600; }
    .level-hub .hub-table tbody td { vertical-align: middle; padding-top: 14px; padding-bottom: 14px; }
    .level-hub .table-action-menu { color: #D32F2F; padding: 2px 6px; border-radius: 6px; display: inline-block; line-height: 1; }
    .level-hub .table-action-menu:hover { color: #b71c1c; background: rgba(211, 47, 47, 0.08); }
    .level-hub .action-toolbar .btn { height: 34px; display: inline-flex; align-items: center; }
    .level-hub .mapping-section .card-body { padding-top: 0.35rem !important; }
    .level-hub .term-select {
        border: 1px solid #d9dee6;
        border-radius: 7px;
        padding: 0.2rem 1.8rem 0.2rem 0.65rem;
        height: 34px;
        box-shadow: none;
        background-color: #fff;
        font-size: 0.85rem;
    }
    .level-hub .term-select:focus {
        border-color: #002147;
        box-shadow: 0 0 0 0.1rem rgba(0, 33, 71, 0.08);
    }
    .level-hub .slot-grid-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
        padding: 0 2px;
        color: #6b7280;
        font-weight: 600;
        font-size: 0.8rem;
    }
    .level-hub .slot-grid-title { flex: 0 0 60%; }
    .level-hub .slot-grid-weight { flex: 0 0 30%; }
    .level-hub .slot-grid-remove { flex: 0 0 10%; }

    .level-hub .config-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }
    .level-hub .config-row input.form-control-sm {
        height: 34px;
        padding-top: 0.25rem;
        padding-bottom: 0.25rem;
        border-radius: 7px;
        border: 1px solid #d9dee6;
        box-shadow: none;
    }
    .level-hub .config-row input.form-control-sm:focus {
        border-color: #002147;
        box-shadow: 0 0 0 0.1rem rgba(0, 33, 71, 0.08);
    }
    .level-hub .config-row .slot-label { flex: 0 0 60%; }
    .level-hub .config-row .slot-max { flex: 0 0 30%; }
    .level-hub .config-row .slot-max { text-align: center; }
    .level-hub .config-row .remove-row {
        flex: 0 0 10%;
        min-width: 38px;
        height: 34px;
        padding: 0;
        border-radius: 7px;
        line-height: 1;
        border: none;
        background: transparent;
        color: #D32F2F;
        font-size: 1.25rem;
        font-weight: 600;
    }
    .level-hub .add-slot-btn {
        align-self: flex-start;
    }

    /* Compact modal layout */
    .level-hub #modalAddScheme .modal-body,
    .level-hub #modalEditScheme .modal-body {
        padding: 0.75rem 1rem !important;
    }
    .level-hub #modalAddScheme .form-group,
    .level-hub #modalEditScheme .form-group {
        margin-bottom: 0.75rem;
    }
    .level-hub #modalAddScheme .add-slot-btn,
    .level-hub #modalEditScheme .add-slot-btn {
        margin-top: 0.25rem !important;
    }
    @media (max-width: 767.98px) {
        .level-hub { margin-top: -8px; }
        .level-hub .scheme-toolbar {
            align-self: flex-end;
        }
    }
</style>
@endsection

@section('scripts')
<script>
(function() {
    function getWeightSum(container) {
        if (!container) return 0;
        var sum = 0;
        container.querySelectorAll('.levels-scheme-slot-weight').forEach(function(inp) {
            var v = parseInt(inp.value, 10);
            if (!isNaN(v)) sum += v;
        });
        return sum;
    }

    function updateAddTotal() {
        if (!addContainer) return;
        var span = document.getElementById('addLiveTotal');
        if (!span) return;
        span.textContent = getWeightSum(addContainer);
    }

    function updateEditTotal() {
        if (!editContainer) return;
        var span = document.getElementById('editLiveTotal');
        if (!span) return;
        span.textContent = getWeightSum(editContainer);
    }

    function makeRow(idx, label, max) {
        var row = document.createElement('div');
        row.className = 'levels-scheme-slot-row';
        row.setAttribute(
            'style',
            'display:flex !important; flex-direction:row !important; align-items:center; gap:10px; margin-bottom:8px; width:100%;'
        );

        row.innerHTML =
            '<input type="text" ' +
                'name="config[' + idx + '][label]" ' +
                'class="form-control form-control-sm levels-scheme-slot-title" ' +
                'maxlength="60" placeholder="e.g. CA 1" ' +
                'value="' + (label || '') + '" ' +
                'style="width:60% !important;"' +
            '>' +
            '<input type="number" ' +
                'name="config[' + idx + '][max]" ' +
                'class="form-control form-control-sm levels-scheme-slot-weight" ' +
                'min="0" max="100" ' +
                'value="' + (max || 0) + '" ' +
                'style="width:30% !important; text-align:center;"' +
            '>' +
            '<button type="button" ' +
                'class="levels-scheme-slot-remove" ' +
                'aria-label="Remove slot" title="Remove slot" ' +
                'style="width:10%; color: #D32F2F; border: none; background: none; cursor: pointer; padding: 0; line-height: 1; font-size: 1.25rem; font-weight: 600;"' +
            '>&times;</button>';
        return row;
    }

    function reindex(container) {
        var rows = container.querySelectorAll('.levels-scheme-slot-row');
        rows.forEach(function(row, index) {
            row.querySelector('.levels-scheme-slot-title').name = 'config[' + index + '][label]';
            row.querySelector('.levels-scheme-slot-weight').name = 'config[' + index + '][max]';
        });
    }

    function wireRowEvents(container, row) {
        row.querySelector('.levels-scheme-slot-remove').addEventListener('click', function() {
            if (container.querySelectorAll('.levels-scheme-slot-row').length <= 1) return;
            row.remove();
            reindex(container);
            container === addContainer ? updateAddTotal() : updateEditTotal();
        });

        var weightInput = row.querySelector('.levels-scheme-slot-weight');
        if (weightInput) {
            weightInput.addEventListener('input', function() {
                container === addContainer ? updateAddTotal() : updateEditTotal();
            });
        }
    }

    var addContainer = document.getElementById('levelsSchemeAddRows');
    var addBtn = document.getElementById('levelsSchemeAddSlotBtn');
    if (addContainer) {
        [['1st CA', 20], ['2nd CA', 20], ['Exam', 60]].forEach(function(item, i) {
            var row = makeRow(i, item[0], item[1]);
            addContainer.appendChild(row);
            wireRowEvents(addContainer, row);
        });
    }
    if (addBtn && addContainer) {
        addBtn.addEventListener('click', function() {
            var row = makeRow(addContainer.querySelectorAll('.levels-scheme-slot-row').length, '', 0);
            addContainer.appendChild(row);
            wireRowEvents(addContainer, row);
            reindex(addContainer);
            updateAddTotal();
        });
    }

    var editForm = document.getElementById('formEditScheme');
    var editContainer = document.getElementById('levelsSchemeEditRows');
    var editBtn = document.getElementById('levelsSchemeEditSlotBtn');
    document.querySelectorAll('.edit-scheme').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = btn.getAttribute('data-id');
            var name = btn.getAttribute('data-name') || '';
            var config = [];
            try { config = JSON.parse(btn.getAttribute('data-config') || '[]'); } catch (e) { config = []; }
            editForm.action = '{{ url("/super_admin/levels/template") }}/' + id;
            document.getElementById('editSchemeName').value = name;
            editContainer.innerHTML = '';
            if (!Array.isArray(config) || config.length === 0) {
                config = [{label: '1st CA', max: 20}, {label: '2nd CA', max: 20}, {label: 'Exam', max: 60}];
            }
            config.forEach(function(item, i) {
                var row = makeRow(i, item.label, item.max);
                editContainer.appendChild(row);
                wireRowEvents(editContainer, row);
            });
            reindex(editContainer);
            updateEditTotal();
            $('#modalEditScheme').modal('show');
        });
    });
    if (editBtn && editContainer) {
        editBtn.addEventListener('click', function() {
            var row = makeRow(editContainer.querySelectorAll('.levels-scheme-slot-row').length, '', 0);
            editContainer.appendChild(row);
            wireRowEvents(editContainer, row);
            reindex(editContainer);
            updateEditTotal();
        });
    }

    // Smart auto-sync:
    // When Term 1 changes for a given level, copy the same scheme to Term 2 for that level.
    // Manual Term 2 override stays until Term 1 changes again.
    document.querySelectorAll('.term-select[data-term="1"]').forEach(function(term1Select) {
        term1Select.addEventListener('change', function() {
            var levelId = term1Select.getAttribute('data-level-id');
            var term2Select = document.querySelector('.term-select[data-level-id="' + levelId + '"][data-term="2"]');
            if (term2Select) {
                term2Select.value = term1Select.value;
            }
        });
    });
})();
</script>
@endsection
