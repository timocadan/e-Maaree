@extends('layouts.master')
@section('page_title', 'Graduated Students')
@section('content')

<div class="card" id="graduated-students-card"
     data-school-name="{{ e(Qs::getSystemName()) }}"
     data-school-address="{{ e(Qs::getSetting('address') ?? '') }}"
     data-school-phone="{{ e(Qs::getSetting('phone') ?? '') }}"
     data-school-phone2="{{ e(Qs::getSetting('phone2') ?? '') }}"
     data-school-email="{{ e(Qs::getSetting('system_email') ?? '') }}">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Students Graduated</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item"><a href="#all-students" class="nav-link active" data-toggle="tab">All Graduated Students</a></li>
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Select Class</a>
                <div class="dropdown-menu dropdown-menu-right">
                    @foreach($my_classes as $c)
                    <a href="#c{{ $c->id }}" class="dropdown-item" data-toggle="tab">{{ $c->name }}</a>
                    @endforeach
                </div>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="all-students">
                <table class="table datatable-graduated">
                    <thead>
                    <tr>
                        <th>S/N</th>
                        {{-- <th>Photo</th> --}}
                        <th>Name</th>
                        <th>ADM_No</th>
                        <th>Section</th>
                        <th>Grad Year</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($students as $s)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        {{-- <td><img class="rounded-circle" style="height: 40px; width: 40px;" src="{{ $s->user->photo }}" alt="photo"></td> --}}
                        <td>{{ $s->user->name }}</td>
                        <td>{{ $s->adm_no }}</td>
                        <td>{{ $s->my_class->name.' '.$s->section->name }}</td>
                        <td>{{ $s->grad_date }}</td>
                        <td class="text-center">
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

                                        {{--Not Graduated--}}
                                        <a id="{{ Qs::hash($s->id) }}" href="#" onclick="$('form#ng-'+this.id).submit();" class="dropdown-item"><i class="icon-stairs-down"></i> Not Graduated</a>
                                            <form method="post" id="ng-{{ Qs::hash($s->id) }}" action="{{ route('st.not_graduated', Qs::hash($s->id)) }}" class="hidden">@csrf @method('put')</form>
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

            @foreach($my_classes as $mc)
            <div class="tab-pane fade" id="c{{$mc->id}}">                                      <table class="table datatable-graduated">
                    <thead>
                    <tr>
                        <th>S/N</th>
                        {{-- <th>Photo</th> --}}
                        <th>Name</th>
                        <th>ADM_No</th>
                        <th>Section</th>
                        <th>Grad Year</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($students->where('my_class_id', $mc->id) as $s)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            {{-- <td><img class="rounded-circle" style="height: 40px; width: 40px;" src="{{ $s->user->photo }}" alt="photo"></td> --}}
                            <td>{{ $s->user->name }}</td>
                            <td>{{ $s->adm_no }}</td>
                            <td>{{ $s->my_class->name.' '.$s->section->name }}</td>
                            <td>{{ $s->grad_date }}</td>
                            <td class="text-center">
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

                                                {{--Not Graduated--}}
                                                <a id="{{ Qs::hash($s->id) }}" href="#" onclick="$('form#ng-'+this.id).submit();" class="dropdown-item"><i class="icon-stairs-down"></i> Not Graduated</a>
                                                <form method="post" id="ng-{{ Qs::hash($s->id) }}" action="{{ route('st.not_graduated', Qs::hash($s->id)) }}" class="hidden">@csrf @method('put')</form>
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

    var $card = $('#graduated-students-card');
    var schoolName = $card.data('school-name') || 'Graduated Students';
    var address = $card.data('school-address') || '';
    var phone = $card.data('school-phone') || '';
    var phone2 = $card.data('school-phone2') || '';
    var email = $card.data('school-email') || '';
    var telLine = [phone, phone2].filter(Boolean).join(', ');
    var addressBarText = [address, telLine ? ('Tel: ' + telLine) : '', email].filter(Boolean).join(' | ');
    var docDate = new Date().toLocaleDateString();
    var exportLabel = schoolName + ' - Graduated Students List';
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
        $(wb).find('sheets sheet').attr('name', 'Graduated Students');
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
        var fontTitle = el('font', {}, [el('sz', { val: '14' }), el('name', { val: 'Calibri' }), el('b', {})]);
        var fontHeader = el('font', {}, [el('sz', { val: '11' }), el('name', { val: 'Calibri' }), el('b', {}), el('color', { rgb: 'FFFFFFFF' })]);
        fontsEl.appendChild(fontTitle); fontsEl.appendChild(fontHeader);
        fontsEl.setAttribute('count', '' + (parseInt(fontsEl.getAttribute('count'), 10) + 2));
        var pat = el('patternFill', { patternType: 'solid' });
        pat.appendChild(el('fgColor', { rgb: 'FF' + navyBlue.replace('#', '') })); pat.appendChild(el('bgColor', { indexed: '64' }));
        fillsEl.appendChild(el('fill', {}, [pat])); fillsEl.setAttribute('count', String(parseInt(fillsEl.getAttribute('count'), 10) + 1));
        var xfTitle = el('xf', { numFmtId: '0', fontId: '5', fillId: '0', borderId: '0', applyFont: '1', applyFill: '1', applyBorder: '1', applyAlignment: '1', xfId: '0' }, [el('alignment', { horizontal: 'center' })]);
        var xfHeader = el('xf', { numFmtId: '0', fontId: '6', fillId: '6', borderId: '1', applyFont: '1', applyFill: '1', applyBorder: '1', applyAlignment: '1', xfId: '0' }, [el('alignment', { horizontal: 'center' })]);
        var xfDataCenter = el('xf', { numFmtId: '0', fontId: '0', fillId: '0', borderId: '1', applyFont: '1', applyFill: '1', applyBorder: '1', applyAlignment: '1', xfId: '0' }, [el('alignment', { horizontal: 'center' })]);
        var xfDataLeft = el('xf', { numFmtId: '0', fontId: '0', fillId: '0', borderId: '1', applyFont: '1', applyFill: '1', applyBorder: '1', applyAlignment: '1', xfId: '0' }, [el('alignment', { horizontal: 'left' })]);
        cellXfsEl.appendChild(xfTitle); cellXfsEl.appendChild(xfHeader); cellXfsEl.appendChild(xfDataCenter); cellXfsEl.appendChild(xfDataLeft);
        var xfCount = parseInt(cellXfsEl.getAttribute('count'), 10) + 4;
        cellXfsEl.setAttribute('count', '' + xfCount);
        var styleTitle = xfCount - 4, styleHeader = xfCount - 3, styleDataCenter = xfCount - 2, styleDataLeft = xfCount - 1;
        var sheetData = ws.getElementsByTagName('sheetData')[0];
        if (sheetData) {
            var rows = sheetData.getElementsByTagName('row');
            for (var r = 0; r < rows.length; r++) {
                var row = rows[r], cells = row.getElementsByTagName('c');
                for (var ci = 0; ci < cells.length; ci++) {
                    var cell = cells[ci];
                    if (r === 0) cell.setAttribute('s', '' + styleTitle);
                    else if (r === 1) cell.setAttribute('s', '' + styleHeader);
                    else cell.setAttribute('s', ci === 1 ? '' + styleDataLeft : '' + styleDataCenter);
                }
            }
        }
        var colsEl = ws.getElementsByTagName('cols')[0];
        if (colsEl && colsEl.getElementsByTagName('col').length > 1) colsEl.getElementsByTagName('col')[1].setAttribute('width', '35');
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
            var borderGrey = '#dddddd';
            doc.styles.tableHeader = { bold: true, fontSize: 11, color: '#FFFFFF', fillColor: navyBlue, alignment: 'center' };
            doc.styles.tableBodyOdd = { fillColor: '#f5f5f5', fontSize: 10 };
            doc.styles.tableBodyEven = { fillColor: '#ffffff', fontSize: 10 };
            var tableElem = null;
            for (var i = 0; i < doc.content.length; i++) {
                if (doc.content[i].table) { tableElem = doc.content[i]; break; }
            }
            if (!tableElem || !tableElem.table) return;
            var numCols = tableElem.table.body[0] ? tableElem.table.body[0].length : 5;
            tableElem.table.widths = ['7%', '33%', '18%', '25%', '17%'];
            tableElem.layout = { hLineWidth: function() { return 0.5; }, vLineWidth: function() { return 0.5; }, hLineColor: function() { return borderGrey; }, vLineColor: function() { return borderGrey; } };
            var body = tableElem.table.body, headerRows = tableElem.table.headerRows || 1;
            for (var r = 0; r < body.length; r++) {
                var row = body[r], isHeader = r < headerRows;
                var rowStyle = isHeader ? 'tableHeader' : (r % 2 === 0 ? 'tableBodyEven' : 'tableBodyOdd');
                var cellPadding = isHeader ? [10, 10, 10, 10] : [6, 8, 6, 8];
                for (var c = 0; c < row.length; c++) {
                    var cell = row[c];
                    var text = typeof cell === 'object' && cell !== null && cell.text !== undefined ? cell.text : String(cell);
                    row[c] = { text: text, style: rowStyle, alignment: isHeader ? 'center' : (c === 1 ? 'left' : 'center'), margin: cellPadding };
                }
            }
            var metaTable = {
                table: { body: [[ { text: '', fontSize: 10, alignment: 'left' }, { text: 'GRADUATED STUDENTS LIST', fontSize: 13, bold: true, alignment: 'center' }, { text: 'Date: ' + docDate, fontSize: 10, alignment: 'right' } ]], widths: ['*', '*', '*'], heights: 26 },
                layout: 'noBorders',
                margin: [0, 0, 0, 12]
            };
            var schoolNameCaps = (schoolName || '').toUpperCase();
            var customHeader = [
                { text: schoolNameCaps, fontSize: 28, bold: true, color: 'black', alignment: 'center', decoration: 'underline', margin: [0, 0, 0, 12] },
                { table: { body: [[{ text: addressBarText || ' ', fillColor: navyBlue, color: '#FFFFFF', fontSize: 10, alignment: 'center', margin: [12, 8, 12, 8] }]], widths: ['*'], heights: 32 }, layout: 'noBorders', margin: [0, 0, 0, 12] },
                metaTable
            ];
            doc.content = customHeader.concat([tableElem]);
            doc.footer = function(currentPage, pageCount) {
                return { text: 'Generated by e-maaree | Page ' + currentPage + ' of ' + pageCount, alignment: 'center', fontSize: 8, color: '#999999', margin: [0, 8, 0, 0] };
            };
        }
    };

    $('.datatable-graduated').DataTable({
        autoWidth: false,
        dom: '<"datatable-header"fBl><"datatable-scroll"t><"datatable-footer"ip>',
        language: { search: '<span>Filter:</span> _INPUT_', searchPlaceholder: 'Type to filter...', lengthMenu: '<span>Show:</span> _MENU_', paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' } },
        buttons: {
            dom: { button: { className: 'btn btn-light' } },
            buttons: [
                { extend: 'excelHtml5', className: 'btn btn-light', exportOptions: { columns: [0, 1, 2, 3, 4] }, filename: exportFilename, title: exportLabel, sheetName: 'Graduated Students', customize: function(xlsx) { excelPremiumCustomize(xlsx, 5); } },
                { extend: 'pdfHtml5', className: 'btn btn-light', exportOptions: { columns: [0, 1, 2, 3, 4] }, filename: exportFilename, orientation: pdfCommon.orientation, pageSize: pdfCommon.pageSize, title: pdfCommon.title, customize: pdfCommon.customize },
                { extend: 'colvis', text: '<i class="icon-three-bars"></i> Visibility', className: 'btn bg-blue btn-icon dropdown-toggle' }
            ]
        }
    });
})();
</script>
@endsection
