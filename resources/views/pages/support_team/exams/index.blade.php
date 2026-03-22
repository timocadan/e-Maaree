@extends('layouts.master')
@section('page_title', 'Manage Exams')
@section('content')

    <div class="card" id="exams-list-card"
         data-school-name="{{ e(Qs::getSystemName()) }}"
         data-school-address="{{ e(Qs::getSetting('address') ?? '') }}"
         data-school-phone="{{ e(Qs::getSetting('phone') ?? '') }}"
         data-school-phone2="{{ e(Qs::getSetting('phone2') ?? '') }}"
         data-school-email="{{ e(Qs::getSetting('system_email') ?? '') }}">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Exams</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#all-exams" class="nav-link active" data-toggle="tab">Manage Exam</a></li>
                <li class="nav-item"><a href="#new-exam" class="nav-link" data-toggle="tab"><i class="icon-plus2"></i> Add Exam</a></li>
            </ul>

            <div class="tab-content">
                    <style>
                        /* Exams table: navy header + left alignment (S/N, Name, Term, Session), Action right */
                        .datatable-exam-list thead th { background-color: #002147; color: #fff; font-weight: 600; border-color: #002147; }
                        .datatable-exam-list thead th:first-child,
                        .datatable-exam-list tbody td:first-child,
                        .dataTables_wrapper table.datatable-exam-list thead th:first-child,
                        .dataTables_wrapper table.datatable-exam-list tbody td:first-child {
                            text-align: left !important;
                            padding-left: 15px !important;
                        }
                        .datatable-exam-list thead th:nth-child(2),
                        .datatable-exam-list thead th:nth-child(3),
                        .datatable-exam-list thead th:nth-child(4),
                        .datatable-exam-list tbody td:nth-child(2),
                        .datatable-exam-list tbody td:nth-child(3),
                        .datatable-exam-list tbody td:nth-child(4),
                        .dataTables_wrapper table.datatable-exam-list thead th:nth-child(2),
                        .dataTables_wrapper table.datatable-exam-list thead th:nth-child(3),
                        .dataTables_wrapper table.datatable-exam-list thead th:nth-child(4),
                        .dataTables_wrapper table.datatable-exam-list tbody td:nth-child(2),
                        .dataTables_wrapper table.datatable-exam-list tbody td:nth-child(3),
                        .dataTables_wrapper table.datatable-exam-list tbody td:nth-child(4) {
                            text-align: left !important;
                        }
                        .datatable-exam-list thead th:nth-child(5),
                        .datatable-exam-list tbody td:nth-child(5),
                        .dataTables_wrapper table.datatable-exam-list thead th:nth-child(5),
                        .dataTables_wrapper table.datatable-exam-list tbody td:nth-child(5) {
                            text-align: right !important;
                        }
                    </style>
                    <div class="tab-pane fade show active" id="all-exams">
                        <table class="table datatable-exam-list">
                            <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Term</th>
                                <th>Session</th>
                                <th class="no-export">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($exams as $ex)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $ex->name }}</td>
                                    <td>{{ $ex->term == 1 ? 'Term 1' : ($ex->term == 2 ? 'Term 2' : 'Term ' . $ex->term) }}</td>
                                    <td>{{ $ex->year }}</td>
                                    <td class="text-right">
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                <i class="icon-menu9"></i>
                                                </a>

                                                <div class="dropdown-menu dropdown-menu-left">
                                                    @if(Qs::userIsTeamSA())
                                                    {{--Edit--}}
                                                    <a href="{{ request()->root() }}/exams/{{ $ex->id }}/edit" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                                   @endif
                                                    @if(Qs::userIsSuperAdmin())
                                                    {{--Delete--}}
                                                    <a href="#" id="{{ $ex->id }}" onclick="confirmDelete(this.id); return false;" class="dropdown-item"><i class="icon-trash"></i> Delete</a>
                                                    <form method="get" id="item-delete-{{ $ex->id }}" action="{{ request()->root() }}/exams/{{ $ex->id }}/delete" class="hidden">@csrf</form>
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

                <div class="tab-pane fade" id="new-exam">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info border-0 alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>

                                <span>You are creating an Exam for the Current Session <strong>{{ Qs::getSetting('current_session') }}</strong></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <form method="post" action="{{ route('exams.store') }}">
                                @csrf
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <input name="name" value="{{ old('name') }}" required type="text" class="form-control" placeholder="Name of Exam">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="term" class="col-lg-3 col-form-label font-weight-semibold">Term</label>
                                    <div class="col-lg-9">
                                        <select data-placeholder="Select Term" class="form-control select-search" name="term" id="term">
                                            <option {{ old('term') == 1 ? 'selected' : '' }} value="1">Term 1</option>
                                            <option {{ old('term') == 2 ? 'selected' : '' }} value="2">Term 2</option>
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
            </div>
        </div>
    </div>

    {{--Exams List Ends--}}

@endsection

