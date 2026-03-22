@extends('layouts.master')
@section('page_title', 'Manage Class Sections')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Class Sections</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <style>
                /* Sections table: navy header + left alignment (S/N, Name, Class, Teacher), Action right */
                .datatable-basic thead th { background-color: #002147; color: #fff; font-weight: 600; border-color: #002147; }
                .datatable-basic thead th:first-child,
                .datatable-basic tbody td:first-child,
                .dataTables_wrapper table.datatable-basic thead th:first-child,
                .dataTables_wrapper table.datatable-basic tbody td:first-child {
                    text-align: left !important;
                    padding-left: 15px !important;
                }
                .datatable-basic thead th:nth-child(2),
                .datatable-basic thead th:nth-child(3),
                .datatable-basic thead th:nth-child(4),
                .datatable-basic tbody td:nth-child(2),
                .datatable-basic tbody td:nth-child(3),
                .datatable-basic tbody td:nth-child(4),
                .dataTables_wrapper table.datatable-basic thead th:nth-child(2),
                .dataTables_wrapper table.datatable-basic thead th:nth-child(3),
                .dataTables_wrapper table.datatable-basic thead th:nth-child(4),
                .dataTables_wrapper table.datatable-basic tbody td:nth-child(2),
                .dataTables_wrapper table.datatable-basic tbody td:nth-child(3),
                .dataTables_wrapper table.datatable-basic tbody td:nth-child(4) {
                    text-align: left !important;
                }
                .datatable-basic thead th:nth-child(5),
                .datatable-basic tbody td:nth-child(5),
                .dataTables_wrapper table.datatable-basic thead th:nth-child(5),
                .dataTables_wrapper table.datatable-basic tbody td:nth-child(5) {
                    text-align: right !important;
                }
            </style>
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#new-section" class="nav-link active" data-toggle="tab">Create New Section</a></li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Manage Sections</a>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach($my_classes as $c)
                            <a href="#c{{ $c->id }}" class="dropdown-item" data-toggle="tab">{{ $c->name }}</a>
                        @endforeach
                    </div>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane show  active fade" id="new-section">
                    <div class="row">
                        <div class="col-md-6">
                            <form class="ajax-store" method="post" action="{{ route('sections.store') }}">
                                @csrf
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <input name="name" value="{{ old('name') }}" required type="text" class="form-control" placeholder="Name of Section">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="my_class_id" class="col-lg-3 col-form-label font-weight-semibold">Select Class <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <select required data-placeholder="Select Class" class="form-control select" name="my_class_id" id="my_class_id">
                                            @foreach($my_classes as $c)
                                                <option {{ old('my_class_id') == $c->id ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="teacher_id" class="col-lg-3 col-form-label font-weight-semibold">Teacher</label>
                                    <div class="col-lg-9">
                                        <select data-placeholder="Select Teacher" class="form-control select-search" name="teacher_id" id="teacher_id">
                                            <option value=""></option>
                                            @foreach($teachers as $t)
                                                <option {{ old('teacher_id') == Qs::hash($t->id) ? 'selected' : '' }} value="{{ Qs::hash($t->id) }}">{{ $t->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary">Submit form <i class="icon-paperplane ml-2"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @foreach($my_classes as $d)
                    <div class="tab-pane fade" id="c{{ $d->id }}">                         <table class="table datatable-basic">
                            <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Teacher</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($sections->where('my_class.id', $d->id) as $s)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $s->name }} @if($s->active)<i class='icon-check'> </i>@endif</td>
                                    <td>{{ $s->my_class->name }}</td>

                                    @if($s->teacher_id)
                                    <td><a target="_blank" href="{{ route('users.show', Qs::hash($s->teacher_id)) }}">{{ $s->teacher->name }}</a></td>
                                        @else
                                        <td> - </td>
                                    @endif

                                    <td class="text-right">
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                    <i class="icon-menu9"></i>
                                                </a>

                                                <div class="dropdown-menu dropdown-menu-left">
                                                    {{--edit--}}
                                                    @if(Qs::userIsTeamSA())
                                                        <a href="{{ route('sections.edit', $s->id) }}" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                                    @endif
                                                    {{--Delete--}}
                                                    @if(Qs::userIsSuperAdmin())
                                                        <a id="{{ $s->id }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item"><i class="icon-trash"></i> Delete</a>
                                                        <form method="post" id="item-delete-{{ $s->id }}" action="{{ route('sections.destroy', $s->id) }}" class="hidden">@csrf @method('delete')</form>
                                                    @endif

                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach

            </div>
        </div>
    </div>

    {{--Section List Ends--}}

@endsection

@section('scripts')
<script>
(function() {
    if (!$().DataTable) return;
    $('.datatable-basic').DataTable({
        autoWidth: false,
        dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
        language: {
            search: '<span>Filter:</span> _INPUT_',
            searchPlaceholder: 'Type to filter...',
            lengthMenu: '<span>Show:</span> _MENU_',
            paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' }
        },
        initComplete: function() {
            var $t = $('.dataTables_wrapper table.datatable-basic');
            $t.find('thead th:first-child, tbody td:first-child').css({ 'text-align': 'left', 'padding-left': '15px' });
            $t.find('thead th:nth-child(2), thead th:nth-child(3), thead th:nth-child(4), tbody td:nth-child(2), tbody td:nth-child(3), tbody td:nth-child(4)').css('text-align', 'left');
            $t.find('thead th:nth-child(5), tbody td:nth-child(5)').css('text-align', 'right');
        },
        drawCallback: function() {
            var $t = $('.dataTables_wrapper table.datatable-basic');
            $t.find('thead th:first-child, tbody td:first-child').css({ 'text-align': 'left', 'padding-left': '15px' });
            $t.find('thead th:nth-child(2), thead th:nth-child(3), thead th:nth-child(4), tbody td:nth-child(2), tbody td:nth-child(3), tbody td:nth-child(4)').css('text-align', 'left');
            $t.find('thead th:nth-child(5), tbody td:nth-child(5)').css('text-align', 'right');
        }
    });
})();
</script>
@endsection
