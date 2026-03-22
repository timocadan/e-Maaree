@extends('layouts.master')
@section('page_title', 'Manage Marks')
@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold text-body"><i class="icon-books mr-2 text-body"></i> Manage Exam Marks</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            @include('pages.support_team.marks.selector')
        </div>
    </div>

    @if(!empty($no_blueprint))
        <div class="card">
            <div class="card-body">
                <p class="text-warning mb-0">
                    <i class="icon-warning2 mr-2"></i>
                    {{ $no_blueprint_msg ?? 'Grading scheme not set by Admin for this level/term. Ask an admin to assign a scheme in Manage Levels.' }}
                </p>
            </div>
        </div>
    @elseif(isset($m) && $m && isset($mark_config) && $mark_config)
        <div class="card">
            <div class="marks-info-bar px-3 py-2 bg-light border-bottom">
                <span class="text-dark font-weight-semibold">{{ $m->subject->name }} &nbsp;|&nbsp; {{ $m->my_class->name }} {{ $m->section->name }} &nbsp;|&nbsp; {{ $terms[$m->term] ?? 'Term ' . $m->term }}</span>
            </div>
            <div class="card-body">
                @include('pages.support_team.marks.edit')
            </div>
        </div>
    @endif

    {{--Marks Manage End--}}

@endsection