@section('scripts')
<script>
(function() {
    if (!$().DataTable) return;
    var $card = $('#exams-list-card');
    if (!$card.length) return;
    var schoolName = $card.data('school-name') || 'Exam List';
    var address = $card.data('school-address') || '';
    var phone = $card.data('school-phone') || '';
    var phone2 = $card.data('school-phone2') || '';
    var email = $card.data('school-email') || '';
    var telLine = [phone, phone2].filter(Boolean).join(', ');
    var addressBarText = [address, telLine ? ('Tel: ' + telLine) : '', email].filter(Boolean).join(' | ');
    var docDate = new Date().toLocaleDateString();
    var exportLabel = schoolName + ' - Exam List';
    var exportFilename = exportLabel.replace(/[/\\:*?"<>|]/g, '-');
    var navyBlue = '#002147';

    var pdfCommon = {
        orientation: 'landscape',
        pageSize: 'A4',
        title: exportLabel,
        filename: exportFilename,
        customize: function(doc) {
            doc.pageOrientation = 'landscape';
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
            tableElem.table.widths = numCols === 4 ? ['8%', '35%', '22%', '35%'] : ['*'];
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
                    row[c] = { text: text, style: rowStyle, alignment: isHeader ? 'center' : (c === 1 ? 'left' : 'center'), margin: cellPadding };
                }
            }

            var schoolNameCaps = (schoolName || '').toUpperCase();
            var customHeader = [
                { text: schoolNameCaps, fontSize: 28, bold: true, color: 'black', alignment: 'center', decoration: 'underline', margin: [0, 0, 0, 12] },
                { table: { body: [[{ text: addressBarText || ' ', fillColor: navyBlue, color: '#FFFFFF', fontSize: 10, alignment: 'center', margin: [12, 8, 12, 8] }]], widths: ['*'], heights: 32 }, layout: 'noBorders', margin: [0, 0, 0, 12] },
                { table: { body: [[{ text: '', fontSize: 10 }, { text: 'OFFICIAL EXAM LIST', fontSize: 13, bold: true, alignment: 'center' }, { text: 'Date: ' + docDate, fontSize: 10, alignment: 'right' }]], widths: ['*', '*', '*'], heights: 26 }, layout: 'noBorders', margin: [0, 0, 0, 12] }
            ];
            doc.content = customHeader.concat([tableElem]);
            doc.footer = function(currentPage, pageCount) {
                return { text: 'Generated by e-maaree | Page ' + currentPage + ' of ' + pageCount, alignment: 'center', fontSize: 8, color: '#999999', margin: [0, 8, 0, 0] };
            };
        }
    };

    if ($('.datatable-exam-list').length) {
        $('.datatable-exam-list').DataTable({
            autoWidth: false,
            dom: '<"datatable-header"fBl><"datatable-scroll"t><"datatable-footer"ip>',
            language: { search: '<span>Filter:</span> _INPUT_', searchPlaceholder: 'Type to filter...', lengthMenu: '<span>Show:</span> _MENU_', paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' } },
            initComplete: function() {
                var $t = $('.dataTables_wrapper table.datatable-exam-list');
                $t.find('thead th:first-child, tbody td:first-child').css({ 'text-align': 'left', 'padding-left': '15px' });
                $t.find('thead th:nth-child(2), thead th:nth-child(3), thead th:nth-child(4), tbody td:nth-child(2), tbody td:nth-child(3), tbody td:nth-child(4)').css('text-align', 'left');
                $t.find('thead th:nth-child(5), tbody td:nth-child(5)').css('text-align', 'right');
            },
            drawCallback: function() {
                var $t = $('.dataTables_wrapper table.datatable-exam-list');
                $t.find('thead th:first-child, tbody td:first-child').css({ 'text-align': 'left', 'padding-left': '15px' });
                $t.find('thead th:nth-child(2), thead th:nth-child(3), thead th:nth-child(4), tbody td:nth-child(2), tbody td:nth-child(3), tbody td:nth-child(4)').css('text-align', 'left');
                $t.find('thead th:nth-child(5), tbody td:nth-child(5)').css('text-align', 'right');
            },
            buttons: {
                dom: { button: { className: 'btn btn-light' } },
                buttons: [
                    { extend: 'excelHtml5', className: 'btn btn-light', exportOptions: { columns: [0, 1, 2, 3] }, filename: exportFilename, title: exportLabel, sheetName: 'Exam List' },
                    { extend: 'pdfHtml5', className: 'btn btn-light', exportOptions: { columns: [0, 1, 2, 3] }, filename: exportFilename, orientation: pdfCommon.orientation, pageSize: pdfCommon.pageSize, title: pdfCommon.title, customize: pdfCommon.customize },
                    { extend: 'colvis', text: '<i class="icon-three-bars"></i> Visibility', className: 'btn bg-blue btn-icon dropdown-toggle' }
                ]
            }
        });
        $('.dataTables_length select').select2 && $('.dataTables_length select').select2({ minimumResultsForSearch: Infinity, width: 'auto' });
    }
})();
</script>
@endsection
