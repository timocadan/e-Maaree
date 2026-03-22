@extends('layouts.master')
@section('page_title', 'Manage Promotions')
@section('content')

    {{--Reset All--}}
    <div class="card">
        <div class="card-body text-center">
            <button id="promotion-reset-all" class="btn btn-danger btn-large">Reset All Promotions for the Session</button>
        </div>
    </div>

{{-- Reset Promotions --}}
    <div class="card" id="promotion-reset-card"
         data-school-name="{{ e(Qs::getSystemName()) }}"
         data-school-address="{{ e(Qs::getSetting('address') ?? '') }}"
         data-school-phone="{{ e(Qs::getSetting('phone') ?? '') }}"
         data-school-phone2="{{ e(Qs::getSetting('phone2') ?? '') }}"
         data-school-email="{{ e(Qs::getSetting('system_email') ?? '') }}"
         data-old-year="{{ e($old_year ?? '') }}"
         data-new-year="{{ e($new_year ?? '') }}">
        <div class="card-header header-elements-inline">
            <h5 class="card-title font-weight-bold">Manage Promotions - Students Who Were Promoted From <span class="text-danger">{{ $old_year }}</span> TO <span class="text-success">{{ $new_year }}</span> Session</h5>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">

            <table id="promotions-list" class="table datatable-promotion-reset">
                <thead>
                <tr>
                    <th>S/N</th>
                    {{-- <th>Photo</th> --}}
                    <th>Name</th>
                    <th>From Class</th>
                    <th>To Class</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($promotions->sortBy('fc.name')->sortBy('student.name') as $p)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        {{-- <td><img class="rounded-circle" style="height: 40px; width: 40px;" src="{{ $p->student->photo }}" alt="photo"></td> --}}
                        <td>{{ $p->student->name }}</td>
                        <td>{{ $p->fc->name.' '.$p->fs->name }}</td>
                        <td>{{ $p->tc->name.' '.$p->ts->name }}</td>
                        @if($p->status === 'P')
                            <td><span class="text-success">Promoted</span></td>
                        @elseif($p->status === 'D')
                            <td><span class="text-danger">Not Promoted</span></td>
                        @else
                            <td><span class="text-primary">Graduated</span></td>
                        @endif
                        <td class="text-center">
                            <button data-id="{{ $p->id }}" class="btn btn-danger promotion-reset">Reset</button>
                            <form id="promotion-reset-{{ $p->id }}" method="post" action="{{ route('students.promotion_reset', $p->id) }}">@csrf @method('DELETE')</form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('scripts')
<script>
(function() {
    if (!$().DataTable) return;

    var $card = $('#promotion-reset-card');
    var schoolName = $card.data('school-name') || 'Promotion List';
    var address = $card.data('school-address') || '';
    var phone = $card.data('school-phone') || '';
    var phone2 = $card.data('school-phone2') || '';
    var email = $card.data('school-email') || '';
    var oldYear = $card.data('old-year') || '';
    var newYear = $card.data('new-year') || '';
    var telLine = [phone, phone2].filter(Boolean).join(', ');
    var addressBarText = [address, telLine ? ('Tel: ' + telLine) : '', email].filter(Boolean).join(' | ');
    var docDate = new Date().toLocaleDateString();
    var exportLabel = schoolName + ' - Student Promotion List';
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
        $(wb).find('sheets sheet').attr('name', 'Promotion List');
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
            tableElem.table.widths = ['8%', '28%', '22%', '22%', '20%'];
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
            var metaLeft = (oldYear && newYear) ? ('Session: ' + oldYear + ' to ' + newYear) : '';
            var metaTable = {
                table: { body: [[ { text: metaLeft, fontSize: 10, alignment: 'left' }, { text: 'STUDENT PROMOTION LIST', fontSize: 13, bold: true, alignment: 'center' }, { text: 'Date: ' + docDate, fontSize: 10, alignment: 'right' } ]], widths: ['*', '*', '*'], heights: 26 },
                layout: 'noBorders',
                margin: [0, 15, 0, 12]
            };
            var schoolNameCaps = (schoolName || '').toUpperCase();
            var customHeader = [
                { text: schoolNameCaps, fontSize: 28, bold: true, color: 'black', alignment: 'center', decoration: 'underline', margin: [0, 0, 0, 12] },
                { table: { body: [[{ text: addressBarText || ' ', fillColor: navyBlue, color: '#FFFFFF', fontSize: 10, alignment: 'center', margin: [12, 8, 12, 8] }]], widths: ['*'], heights: 32 }, layout: 'noBorders', margin: [0, 0, 0, 12] },
                metaTable
            ];
            var signatureBlock = {
                columns: [
                    { stack: [ { text: '__________________________', fontSize: 10 }, { text: "Class Teacher's Signature", fontSize: 9, margin: [0, 4, 0, 0] } ], width: '*' },
                    { stack: [ { text: '__________________________', fontSize: 10 }, { text: "Principal's Signature & Stamp", fontSize: 9, margin: [0, 4, 0, 0] } ], width: '*' }
                ],
                margin: [0, 24, 0, 0]
            };
            doc.content = customHeader.concat([tableElem, signatureBlock]);
            doc.footer = function(currentPage, pageCount) {
                return { text: 'Generated by e-maaree | Page ' + currentPage + ' of ' + pageCount, alignment: 'center', fontSize: 8, color: '#999999', margin: [0, 8, 0, 0] };
            };
        }
    };

    $('.datatable-promotion-reset').DataTable({
        autoWidth: false,
        dom: '<"datatable-header"fBl><"datatable-scroll"t><"datatable-footer"ip>',
        language: { search: '<span>Filter:</span> _INPUT_', searchPlaceholder: 'Type to filter...', lengthMenu: '<span>Show:</span> _MENU_', paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' } },
        buttons: {
            dom: { button: { className: 'btn btn-light' } },
            buttons: [
                { extend: 'excelHtml5', className: 'btn btn-light', exportOptions: { columns: [0, 1, 2, 3, 4] }, filename: exportFilename, title: exportLabel, sheetName: 'Promotion List', customize: function(xlsx) { excelPremiumCustomize(xlsx, 5); } },
                { extend: 'pdfHtml5', className: 'btn btn-light', exportOptions: { columns: [0, 1, 2, 3, 4] }, filename: exportFilename, orientation: pdfCommon.orientation, pageSize: pdfCommon.pageSize, title: pdfCommon.title, customize: pdfCommon.customize },
                { extend: 'colvis', text: '<i class="icon-three-bars"></i> Visibility', className: 'btn bg-blue btn-icon dropdown-toggle' }
            ]
        }
    });

    $('.promotion-reset').on('click', function () {
        var pid = $(this).data('id');
        if (confirm('Are You Sure you want to proceed?')) $('form#promotion-reset-' + pid).submit();
        return false;
    });
    $('#promotion-reset-all').on('click', function () {
        if (confirm('Are You Sure you want to proceed?')) {
            $.ajax({
                url: "{{ route('students.promotion_reset_all') }}",
                type: 'DELETE',
                data: { '_token': $('meta[name="csrf-token"]').attr('content') },
                success: function (resp) {
                    $('table#promotions-list > tbody').fadeOut().remove();
                    flash({ msg: resp.msg, type: 'success' });
                }
            });
        }
        return false;
    });
})();
</script>
@endsection
