@extends('layouts.master')
@section('page_title', 'My Children')
@section('content')

    <style>
        .my-children-table thead th {
            background: #002147;
            color: #fff;
            border-color: #002147;
            white-space: nowrap;
        }
        .my-children-table td,
        .my-children-table th {
            vertical-align: middle !important;
        }
        .adm-pill {
            display: inline-block;
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            background: #f8f9fa;
            color: #1f2937;
            font-weight: 600;
        }
    </style>

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">My Children</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <table class="table my-children-table datatable-my-children">
                <thead>
                <tr>
                    <th>S/N</th>
                    <th>Name</th>
                    <th>ADM_No</th>
                    <th>Section</th>
                    <th class="no-export">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($students as $s)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $s->user->name }}</td>
                        <td><span class="adm-pill">{{ $s->adm_no }}</span></td>
                        <td>{{ $s->my_class->name.' '.$s->section->name }}</td>
                        <td class="text-center">
                            <div class="list-icons">
                                <div class="dropdown">
                                    <a href="#" class="list-icons-item" data-toggle="dropdown">
                                        <i class="icon-menu9"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-left">
                                        <a href="{{ route('students.show', Qs::hash($s->id)) }}" class="dropdown-item"><i class="icon-eye"></i> View Profile</a>
                                        <a target="_blank" href="{{ route('marks.year_selector', Qs::hash($s->user->id)) }}" class="dropdown-item"><i class="icon-check"></i> Marksheet</a>

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

    {{--Student List Ends--}}

@endsection

@section('scripts')
    <script>
        (function () {
            if (!$().DataTable) return;
            var $t = $('.datatable-my-children');
            if (!$t.length) return;

            if ($.fn.DataTable.isDataTable($t)) {
                $t.DataTable().destroy();
            }

            $t.DataTable({
                autoWidth: false,
                ordering: false,
                dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
                language: {
                    search: '<span>Filter:</span> _INPUT_',
                    searchPlaceholder: 'Type to filter...',
                    lengthMenu: '<span>Show:</span> _MENU_',
                    paginate: { 'first': 'First', 'last': 'Last', 'next': $('html').attr('dir') === 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') === 'rtl' ? '&rarr;' : '&larr;' }
                }
            });
        })();
    </script>
@endsection