@section('scripts')
<script>
(function() {
    if (!$().DataTable) return;

    var $table = $('#marks-entry-table');
    if ($table.length === 0) return;

    // Auto-calculate row totals (sum of all assessment inputs).
    // Delegated listeners ensure totals update instantly even after DataTables DOM redraws.
    function recalcRowTotal($row) {
        var sum = 0;
        $row.find('input.score-input').each(function() {
            var raw = $(this).val();
            var v = (raw === '' || raw === null || raw === undefined) ? 0 : parseInt(raw, 10);
            if (isNaN(v)) v = 0;
            sum += v;
        });
        $row.find('input.total-input').val(sum);
    }

    // Initial calculation
    $table.find('tbody tr').each(function() {
        recalcRowTotal($(this));
    });

    // Reactive totals
    $table.on('input.marksTotal keyup.marksTotal change.marksTotal', 'input.score-input', function() {
        recalcRowTotal($(this).closest('tr'));
    });

    // Export context
    var schoolName = @json(Qs::getSystemName());
    var address = @json(Qs::getSetting('address') ?? '');
    var phone = @json(Qs::getSetting('phone') ?? '');
    var phone2 = @json(Qs::getSetting('phone2') ?? '');
    var email = @json(Qs::getSetting('system_email') ?? '');

    var subjectName = @json(isset($m) && $m ? ($m->subject->name ?? '') : '');
    var className = @json(isset($m) && $m ? ($m->my_class->name ?? '') : '');
    var sectionName = @json(isset($m) && $m ? ($m->section->name ?? '') : '');

    var academicYear = @json(Qs::getSetting('current_session') ?? '');
    var termName = @json(isset($m) && $m ? ($terms[$m->term] ?? ('Term ' . $m->term)) : '');
    var docDate = new Date().toLocaleDateString();

    var telLine = [phone, phone2].filter(Boolean).join(', ');
    var addressBarText = [address, telLine ? ('Tel: ' + telLine) : '', email].filter(Boolean).join(' | ');

    var officialTitle = 'OFFICIAL CONTINUOUS ASSESSMENT SHEET';
    var exportLabel = officialTitle;
    var exportFilename = exportLabel.replace(/[/\\:*?"<>|]/g, '-');
    var excelFilename = ((schoolName || 'School') + ' - ' + (subjectName || 'Subject') + ' - ' + (className || 'Class') + ' Mark Sheet')
        .replace(/[/\\:*?"<>|]/g, '-');
    var navyBlue = '#002147';

    function extractInputValue(html) {
        if (html === null || html === undefined) return '';
        if (typeof html !== 'string') return html;
        var m = html.match(/value="([^"]*)"/);
        return m ? m[1] : html;
    }

    function excelPremiumCustomize(xlsx, numCols) {
        numCols = numCols || 5;
        var $ = window.jQuery || window.$;
        if (!$ || !xlsx.xl) return;
        var wb = xlsx.xl['workbook.xml'];
        var ws = xlsx.xl.worksheets['sheet1.xml'];
        var styleDoc = xlsx.xl['styles.xml'];
        if (!wb || !ws || !styleDoc) return;
        var ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

        $(wb).find('sheets sheet').attr('name', 'Mark Sheet');

        var fontsEl = styleDoc.getElementsByTagName('fonts')[0];
        var fillsEl = styleDoc.getElementsByTagName('fills')[0];
        var cellXfsEl = styleDoc.getElementsByTagName('cellXfs')[0];
        if (!fontsEl || !fillsEl || !cellXfsEl) return;

        function colName(idx1) {
            var s = '';
            while (idx1 > 0) {
                var m = (idx1 - 1) % 26;
                s = String.fromCharCode(65 + m) + s;
                idx1 = Math.floor((idx1 - 1) / 26);
            }
            return s;
        }
        function cellRef(col, row) { return colName(col) + row; }
        function el(name, attrs, children) {
            var node = styleDoc.createElementNS ? styleDoc.createElementNS(ns, name) : styleDoc.createElement(name);
            if (attrs) for (var k in attrs) node.setAttribute(k, attrs[k]);
            if (children) for (var i = 0; i < children.length; i++) node.appendChild(children[i]);
            return node;
        }
        function fontChild(name, attrs) { return el(name, attrs || {}); }
        function appendFont(node) {
            var idx = parseInt(fontsEl.getAttribute('count') || '0', 10);
            fontsEl.appendChild(node);
            fontsEl.setAttribute('count', '' + (idx + 1));
            return idx;
        }
        function appendFill(node) {
            var idx = parseInt(fillsEl.getAttribute('count') || '0', 10);
            fillsEl.appendChild(node);
            fillsEl.setAttribute('count', '' + (idx + 1));
            return idx;
        }
        function appendXf(node) {
            var idx = parseInt(cellXfsEl.getAttribute('count') || '0', 10);
            cellXfsEl.appendChild(node);
            cellXfsEl.setAttribute('count', '' + (idx + 1));
            return idx;
        }

        var fontSchool = appendFont(el('font', {}, [
            fontChild('sz', { val: '16' }),
            fontChild('name', { val: 'Calibri' }),
            fontChild('b', {})
        ]));
        var fontWhiteBold = appendFont(el('font', {}, [
            fontChild('sz', { val: '11' }),
            fontChild('name', { val: 'Calibri' }),
            fontChild('b', {}),
            fontChild('color', { rgb: 'FFFFFFFF' })
        ]));
        var fontInfoBold = appendFont(el('font', {}, [
            fontChild('sz', { val: '11' }),
            fontChild('name', { val: 'Calibri' }),
            fontChild('b', {})
        ]));
        var fontTitle = appendFont(el('font', {}, [
            fontChild('sz', { val: '13' }),
            fontChild('name', { val: 'Calibri' }),
            fontChild('b', {})
        ]));

        var fillNavy = appendFill(el('fill', {}, [
            el('patternFill', { patternType: 'solid' }, [
                el('fgColor', { rgb: 'FF' + navyBlue.replace('#', '') }),
                el('bgColor', { indexed: '64' })
            ])
        ]));
        var fillWhite = appendFill(el('fill', {}, [
            el('patternFill', { patternType: 'solid' }, [
                el('fgColor', { rgb: 'FFFFFFFF' }),
                el('bgColor', { indexed: '64' })
            ])
        ]));

        var thinBorderId = 1; // default thin border in template
        var styleSchool = appendXf(el('xf', {
            numFmtId: '0', fontId: '' + fontSchool, fillId: '' + fillWhite, borderId: '' + thinBorderId,
            applyFont: '1', applyFill: '1', applyBorder: '1', applyAlignment: '1', xfId: '0'
        }, [el('alignment', { horizontal: 'center', vertical: 'center' })]));
        var styleInfo = appendXf(el('xf', {
            numFmtId: '0', fontId: '' + fontInfoBold, fillId: '' + fillWhite, borderId: '' + thinBorderId,
            applyFont: '1', applyFill: '1', applyBorder: '1', applyAlignment: '1', xfId: '0'
        }, [el('alignment', { horizontal: 'center', vertical: 'center' })]));
        var styleSpacer = appendXf(el('xf', {
            numFmtId: '0', fontId: '0', fillId: '' + fillWhite, borderId: '' + thinBorderId,
            applyFont: '1', applyFill: '1', applyBorder: '1', applyAlignment: '1', xfId: '0'
        }, [el('alignment', { horizontal: 'center', vertical: 'center' })]));
        var styleHeader = appendXf(el('xf', {
            numFmtId: '0', fontId: '' + fontWhiteBold, fillId: '' + fillNavy, borderId: '' + thinBorderId,
            applyFont: '1', applyFill: '1', applyBorder: '1', applyAlignment: '1', xfId: '0'
        }, [el('alignment', { horizontal: 'center', vertical: 'center' })]));
        var styleDataCenter = appendXf(el('xf', {
            numFmtId: '0', fontId: '0', fillId: '' + fillWhite, borderId: '' + thinBorderId,
            applyFont: '1', applyFill: '1', applyBorder: '1', applyAlignment: '1', xfId: '0'
        }, [el('alignment', { horizontal: 'center', vertical: 'center' })]));
        var styleDataLeft = appendXf(el('xf', {
            numFmtId: '0', fontId: '0', fillId: '' + fillWhite, borderId: '' + thinBorderId,
            applyFont: '1', applyFill: '1', applyBorder: '1', applyAlignment: '1', xfId: '0'
        }, [el('alignment', { horizontal: 'left', vertical: 'center' })]));

        var sheetData = ws.getElementsByTagName('sheetData')[0];
        if (!sheetData) return;

        var rows = sheetData.getElementsByTagName('row');
        for (var r = rows.length - 1; r >= 0; r--) {
            var row = rows[r];
            var oldR = parseInt(row.getAttribute('r'), 10);
            if (oldR >= 1) {
                var newR = oldR + 3;
                row.setAttribute('r', '' + newR);
                var cells = row.getElementsByTagName('c');
                for (var ci = 0; ci < cells.length; ci++) {
                    var ref = cells[ci].getAttribute('r');
                    if (!ref) continue;
                    cells[ci].setAttribute('r', ref.replace(/\d+$/, '' + newR));
                }
            }
        }

        var maxCol = Math.max(1, numCols);
        var lastColRef = colName(maxCol);
        function inlineCell(col, row, text, styleId) {
            var c = el('c', { r: cellRef(col, row), t: 'inlineStr', s: '' + styleId });
            c.appendChild(el('is', {}, [el('t', {}, [styleDoc.createTextNode(text || '')])]));
            return c;
        }
        function createRow(rowNum, cells) {
            var row = el('row', { r: '' + rowNum });
            for (var i = 0; i < cells.length; i++) row.appendChild(cells[i]);
            return row;
        }

        var row1 = createRow(1, [inlineCell(1, 1, (schoolName || '').toUpperCase(), styleSchool)]);
        var infoLine = 'Class: ' + (className || '—') +
            ' | Section: ' + (sectionName || '—') +
            ' | Subject: ' + (subjectName || '—') +
            ' | Term: ' + (termName || '—') +
            ' | Year: ' + (academicYear || '—');
        var row2 = createRow(2, [inlineCell(1, 2, infoLine, styleInfo)]);
        var row3 = createRow(3, [inlineCell(1, 3, '', styleSpacer)]);

        sheetData.insertBefore(row3, sheetData.firstChild);
        sheetData.insertBefore(row2, sheetData.firstChild);
        sheetData.insertBefore(row1, sheetData.firstChild);

        var mergeCells = ws.getElementsByTagName('mergeCells')[0];
        if (!mergeCells) {
            mergeCells = el('mergeCells', { count: '0' });
            ws.documentElement.appendChild(mergeCells);
        }
        function addMerge(ref) {
            mergeCells.appendChild(el('mergeCell', { ref: ref }));
        }
        addMerge('A1:' + lastColRef + '1');
        addMerge('A2:' + lastColRef + '2');
        addMerge('A3:' + lastColRef + '3');
        mergeCells.setAttribute('count', '' + mergeCells.getElementsByTagName('mergeCell').length);

        var headerRowNumber = 4;
        var allRows = sheetData.getElementsByTagName('row');
        for (var rr = 0; rr < allRows.length; rr++) {
            var rowNo = parseInt(allRows[rr].getAttribute('r'), 10);
            var cells = allRows[rr].getElementsByTagName('c');
            for (var cc = 0; cc < cells.length; cc++) {
                if (rowNo === headerRowNumber) {
                    cells[cc].setAttribute('s', '' + styleHeader);
                } else if (rowNo > headerRowNumber) {
                    cells[cc].setAttribute('s', (cc === 1 || cc === 2) ? '' + styleDataLeft : '' + styleDataCenter);
                }
            }
        }

        var colsEl = ws.getElementsByTagName('cols')[0];
        if (!colsEl) {
            colsEl = el('cols');
            ws.documentElement.insertBefore(colsEl, ws.documentElement.firstChild);
        }
        while (colsEl.firstChild) colsEl.removeChild(colsEl.firstChild);
        for (var i = 1; i <= maxCol; i++) {
            var width = 12;
            if (i === 1) width = 7;
            if (i === 2) width = 35;
            if (i === 3) width = 14;
            colsEl.appendChild(el('col', { min: '' + i, max: '' + i, width: '' + width, customWidth: '1' }));
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

            var borderGrey = '#dddddd';
            doc.styles.tableHeader = { bold: true, fontSize: 10, color: '#FFFFFF', fillColor: navyBlue, alignment: 'center' };
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

            var numCols = tableElem.table.body[0] ? tableElem.table.body[0].length : 4;
            // Precision widths: S/N 4%, Name 22%, ADM_No 12%, rest equally.
            var fixedSn = 4, fixedName = 22, fixedAdm = 12;
            var otherCols = Math.max(1, numCols - 3);
            var otherWidth = (100 - fixedSn - fixedName - fixedAdm) / otherCols;
            var widths = [];
            for (var c = 0; c < numCols; c++) {
                if (c === 0) widths.push(fixedSn + '%');
                else if (c === 1) widths.push(fixedName + '%');
                else if (c === 2) widths.push(fixedAdm + '%');
                else widths.push(otherWidth + '%');
            }
            tableElem.table.widths = widths;

            tableElem.layout = {
                hLineWidth: function() { return 0.5; },
                vLineWidth: function() { return 0.5; },
                hLineColor: function() { return borderGrey; },
                vLineColor: function() { return borderGrey; }
            };

            var body = tableElem.table.body;
            var headerRows = tableElem.table.headerRows || 1;
            var lastColIndex = numCols - 1;
            for (var r = 0; r < body.length; r++) {
                var row = body[r];
                var isHeader = r < headerRows;
                var rowStyle = isHeader ? 'tableHeader' : (r % 2 === 0 ? 'tableBodyEven' : 'tableBodyOdd');
                var cellPadding = isHeader ? [6, 7, 6, 7] : [6, 7, 6, 7];
                for (var c = 0; c < row.length; c++) {
                    var cell = row[c];
                    var text = typeof cell === 'object' && cell !== null && cell.text !== undefined ? cell.text : String(cell);
                    var isTotal = (!isHeader && c === lastColIndex);
                    var headerAlign = (c === 1) ? 'left' : 'center';
                    row[c] = {
                        text: text,
                        style: rowStyle,
                        alignment: isHeader ? headerAlign : ((c === 1 || c === 2) ? 'left' : 'center'),
                        bold: isHeader ? true : (isTotal ? true : undefined),
                        fontSize: isHeader ? 10 : undefined,
                        margin: cellPadding
                    };
                }
            }

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
                {
                    table: {
                        body: [[
                            {
                                table: {
                                    body: [
                                        [{ text: 'Class: ' + (className || '—'), fontSize: 10, alignment: 'left' }],
                                        [{ text: 'Section: ' + (sectionName || '—'), fontSize: 10, alignment: 'left' }],
                                        [{ text: 'Subject: ' + (subjectName || '—'), fontSize: 10, alignment: 'left' }]
                                    ],
                                    widths: ['*']
                                },
                                layout: 'noBorders',
                                margin: [0, 0, 10, 0]
                            },
                            {
                                text: exportLabel,
                                fontSize: 13,
                                bold: true,
                                alignment: 'center',
                                margin: [0, 14, 0, 0]
                            },
                            {
                                table: {
                                    body: [
                                        [{ text: 'Term: ' + (termName || '—'), fontSize: 10, alignment: 'right' }],
                                        [{ text: 'Academic Year: ' + (academicYear || '—'), fontSize: 10, alignment: 'right' }],
                                        [{ text: 'Date: ' + docDate, fontSize: 10, alignment: 'right' }]
                                    ],
                                    widths: ['*']
                                },
                                layout: 'noBorders',
                                margin: [10, 0, 0, 0]
                            }
                        ]],
                        widths: ['33%', '34%', '33%'],
                        heights: 54
                    },
                    layout: 'noBorders',
                    margin: [0, 2, 0, 16]
                }
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

    var colCount = $table.find('thead th').length;
    var exportCols = [];
    var caTargets = [];
    for (var i = 0; i < colCount; i++) {
        // Always export S/N, Name, ADM_No (first 3 columns).
        if (i === 0 || i === 1 || i === 2) {
            exportCols.push(i);
            continue;
        }

        // Export only assessment columns (headers marked with `.export-col`)
        // plus Total column (headers marked with `.col-total`).
        var $th = $table.find('thead th').eq(i);
        if ($th.hasClass('export-col') || $th.hasClass('col-total')) {
            exportCols.push(i);
            caTargets.push(i);
        }
    }

    // DataTables UI: match the project standard.
    var dt = $table.DataTable({
        autoWidth: false,
        ordering: false,
        dom: '<"datatable-header"fBl><"datatable-scroll"t><"datatable-footer"ip>',
        language: {
            search: '<span>Filter:</span> _INPUT_',
            searchPlaceholder: 'Type to filter...',
            lengthMenu: '<span>Show:</span> _MENU_',
            paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' }
        },
        buttons: {
            dom: { button: { className: 'btn btn-light' } },
            buttons: [
                {
                    extend: 'excelHtml5',
                    className: 'btn btn-light',
                    exportOptions: {
                        columns: exportCols,
                        format: {
                            body: function(data, rowIdx, colIdx, node) {
                                var $ = window.jQuery || window.$;
                                if (node) {
                                    if ($) {
                                        var $cell = $(node);
                                        var $liveEl = $cell.find('input,select').first();
                                        if ($liveEl && $liveEl.length) {
                                            var v = $liveEl.val();
                                            if (v === null || v === undefined || v === '') v = 0;
                                            return v;
                                        }
                                    } else if (node.querySelector) {
                                        var el = node.querySelector('input,select');
                                        if (el) {
                                            var v2 = el.value;
                                            if (v2 === null || v2 === undefined || v2 === '') v2 = 0;
                                            return v2;
                                        }
                                    }
                                }
                                return data;
                            }
                        }
                    },
                    filename: excelFilename,
                    title: null,
                    sheetName: 'Mark Sheet',
                    customize: function(xlsx) { excelPremiumCustomize(xlsx, colCount); }
                },
                {
                    extend: 'pdfHtml5',
                    className: 'btn btn-light',
                    exportOptions: {
                        columns: exportCols,
                        // Use live values from <input>/<select> so the PDF matches the typed grid.
                        format: {
                            body: function(data, rowIdx, colIdx, node) {
                                var $ = window.jQuery || window.$;
                                if (node) {
                                    if ($) {
                                        var $cell = $(node);
                                        var $liveEl = $cell.find('input,select').first();
                                        if ($liveEl && $liveEl.length) {
                                            var v = $liveEl.val();
                                            if (v === null || v === undefined || v === '') v = 0;
                                            return v;
                                        }
                                    } else if (node.querySelector) {
                                        var el = node.querySelector('input,select');
                                        if (el) {
                                            var v2 = el.value;
                                            if (v2 === null || v2 === undefined || v2 === '') v2 = 0;
                                            return v2;
                                        }
                                    }
                                }
                                return data;
                            }
                        }
                    },
                    filename: exportFilename,
                    orientation: pdfCommon.orientation,
                    pageSize: pdfCommon.pageSize,
                    title: pdfCommon.title,
                    customize: pdfCommon.customize
                },
                { extend: 'colvis', text: '<i class="icon-three-bars"></i> Visibility', className: 'btn bg-blue btn-icon dropdown-toggle' }
            ]
        },
        columnDefs: [
            {
                targets: caTargets,
                render: function(data, type, row, meta) {
                    // For export (excel/pdf) we need the current input value, not the initial HTML.
                    if (type === 'export') {
                        var cell = meta && meta.settings && meta.row !== undefined
                            ? meta.settings.aoData[meta.row].anCells[meta.col]
                            : null;
                        if (cell) {
                            var inp = cell.querySelector('input');
                            if (inp) return inp.value;
                        }
                        return extractInputValue(data);
                    }
                    return data;
                }
            }
        ]
    });

    // Recalculate totals after DataTables draw/redraw.
    dt.on('draw', function() {
        $table.find('tbody tr').each(function() {
            recalcRowTotal($(this));
        });
    });

    // Standard select2 styling for the "Show: _MENU_" control.
    $('.dataTables_length select').select2 && $('.dataTables_length select').select2({ minimumResultsForSearch: Infinity, width: 'auto' });
})();
</script>
@endsection
