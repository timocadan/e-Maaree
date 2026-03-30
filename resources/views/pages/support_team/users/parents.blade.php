@extends('layouts.master')
@section('page_title', 'Manage Parents')
@section('content')

    <style>
        .parents-table thead th {
            background: #002147;
            color: #fff;
            border-color: #002147;
            white-space: nowrap;
        }
        .parents-table td, .parents-table th {
            vertical-align: middle !important;
        }
        .username-pill {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            background: #f8f9fa;
            color: #1f2937;
            font-weight: 600;
            letter-spacing: 0.2px;
        }
        .children-inline {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 520px;
        }
        .parents-table .list-icons .list-icons-item {
            color: #333;
        }
    </style>

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title mb-0">Manage Parents</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable-basic parents-table">
                    <thead>
                    <tr>
                        <th style="width: 5%;">S/N</th>
                        <th style="width: 28%;">Full Name</th>
                        <th style="width: 17%;">Username (Phone)</th>
                        <th>Children</th>
                        <th style="width: 14%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($parents as $p)
                        @php($kids = $childrenByParent[$p->id] ?? [])
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p->name }}</td>
                            <td><span class="username-pill">{{ $p->username ?: $p->phone }}</span></td>
                            <td>
                                @if(!empty($kids))
                                    <span class="children-inline" title="{{ implode(', ', $kids) }}">{{ implode(', ', $kids) }}</span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown">
                                            <i class="icon-menu9"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            <a href="{{ route('users.show', Qs::hash($p->id)) }}" class="dropdown-item"><i class="icon-eye"></i> View Profile</a>
                                            <a href="{{ route('users.edit', Qs::hash($p->id)) }}" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                            <form method="post" action="{{ route('users.parents.reset_pass', Qs::hash($p->id)) }}" onsubmit="return confirm('Reset password for this parent to 123456?');">
                                                @csrf
                                                <button type="submit" class="dropdown-item border-0 bg-transparent w-100 text-left">
                                                    <i class="icon-lock"></i> Reset Password
                                                </button>
                                            </form>
                                            <a id="parent-{{ Qs::hash($p->id) }}" onclick="confirmDelete('{{ Qs::hash($p->id) }}')" href="#" class="dropdown-item text-danger">
                                                <i class="icon-trash"></i> Delete
                                            </a>
                                            <form method="post" id="item-delete-{{ Qs::hash($p->id) }}" action="{{ route('users.destroy', Qs::hash($p->id)) }}" class="hidden">
                                                @csrf
                                                @method('delete')
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        (function () {
            if (!$().DataTable) return;
            $('.datatable-basic').DataTable({
                autoWidth: false,
                ordering: false,
                dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
                language: {
                    search: '<span>Filter:</span> _INPUT_',
                    searchPlaceholder: 'Type to filter...',
                    lengthMenu: '<span>Show:</span> _MENU_'
                }
            });
        })();
    </script>
@endsection

