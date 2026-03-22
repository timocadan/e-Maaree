@extends('layouts.master')
@section('page_title', 'Student Information - '.$my_class->name)
@section('content')

    <div class="card" id="students-list-card"
         data-school-name="{{ e(Qs::getSystemName()) }}"
         data-school-address="{{ e(Qs::getSetting('address') ?? '') }}"
         data-school-phone="{{ e(Qs::getSetting('phone') ?? '') }}"
         data-school-phone2="{{ e(Qs::getSetting('phone2') ?? '') }}"
         data-school-email="{{ e(Qs::getSetting('system_email') ?? '') }}"
         data-class-name="{{ e($my_class->name ?? '') }}"
         data-academic-year="{{ e(Qs::getSetting('current_session') ?? '') }}">
        <div class="card-header header-elements-inline">
            <h6 class="card-title mb-0">Students List <span class="badge badge-secondary font-weight-normal ml-2">Total Students: {{ $students->count() }}</span></h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#all-students" class="nav-link active" data-toggle="tab">All {{ $my_class->name }} Students</a></li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Sections</a>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach($sections as $s)
                            <a href="#s{{ $s->id }}" class="dropdown-item" data-toggle="tab">{{ $my_class->name.' '.$s->name }}</a>
                        @endforeach
                    </div>
                </li>
            </ul>

            <div class="tab-content">
                <style>
                    /* S/N: left + padding */
                    .datatable-student-list thead th:first-child,
                    .datatable-student-list tbody td:first-child,
                    .datatable-student-list-section thead th:first-child,
                    .datatable-student-list-section tbody td:first-child,
                    .dataTables_wrapper table.datatable-student-list thead th:first-child,
                    .dataTables_wrapper table.datatable-student-list tbody td:first-child,
                    .dataTables_wrapper table.datatable-student-list-section thead th:first-child,
                    .dataTables_wrapper table.datatable-student-list-section tbody td:first-child {
                        text-align: left !important;
                        padding-left: 15px !important;
                    }
                    /* Main table: Name, ADM_No, Class, Section left-aligned */
                    .datatable-student-list thead th:nth-child(2),
                    .datatable-student-list thead th:nth-child(3),
                    .datatable-student-list thead th:nth-child(4),
                    .datatable-student-list thead th:nth-child(5),
                    .datatable-student-list tbody td:nth-child(2),
                    .datatable-student-list tbody td:nth-child(3),
                    .datatable-student-list tbody td:nth-child(4),
                    .datatable-student-list tbody td:nth-child(5),
                    .dataTables_wrapper table.datatable-student-list thead th:nth-child(2),
                    .dataTables_wrapper table.datatable-student-list thead th:nth-child(3),
                    .dataTables_wrapper table.datatable-student-list thead th:nth-child(4),
                    .dataTables_wrapper table.datatable-student-list thead th:nth-child(5),
                    .dataTables_wrapper table.datatable-student-list tbody td:nth-child(2),
                    .dataTables_wrapper table.datatable-student-list tbody td:nth-child(3),
                    .dataTables_wrapper table.datatable-student-list tbody td:nth-child(4),
                    .dataTables_wrapper table.datatable-student-list tbody td:nth-child(5) {
                        text-align: left !important;
                    }
                    /* Main table: Action right-aligned */
                    .datatable-student-list thead th:nth-child(6),
                    .datatable-student-list tbody td:nth-child(6),
                    .dataTables_wrapper table.datatable-student-list thead th:nth-child(6),
                    .dataTables_wrapper table.datatable-student-list tbody td:nth-child(6) {
                        text-align: right !important;
                    }
                    /* Section table: Name, ADM_No left-aligned */
                    .datatable-student-list-section thead th:nth-child(2),
                    .datatable-student-list-section thead th:nth-child(3),
                    .datatable-student-list-section tbody td:nth-child(2),
                    .datatable-student-list-section tbody td:nth-child(3),
                    .dataTables_wrapper table.datatable-student-list-section thead th:nth-child(2),
                    .dataTables_wrapper table.datatable-student-list-section thead th:nth-child(3),
                    .dataTables_wrapper table.datatable-student-list-section tbody td:nth-child(2),
                    .dataTables_wrapper table.datatable-student-list-section tbody td:nth-child(3) {
                        text-align: left !important;
                    }
                    /* Section table: Action right-aligned */
                    .datatable-student-list-section thead th:nth-child(4),
                    .datatable-student-list-section tbody td:nth-child(4),
                    .dataTables_wrapper table.datatable-student-list-section thead th:nth-child(4),
                    .dataTables_wrapper table.datatable-student-list-section tbody td:nth-child(4) {
                        text-align: right !important;
                    }
                </style>
                <div class="tab-pane fade show active" id="all-students">
                    <table class="table datatable-student-list">
                        <thead>
                        <tr>
                            <th class="font-weight-semibold" style="width: 40px;">S/N</th>
                            <th class="font-weight-semibold">Name</th>
                            <th class="font-weight-semibold">ADM_No</th>
                            <th class="font-weight-semibold">Class</th>
                            <th class="font-weight-semibold">Section</th>
                            <th class="font-weight-semibold text-right no-export" style="width: 70px;">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($students as $s)
                            <tr>
                                <td style="width: 40px;">{{ $loop->iteration }}</td>
                                {{-- <td><img class="rounded-circle" style="height: 40px; width: 40px;" src="{{ $s->user->photo }}" alt="photo"></td> --}}
                                <td>{{ $s->user->name }}</td>
                                <td>{{ $s->adm_no }}</td>
                                <td>{{ $s->my_class->name }}</td>
                                <td>{{ $s->section->name }}</td>
                                {{-- <td>{{ $s->user->email }}</td> --}}
                                <td class="text-right" style="width: 70px;">
                                    <div class="list-icons">
                                        <div class="dropdown">
                                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                <i class="icon-menu9"></i>
                                            </a>

                                            <div class="dropdown-menu dropdown-menu-left">
                                                <a href="{{ route('students.show', Qs::hash($s->id)) }}" class="dropdown-item"><i class="icon-eye"></i> View Profile</a>
                                                @if(Qs::userIsTeamSA())
                                                    <a href="{{ route('students.edit', Qs::hash($s->id)) }}" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                                    <a href="{{ route('st.reset_pass', Qs::hash($s->user->id)) }}" class="dropdown-item"><i class="icon-lock"></i> Reset password</a>
                                                @endif
                                                <a target="_blank" href="{{ route('marks.year_selector', Qs::hash($s->user->id)) }}" class="dropdown-item"><i class="icon-check"></i> Marksheet</a>

                                                {{--Delete--}}
                                                @if(Qs::userIsSuperAdmin())
                                                    <a id="{{ Qs::hash($s->user->id) }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item"><i class="icon-trash"></i> Delete</a>
                                                    <form method="post" id="item-delete-{{ Qs::hash($s->user->id) }}" action="{{ route('students.destroy', Qs::hash($s->user->id)) }}" class="hidden">@csrf @method('delete')</form>
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

                @foreach($sections as $se)
                    <div class="tab-pane fade" id="s{{$se->id}}">                         <table class="table datatable-student-list-section">
                            <thead>
                            <tr>
                                <th class="font-weight-semibold" style="width: 40px;">S/N</th>
                                <th class="font-weight-semibold">Name</th>
                                <th class="font-weight-semibold">ADM_No</th>
                                <th class="font-weight-semibold text-right no-export" style="width: 70px;">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($students->where('section_id', $se->id) as $sr)
                                <tr>
                                    <td style="width: 40px;">{{ $loop->iteration }}</td>
                                    {{-- <td><img class="rounded-circle" style="height: 40px; width: 40px;" src="{{ $sr->user->photo }}" alt="photo"></td> --}}
                                    <td>{{ $sr->user->name }}</td>
                                    <td>{{ $sr->adm_no }}</td>
                                    {{-- <td>{{ $sr->user->email }}</td> --}}
                                    <td class="text-right" style="width: 70px;">
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                    <i class="icon-menu9"></i>
                                                </a>

                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a href="{{ route('students.show', Qs::hash($sr->id)) }}" class="dropdown-item"><i class="icon-eye"></i> View Info</a>
                                                    @if(Qs::userIsTeamSA())
                                                        <a href="{{ route('students.edit', Qs::hash($sr->id)) }}" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                                        <a href="{{ route('st.reset_pass', Qs::hash($sr->user->id)) }}" class="dropdown-item"><i class="icon-lock"></i> Reset password</a>
                                                    @endif
                                                    <a href="#" class="dropdown-item"><i class="icon-check"></i> Marksheet</a>

                                                    {{--Delete--}}
                                                    @if(Qs::userIsSuperAdmin())
                                                        <a id="{{ Qs::hash($sr->user->id) }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item"><i class="icon-trash"></i> Delete</a>
                                                        <form method="post" id="item-delete-{{ Qs::hash($sr->user->id) }}" action="{{ route('students.destroy', Qs::hash($sr->user->id)) }}" class="hidden">@csrf @method('delete')</form>
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

    {{--Student List Ends--}}

