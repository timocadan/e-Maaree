@extends('layouts.master')
@section('page_title', 'Manage Users')
@section('content')

    <div class="card" id="users-list-card"
         data-school-name="{{ e(Qs::getSystemName()) }}"
         data-school-address="{{ e(Qs::getSetting('address') ?? '') }}"
         data-school-phone="{{ e(Qs::getSetting('phone') ?? '') }}"
         data-school-phone2="{{ e(Qs::getSetting('phone2') ?? '') }}"
         data-school-email="{{ e(Qs::getSetting('system_email') ?? '') }}">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Users</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#new-user" class="nav-link active" data-toggle="tab">Create New User</a></li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Manage Users</a>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach($user_types as $ut)
                            <a href="#ut-{{ Qs::hash($ut->id) }}" class="dropdown-item" data-toggle="tab">{{ $ut->name }}s</a>
                        @endforeach
                    </div>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="new-user">
                    <div class="row justify-content-center mt-3">
                        <div class="col-lg-10 pl-lg-4" style="border-left: 3px solid #D32F2F;">
                            <form method="post" enctype="multipart/form-data" class="ajax-store" action="{{ route('users.store') }}" data-fouc>
                                @csrf
                                {{-- Row 1: User Type, Full Name, Address --}}
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="user_type">Select User: <span class="text-danger">*</span></label>
                                            <select required data-placeholder="Select User" class="form-control select" name="user_type" id="user_type">
                                                @foreach($user_types as $ut)
                                                    <option value="{{ Qs::hash($ut->id) }}">{{ $ut->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Full Name: <span class="text-danger">*</span></label>
                                            <input value="{{ old('name') }}" required type="text" name="name" placeholder="Full Name" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Address: <span class="text-danger">*</span></label>
                                            <input value="{{ old('address') }}" class="form-control" placeholder="Address" name="address" type="text" required>
                                        </div>
                                    </div>
                                </div>

                                {{-- Row 2: Email, Phone, Gender --}}
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Email address:</label>
                                            <input value="{{ old('email') }}" type="email" name="email" class="form-control" placeholder="your@email.com">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Phone:</label>
                                            <input value="{{ old('phone') }}" type="text" name="phone" class="form-control" placeholder="+2341234567">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="gender">Gender: <span class="text-danger">*</span></label>
                                            <select class="select form-control" id="gender" name="gender" required data-fouc data-placeholder="Choose..">
                                                <option value=""></option>
                                                <option {{ (old('gender') == 'Male') ? 'selected' : '' }} value="Male">Male</option>
                                                <option {{ (old('gender') == 'Female') ? 'selected' : '' }} value="Female">Female</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Row 3: Date of Employment, Password --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date of Employment:</label>
                                            <input autocomplete="off" name="emp_date" value="{{ old('emp_date') }}" type="text" class="form-control date-pick" placeholder="Select Date...">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password">Password:</label>
                                            <input id="password" type="password" name="password" class="form-control" placeholder="Leave blank to use default">
                                        </div>
                                    </div>
                                </div>

                                {{-- Hidden/comment-out: Username (auto-generated), Telephone, Nationality, State, LGA, Blood Group, Passport --}}
                                {{--
                                <div class="form-group">
                                    <label>Username:</label>
                                    <input value="{{ old('username') }}" type="text" name="username" class="form-control" placeholder="Username">
                                </div>
                                <div class="form-group">
                                    <label>Telephone:</label>
                                    <input value="{{ old('phone2') }}" type="text" name="phone2" class="form-control" placeholder="+2341234567">
                                </div>
                                Nationality (nal_id), State (state_id), LGA (lga_id), Blood Group (bg_id), Upload Passport Photo (photo) - hidden for simplified staff registration.
                                --}}

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-danger btn-lg">
                                        <i class="icon-plus2 mr-2"></i> Create User
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @foreach($user_types as $ut)
                    <div class="tab-pane fade" id="ut-{{Qs::hash($ut->id)}}">                         <table class="table datatable-user-list table-users-list">
                            <colgroup>
                                <col style="width: 5%;">
                                <col style="width: 24%;">
                                <col style="width: 12%;">
                                <col style="width: 18%;">
                                <col style="width: 26%;">
                                <col style="width: 15%;">
                            </colgroup>
                            <thead>
                            <tr>
                                <th>S/N</th>
                                {{-- <th>Photo</th> --}}
                                <th>Full Name</th>
                                <th>User Type</th>
                                {{-- <th>Username</th> --}}
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($users->where('user_type', $ut->title) as $u)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    {{-- <td><img class="rounded-circle" style="height: 40px; width: 40px;" src="{{ $u->photo }}" alt="photo"></td> --}}
                                    <td>{{ $u->name }}</td>
                                    <td>{{ ucfirst($u->user_type) }}</td>
                                    {{-- <td>{{ $u->username }}</td> --}}
                                    <td>{{ $u->phone }}</td>
                                    <td>{{ $u->email }}</td>
                                    <td class="text-center">
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                    <i class="icon-menu9"></i>
                                                </a>

                                                <div class="dropdown-menu dropdown-menu-left">
                                                    {{--View Profile--}}
                                                    <a href="{{ route('users.show', Qs::hash($u->id)) }}" class="dropdown-item"><i class="icon-eye"></i> View Profile</a>
                                                    {{--Edit--}}
                                                    <a href="{{ route('users.edit', Qs::hash($u->id)) }}" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                                @if(Qs::userIsSuperAdmin())

                                                        <a href="{{ route('users.reset_pass', Qs::hash($u->id)) }}" class="dropdown-item"><i class="icon-lock"></i> Reset password</a>
                                                        {{--Delete--}}
                                                        <a id="{{ Qs::hash($u->id) }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item"><i class="icon-trash"></i> Delete</a>
                                                        <form method="post" id="item-delete-{{ Qs::hash($u->id) }}" action="{{ route('users.destroy', Qs::hash($u->id)) }}" class="hidden">@csrf @method('delete')</form>
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

    {{--Manage Users Ends--}}

@endsection

@section('scripts')
<script>
(function() {
    if (!$().DataTable) return;
    var $card = $('#users-list-card');
    var schoolName = $card.data('school-name') || 'User List';
    var address = $card.data('school-address') || '';
    var phone = $card.data('school-phone') || '';
    var phone2 = $card.data('school-phone2') || '';
    var email = $card.data('school-email') || '';
    var telLine = [phone, phone2].filter(Boolean).join(', ');
    var addressBarText = [address, telLine ? ('Tel: ' + telLine) : '', email].filter(Boolean).join(' | ');
    var docDate = new Date().toLocaleDateString();
    var exportLabel = schoolName + ' - User List';
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

        $(wb).find('sheets sheet').attr('name', 'User List');

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
                ? ['7%', '28%', '15%', '20%', '30%']
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

            var metaLeft = '';
            var metaRight = 'Date: ' + docDate;
            var metaTable = {
                table: {
                    body: [[
                        { text: metaLeft, fontSize: 10, alignment: 'left' },
                        { text: 'OFFICIAL USER LIST', fontSize: 13, bold: true, alignment: 'center' },
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

    $('.datatable-user-list').DataTable({
        autoWidth: false,
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
                    exportOptions: { columns: [0, 1, 2, 3, 4] },
                    filename: exportFilename,
                    title: exportLabel,
                    sheetName: 'User List',
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

    $('.dataTables_length select').select2 && $('.dataTables_length select').select2({ minimumResultsForSearch: Infinity, width: 'auto' });
})();
</script>
@endsection
