@extends('layouts.master')
@section('page_title', 'Manage Grades')
@section('content')

    <div class="card" id="grades-list-card"
         data-school-name="{{ e(Qs::getSystemName()) }}"
         data-school-address="{{ e(Qs::getSetting('address') ?? '') }}"
         data-school-phone="{{ e(Qs::getSetting('phone') ?? '') }}"
         data-school-phone2="{{ e(Qs::getSetting('phone2') ?? '') }}"
         data-school-email="{{ e(Qs::getSetting('system_email') ?? '') }}">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Grades</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#all-grades" class="nav-link active" data-toggle="tab">Manage Grades</a></li>
                @if(Qs::userIsTeamSA())
                <li class="nav-item"><a href="#new-grade" class="nav-link" data-toggle="tab"><i class="icon-plus2"></i> Add Grade</a></li>
                @endif
            </ul>

            <div class="tab-content">
                    <div class="tab-pane fade show active" id="all-grades">
                        <style>
                            .datatable-grade-list thead th { background-color: #002147; color: #fff; font-weight: 600; border-color: #002147; cursor: default !important; }
                            /* Hide sort arrows and keep header non-interactive */
                            .datatable-grade-list thead th::before,
                            .datatable-grade-list thead th::after,
                            .dataTables_wrapper .datatable-grade-list thead th::before,
                            .dataTables_wrapper .datatable-grade-list thead th::after { display: none !important; }
                            .dataTables_wrapper .datatable-grade-list thead th { cursor: default !important; }
                            /* S/N column: force left align (DataTables may center first column) */
                            .datatable-grade-list thead th.grade-col-sn,
                            .datatable-grade-list tbody td.grade-col-sn,
                            .datatable-grade-list thead th:first-child,
                            .datatable-grade-list tbody td:first-child,
                            .dataTables_wrapper .datatable-grade-list thead th:first-child,
                            .dataTables_wrapper .datatable-grade-list tbody td:first-child,
                            table.datatable-grade-list thead th:first-child,
                            table.datatable-grade-list tbody td:first-child { text-align: left !important; padding-left: 15px !important; }
                            .datatable-grade-list thead th.grade-col-left,
                            .datatable-grade-list tbody td.grade-col-left { text-align: left; }
                            .datatable-grade-list thead th.grade-col-action,
                            .datatable-grade-list tbody td.grade-col-action { text-align: right; }
                        </style>
                        <table class="table datatable-grade-list">
                            <thead>
                            <tr>
                                <th class="grade-col-sn">S/N</th>
                                <th class="grade-col-left">Name</th>
                                {{-- <th>Grade Type</th> --}}
                                <th class="grade-col-left">Range</th>
                                <th class="grade-col-left">Remark</th>
                                @if(Qs::userIsTeamSA())
                                <th class="no-export grade-col-action">Action</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($grades as $gr)
                                <tr>
                                    <td class="grade-col-sn">{{ $loop->iteration }}</td>
                                    <td class="grade-col-left">{{ $gr->name }}</td>
                                    {{-- <td>{{ $gr->class_type_id ? $class_types->where('id', $gr->class_type_id)->first()->name : '' }}</td> --}}
                                    <td class="grade-col-left">{{ $gr->mark_from.' - '.$gr->mark_to }}</td>
                                    <td class="grade-col-left">{{ $gr->remark }}</td>
                                    @if(Qs::userIsTeamSA())
                                    <td class="grade-col-action">
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                <i class="icon-menu9"></i>
                                                </a>

                                                <div class="dropdown-menu dropdown-menu-left">
                                                    {{--Edit--}}
                                                    <a href="{{ request()->root() . '/grades/' . $gr->id . '/edit' }}" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                                    @if(Qs::userIsSuperAdmin())
                                                    {{--Delete (GET-based link with confirmation, like Levels)--}}
                                                    <a href="#" id="{{ $gr->id }}" onclick="confirmDelete(this.id); return false;" class="dropdown-item text-danger"><i class="icon-trash"></i> Delete</a>
                                                    <form method="get" id="item-delete-{{ $gr->id }}" action="{{ request()->root() . '/grades/' . $gr->id . '/delete' }}" class="hidden"></form>
                                                    @endif

                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                @if(Qs::userIsTeamSA())
                <div class="tab-pane fade" id="new-grade">
                    <div class="row">
                        <div class="col-md-6">
                            <form method="post" action="{{ route('grades.store') }}">
                                @csrf
                                <input type="hidden" name="class_type_id" value="">
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <input name="name" value="{{ old('name') }}" required type="text" class="form-control text-uppercase" placeholder="E.g. A1">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Mark From <span class="text-danger">*</span></label>
                                    <div class="col-lg-3">
                                        <input min="0" max="100" name="mark_from" value="{{ old('mark_from') }}" required type="number" class="form-control" placeholder="0">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Mark To <span class="text-danger">*</span></label>
                                    <div class="col-lg-3">
                                        <input min="0" max="100" name="mark_to" value="{{ old('mark_to') }}" required type="number" class="form-control" placeholder="0">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="remark" class="col-lg-3 col-form-label font-weight-semibold">Remark</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="remark" id="remark" value="{{ old('remark') }}" class="form-control" placeholder="e.g. Excellent or Aad u Wanaagsan">
                                    </div>
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary" style="background-color: #D32F2F; border-color: #D32F2F;">Submit form <i class="icon-paperplane ml-2"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{--Grades List Ends--}}

@endsection

@section('scripts')
<script>
(function() {
    if (!$().DataTable) return;
    var $card = $('#grades-list-card');
    if (!$card.length) return;
    var schoolName = $card.data('school-name') || 'Grading Scheme';
    var address = $card.data('school-address') || '';
    var phone = $card.data('school-phone') || '';
    var phone2 = $card.data('school-phone2') || '';
    var email = $card.data('school-email') || '';
    var telLine = [phone, phone2].filter(Boolean).join(', ');
    var addressBarText = [address, telLine ? ('Tel: ' + telLine) : '', email].filter(Boolean).join(' | ');
    var docDate = new Date().toLocaleDateString();
    var exportLabel = schoolName + ' - Grading Scheme';
    var exportFilename = exportLabel.replace(/[/\\:*?"<>|]/g, '-');
    var navyBlue = '#002147';

    var pdfCommon = {
        orientation: 'portrait',
        pageSize: 'A4',
        title: exportLabel,
        filename: exportFilename,
        customize: function(doc) {
            doc.pageSize = 'A4';
            doc.pageMargins = [40, 20, 40, 56];
            var borderGrey = '#dddddd';
            doc.styles.tableHeader = { bold: true, fontSize: 11, color: '#FFFFFF', fillColor: navyBlue, alignment: 'center' };
            doc.styles.tableBodyOdd = { fillColor: '#f5f5f5', fontSize: 10 };
            doc.styles.tableBodyEven = { fillColor: '#ffffff', fontSize: 10 };

            var tableElem = null;
            for (var i = 0; i < doc.content.length; i++) {
                if (doc.content[i].table) { tableElem = doc.content[i]; break; }
            }
            if (!tableElem || !tableElem.table) return;

            var numCols = tableElem.table.body[0] ? tableElem.table.body[0].length : 4;
            tableElem.table.widths = numCols === 4 ? ['10%', '20%', '25%', '45%'] : ['*'];
            tableElem.layout = { hLineWidth: function() { return 0.5; }, vLineWidth: function() { return 0.5; }, hLineColor: function() { return borderGrey; }, vLineColor: function() { return borderGrey; } };

            var body = tableElem.table.body;
            var headerRows = tableElem.table.headerRows || 1;
            for (var r = 0; r < body.length; r++) {
                var row = body[r];
                var isHeader = r < headerRows;
                var rowStyle = isHeader ? 'tableHeader' : (r % 2 === 0 ? 'tableBodyEven' : 'tableBodyOdd');
                var cellPadding = isHeader ? [10, 10, 10, 10] : [6, 8, 6, 8];
                for (var c = 0; c < row.length; c++) {
                    var cell = row[c];
                    var text = typeof cell === 'object' && cell !== null && cell.text !== undefined ? cell.text : String(cell);
                    row[c] = { text: text, style: rowStyle, alignment: isHeader ? 'center' : (c === 1 || c === 3 ? 'left' : 'center'), margin: cellPadding };
                }
            }

            var schoolNameCaps = (schoolName || '').toUpperCase();
            var customHeader = [
                { text: schoolNameCaps, fontSize: 28, bold: true, color: 'black', alignment: 'center', decoration: 'underline', margin: [0, 0, 0, 12] },
                { table: { body: [[{ text: addressBarText || ' ', fillColor: navyBlue, color: '#FFFFFF', fontSize: 10, alignment: 'center', margin: [12, 8, 12, 8] }]], widths: ['*'], heights: 32 }, layout: 'noBorders', margin: [0, 0, 0, 12] },
                { table: { body: [[{ text: '', fontSize: 10 }, { text: 'OFFICIAL GRADING SCHEME', fontSize: 13, bold: true, alignment: 'center' }, { text: 'Date: ' + docDate, fontSize: 10, alignment: 'right' }]], widths: ['*', '*', '*'], heights: 26 }, layout: 'noBorders', margin: [0, 0, 0, 12] }
            ];
            doc.content = customHeader.concat([tableElem]);
            doc.footer = function(currentPage, pageCount) {
                return { text: 'Generated by e-maaree | Page ' + currentPage + ' of ' + pageCount, alignment: 'center', fontSize: 8, color: '#999999', margin: [0, 8, 0, 0] };
            };
        }
    };

    if ($('.datatable-grade-list').length) {
        var dt = $('.datatable-grade-list').DataTable({
            autoWidth: false,
            ordering: false,
            dom: '<"datatable-header"fBl><"datatable-scroll"t><"datatable-footer"ip>',
            columnDefs: [
                { targets: 0, className: 'grade-col-sn' },
                { targets: '_all', orderable: false }
            ],
            language: { search: '<span>Filter:</span> _INPUT_', searchPlaceholder: 'Type to filter...', lengthMenu: '<span>Show:</span> _MENU_', paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' } },
            buttons: {
                dom: { button: { className: 'btn btn-light' } },
                buttons: [
                    { extend: 'excelHtml5', className: 'btn btn-light', exportOptions: { columns: [0, 1, 2, 3] }, filename: exportFilename, title: exportLabel, sheetName: 'Grading Scheme' },
                    { extend: 'pdfHtml5', className: 'btn btn-light', exportOptions: { columns: [0, 1, 2, 3] }, filename: exportFilename, orientation: pdfCommon.orientation, pageSize: pdfCommon.pageSize, title: pdfCommon.title, customize: pdfCommon.customize },
                    { extend: 'colvis', text: '<i class="icon-three-bars"></i> Visibility', className: 'btn bg-blue btn-icon dropdown-toggle' }
                ]
            },
            initComplete: function() {
                var snStyle = { 'text-align': 'left', 'padding-left': '15px' };
                $('.dataTables_wrapper table.datatable-grade-list thead th:first-child, .dataTables_wrapper table.datatable-grade-list tbody td:first-child').css(snStyle);
            },
            drawCallback: function() {
                var snStyle = { 'text-align': 'left', 'padding-left': '15px' };
                $('.dataTables_wrapper table.datatable-grade-list thead th:first-child, .dataTables_wrapper table.datatable-grade-list tbody td:first-child').css(snStyle);
            }
        });
        $('.dataTables_length select').select2 && $('.dataTables_length select').select2({ minimumResultsForSearch: Infinity, width: 'auto' });
    }
})();
</script>
@endsection