@endsection

@section('scripts')
<script>
(function() {
    if (!$().DataTable) return;
    var $card = $('#students-list-card');
    var schoolName = $card.data('school-name') || 'Student List';
    var address = $card.data('school-address') || '';
    var phone = $card.data('school-phone') || '';
    var phone2 = $card.data('school-phone2') || '';
    var email = $card.data('school-email') || '';
    var className = $card.data('class-name') || '—';
    var academicYear = $card.data('academic-year') || '';
    var telLine = [phone, phone2].filter(Boolean).join(', ');
    var addressBarText = [address, telLine ? ('Tel: ' + telLine) : '', email].filter(Boolean).join(' | ');
    var docDate = new Date().toLocaleDateString();
    var exportLabel = schoolName + ' - ' + className + ' Student List';
    var exportFilename = exportLabel.replace(/[/\\:*?"<>|]/g, '-');
    var navyBlue = '#002147';

    function excelPremiumCustomize(xlsx, numCols) {
        numCols = numCols || 5;
        var $ = window.jQuery || window.$;
        if (!$ || !xlsx.xl) return;
        var wb = xlsx.xl['workbook.xml'];
        var ws = xlsx.xl.worksheets['sheet1.xml'];
        var styleDoc = xlsx.xl['styles.xml'];
        if (!wb || !ws || !styleDoc) return;
        var ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

        $(wb).find('sheets sheet').attr('name', 'Student List');

        var fontsEl = styleDoc.getElementsByTagName('fonts')[0];
        var fillsEl = styleDoc.getElementsByTagName('fills')[0];
        var cellXfsEl = styleDoc.getElementsByTagName('cellXfs')[0];
        if (!fontsEl || !fillsEl || !cellXfsEl) return;

        function el(name, attrs, children) {
            var node = styleDoc.createElementNS ? styleDoc.createElementNS(ns, name) : styleDoc.createElement(name);
            if (attrs) for (var k in attrs) node.setAttribute(k, attrs[k]);
            if (children) for (var i = 0; i < children.length; i++) node.appendChild(children[i]);
            return node;
        }
        function fontChild(name, attrs) { return el(name, attrs || {}); }

        var fontTitle = el('font', {}, [
            fontChild('sz', { val: '14' }),
            fontChild('name', { val: 'Calibri' }),
            fontChild('b', {})
        ]);
        var fontHeader = el('font', {}, [
            fontChild('sz', { val: '11' }),
            fontChild('name', { val: 'Calibri' }),
            fontChild('b', {}),
            fontChild('color', { rgb: 'FFFFFFFF' })
        ]);
        fontsEl.appendChild(fontTitle);
        fontsEl.appendChild(fontHeader);
        fontsEl.setAttribute('count', '' + (parseInt(fontsEl.getAttribute('count'), 10) + 2));

        var pat = el('patternFill', { patternType: 'solid' });
        pat.appendChild(el('fgColor', { rgb: 'FF' + navyBlue.replace('#', '') }));
        pat.appendChild(el('bgColor', { indexed: '64' }));
        var fillNavy = el('fill', {}, [pat]);
        fillsEl.appendChild(fillNavy);
        fillsEl.setAttribute('count', String(parseInt(fillsEl.getAttribute('count'), 10) + 1));

        var xfTitle = el('xf', {
            numFmtId: '0', fontId: '5', fillId: '0', borderId: '0',
            applyFont: '1', applyFill: '1', applyBorder: '1', applyAlignment: '1', xfId: '0'
        }, [el('alignment', { horizontal: 'center' })]);
        var xfHeader = el('xf', {
            numFmtId: '0', fontId: '6', fillId: '6', borderId: '1',
            applyFont: '1', applyFill: '1', applyBorder: '1', applyAlignment: '1', xfId: '0'
        }, [el('alignment', { horizontal: 'center' })]);
        var xfDataCenter = el('xf', {
            numFmtId: '0', fontId: '0', fillId: '0', borderId: '1',
            applyFont: '1', applyFill: '1', applyBorder: '1', applyAlignment: '1', xfId: '0'
        }, [el('alignment', { horizontal: 'center' })]);
        var xfDataLeft = el('xf', {
            numFmtId: '0', fontId: '0', fillId: '0', borderId: '1',
            applyFont: '1', applyFill: '1', applyBorder: '1', applyAlignment: '1', xfId: '0'
        }, [el('alignment', { horizontal: 'left' })]);
        cellXfsEl.appendChild(xfTitle);
        cellXfsEl.appendChild(xfHeader);
        cellXfsEl.appendChild(xfDataCenter);
        cellXfsEl.appendChild(xfDataLeft);
        var xfCount = parseInt(cellXfsEl.getAttribute('count'), 10) + 4;
        cellXfsEl.setAttribute('count', '' + xfCount);
        var styleTitle = xfCount - 4;
        var styleHeader = xfCount - 3;
        var styleDataCenter = xfCount - 2;
        var styleDataLeft = xfCount - 1;

        var sheetData = ws.getElementsByTagName('sheetData')[0];
        if (!sheetData) return;
        var rows = sheetData.getElementsByTagName('row');
        for (var r = 0; r < rows.length; r++) {
            var row = rows[r];
            var cells = row.getElementsByTagName('c');
            for (var ci = 0; ci < cells.length; ci++) {
                var cell = cells[ci];
                if (r === 0) {
                    cell.setAttribute('s', '' + styleTitle);
                } else if (r === 1) {
                    cell.setAttribute('s', '' + styleHeader);
                } else {
                    cell.setAttribute('s', ci === 1 ? '' + styleDataLeft : '' + styleDataCenter);
                }
            }
        }

        var colsEl = ws.getElementsByTagName('cols')[0];
        if (colsEl) {
            var cols = colsEl.getElementsByTagName('col');
            if (cols.length > 1) cols[1].setAttribute('width', '35');
        }
    }

    var pdfCommon = {
        orientation: 'landscape',
        pageSize: 'A4',
        title: exportLabel,
        filename: exportFilename,
        customize: function(doc) {
            doc.pageOrientation = 'landscape';
            doc.pageSize = 'A4';
            doc.pageMargins = [40, 20, 40, 56];

            var navyBlue = '#002147';
            var borderGrey = '#dddddd';
            doc.styles.tableHeader = { bold: true, fontSize: 11, color: '#FFFFFF', fillColor: navyBlue, alignment: 'center' };
            doc.styles.tableBodyOdd = { fillColor: '#f5f5f5', fontSize: 10 };
            doc.styles.tableBodyEven = { fillColor: '#ffffff', fontSize: 10 };

            var tableElem = null;
            for (var i = 0; i < doc.content.length; i++) {
                if (doc.content[i].table) {
                    tableElem = doc.content[i];
                    break;
                }
            }
            if (!tableElem || !tableElem.table) return;

            var numCols = tableElem.table.body[0] ? tableElem.table.body[0].length : 5;
            tableElem.table.widths = numCols === 5
                ? ['7%', '33%', '25%', '20%', '15%']
                : ['15%', '55%', '30%'];
            tableElem.layout = {
                hLineWidth: function() { return 0.5; },
                vLineWidth: function() { return 0.5; },
                hLineColor: function() { return borderGrey; },
                vLineColor: function() { return borderGrey; }
            };

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
                    row[c] = {
                        text: text,
                        style: rowStyle,
                        alignment: isHeader ? 'center' : (c === 1 ? 'left' : 'center'),
                        margin: cellPadding
                    };
                }
            }

            // Document title & meta: left (Class | Section), center (OFFICIAL STUDENT ENROLLMENT LIST, no underline), right (Academic Year | Date)
            var metaLeft = 'Class: ' + className + ' | Section: All';
            var metaRight = (academicYear ? 'Academic Year: ' + academicYear + '\n' : '') + 'Date: ' + docDate;
            var metaTable = {
                table: {
                    body: [[
                        { text: metaLeft, fontSize: 10, alignment: 'left' },
                        { text: 'OFFICIAL STUDENT ENROLLMENT LIST', fontSize: 13, bold: true, alignment: 'center' },
                        { text: metaRight, fontSize: 10, alignment: 'right' }
                    ]],
                    widths: ['*', '*', '*'],
                    heights: 26
                },
                layout: 'noBorders',
                margin: [0, 0, 0, 12]
            };

            var schoolNameCaps = (schoolName || '').toUpperCase();
            var customHeader = [
                { text: schoolNameCaps, fontSize: 28, bold: true, color: 'black', alignment: 'center', decoration: 'underline', margin: [0, 0, 0, 12] },
                {
                    table: {
                        body: [[{
                            text: addressBarText || ' ',
                            fillColor: navyBlue,
                            color: '#FFFFFF',
                            fontSize: 10,
                            alignment: 'center',
                            margin: [12, 8, 12, 8]
                        }]],
                        widths: ['*'],
                        heights: 32
                    },
                    layout: 'noBorders',
                    margin: [0, 0, 0, 12]
                },
                metaTable
            ];

            doc.content = customHeader.concat([tableElem]);

            doc.footer = function(currentPage, pageCount) {
                return {
                    text: 'Generated by e-maaree | Page ' + currentPage + ' of ' + pageCount,
                    alignment: 'center',
                    fontSize: 8,
                    color: '#999999',
                    margin: [0, 8, 0, 0]
                };
            };
        }
    };

    $('.datatable-student-list').DataTable({
        autoWidth: false,
        dom: '<"datatable-header"fBl><"datatable-scroll"t><"datatable-footer"ip>',
        columnDefs: [ { targets: 0, className: 'student-col-sn' } ],
        language: {
            search: '<span>Filter:</span> _INPUT_',
            searchPlaceholder: 'Type to filter...',
            lengthMenu: '<span>Show:</span> _MENU_',
            paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' }
        },
        initComplete: function() {
            var $t = $('.dataTables_wrapper table.datatable-student-list');
            $t.find('thead th:first-child, tbody td:first-child').css({ 'text-align': 'left', 'padding-left': '15px' });
            $t.find('thead th:nth-child(2), thead th:nth-child(3), thead th:nth-child(4), thead th:nth-child(5), tbody td:nth-child(2), tbody td:nth-child(3), tbody td:nth-child(4), tbody td:nth-child(5)').css('text-align', 'left');
            $t.find('thead th:nth-child(6), tbody td:nth-child(6)').css('text-align', 'right');
        },
        drawCallback: function() {
            var $t = $('.dataTables_wrapper table.datatable-student-list');
            $t.find('thead th:first-child, tbody td:first-child').css({ 'text-align': 'left', 'padding-left': '15px' });
            $t.find('thead th:nth-child(2), thead th:nth-child(3), thead th:nth-child(4), thead th:nth-child(5), tbody td:nth-child(2), tbody td:nth-child(3), tbody td:nth-child(4), tbody td:nth-child(5)').css('text-align', 'left');
            $t.find('thead th:nth-child(6), tbody td:nth-child(6)').css('text-align', 'right');
        },
        buttons: {
            dom: { button: { className: 'btn btn-light' } },
            buttons: [
                {
                    extend: 'excelHtml5',
                    className: 'btn btn-light',
                    exportOptions: { columns: [0, 1, 2, 3, 4] },
                    filename: exportFilename,
                    title: exportLabel,
                    sheetName: 'Student List',
                    customize: function(xlsx) { excelPremiumCustomize(xlsx, 5); }
                },
                {
                    extend: 'pdfHtml5',
                    className: 'btn btn-light',
                    exportOptions: { columns: [0, 1, 2, 3, 4] },
                    filename: exportFilename,
                    orientation: pdfCommon.orientation,
                    pageSize: pdfCommon.pageSize,
                    title: pdfCommon.title,
                    customize: pdfCommon.customize
                },
                { extend: 'colvis', text: '<i class="icon-three-bars"></i> Visibility', className: 'btn bg-blue btn-icon dropdown-toggle' }
            ]
        }
    });

    $('.datatable-student-list-section').DataTable({
        autoWidth: false,
        dom: '<"datatable-header"fBl><"datatable-scroll"t><"datatable-footer"ip>',
        columnDefs: [ { targets: 0, className: 'student-col-sn' } ],
        language: {
            search: '<span>Filter:</span> _INPUT_',
            searchPlaceholder: 'Type to filter...',
            lengthMenu: '<span>Show:</span> _MENU_',
            paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' }
        },
        initComplete: function() {
            var $t = $('.dataTables_wrapper table.datatable-student-list-section');
            $t.find('thead th:first-child, tbody td:first-child').css({ 'text-align': 'left', 'padding-left': '15px' });
            $t.find('thead th:nth-child(2), thead th:nth-child(3), tbody td:nth-child(2), tbody td:nth-child(3)').css('text-align', 'left');
            $t.find('thead th:nth-child(4), tbody td:nth-child(4)').css('text-align', 'right');
        },
        drawCallback: function() {
            var $t = $('.dataTables_wrapper table.datatable-student-list-section');
            $t.find('thead th:first-child, tbody td:first-child').css({ 'text-align': 'left', 'padding-left': '15px' });
            $t.find('thead th:nth-child(2), thead th:nth-child(3), tbody td:nth-child(2), tbody td:nth-child(3)').css('text-align', 'left');
            $t.find('thead th:nth-child(4), tbody td:nth-child(4)').css('text-align', 'right');
        },
        buttons: {
            dom: { button: { className: 'btn btn-light' } },
            buttons: [
                {
                    extend: 'excelHtml5',
                    className: 'btn btn-light',
                    exportOptions: { columns: [0, 1, 2] },
                    filename: exportFilename,
                    title: exportLabel,
                    sheetName: 'Student List',
                    customize: function(xlsx) { excelPremiumCustomize(xlsx, 3); }
                },
                {
                    extend: 'pdfHtml5',
                    className: 'btn btn-light',
                    exportOptions: { columns: [0, 1, 2] },
                    filename: exportFilename,
                    orientation: pdfCommon.orientation,
                    pageSize: pdfCommon.pageSize,
                    title: pdfCommon.title,
                    customize: pdfCommon.customize
                },
                { extend: 'colvis', text: '<i class="icon-three-bars"></i> Visibility', className: 'btn bg-blue btn-icon dropdown-toggle' }
            ]
        }
    });

    $('.dataTables_length select').select2 && $('.dataTables_length select').select2({ minimumResultsForSearch: Infinity, width: 'auto' });
})();
</script>
@endsection
