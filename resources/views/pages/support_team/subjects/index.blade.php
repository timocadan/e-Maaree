@extends('layouts.master')
@section('page_title', 'Manage Subjects')
@section('content')

    <div class="card" id="subjects-list-card"
         data-school-name="{{ e(Qs::getSystemName()) }}"
         data-school-address="{{ e(Qs::getSetting('address') ?? '') }}"
         data-school-phone="{{ e(Qs::getSetting('phone') ?? '') }}"
         data-school-phone2="{{ e(Qs::getSetting('phone2') ?? '') }}"
         data-school-email="{{ e(Qs::getSetting('system_email') ?? '') }}"
         data-class-name="{{ $selected_class ? e($selected_class->name) : '' }}"
         data-session="{{ e(Qs::getCurrentSession()) }}">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Subjects</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#new-subject" class="nav-link {{ $selected_class ? '' : 'active' }}" data-toggle="tab">Add Subject</a></li>
                <li class="nav-item"><a href="#list-subjects" class="nav-link {{ $selected_class ? 'active' : '' }}" data-toggle="tab">Manage Subjects</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade {{ $selected_class ? '' : 'show active' }}" id="new-subject">
                    <div class="row">
                        <div class="col-md-6">
                            <form class="ajax-store" method="post" action="{{ route('subjects.store') }}">
                                @csrf
                                <div class="form-group row">
                                    <label for="name" class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <input id="name" name="name" value="{{ old('name') }}" required type="text" class="form-control" placeholder="Name of subject">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="slug" class="col-lg-3 col-form-label font-weight-semibold">Short Name <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <input id="slug" required name="slug" value="{{ old('slug') }}" type="text" class="form-control" placeholder="Eg. B.Eng">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="my_class_id" class="col-lg-3 col-form-label font-weight-semibold">Select Class <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <select required data-placeholder="Select Class" class="form-control select" name="my_class_id" id="my_class_id">
                                            <option value=""></option>
                                            @foreach($my_classes as $c)
                                                <option {{ old('my_class_id') == $c->id ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="teacher_id" class="col-lg-3 col-form-label font-weight-semibold">Teacher <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <select required data-placeholder="Select Teacher" class="form-control select-search" name="teacher_id" id="teacher_id">
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

                <div class="tab-pane fade {{ $selected_class ? 'show active' : '' }}" id="list-subjects">
                    {{-- Toggle: Select class --}}
                    <div class="card card-body border mb-3">
                        <a href="#class-list-collapse" class="d-flex align-items-center text-body font-weight-semibold" data-toggle="collapse" aria-expanded="{{ $selected_class ? 'false' : 'true' }}" aria-controls="class-list-collapse">
                            <i class="icon-arrow-down8 mr-2 collapse-icon"></i>
                            <span>Select class</span>
                            @if($selected_class)
                                <span class="text-muted ml-2">({{ $selected_class->name }})</span>
                            @endif
                        </a>
                        <div class="collapse {{ $selected_class ? '' : 'show' }}" id="class-list-collapse">
                            <div class="pt-2 class-selector-btns">
                                @foreach($my_classes as $c)
                                    <a href="{{ route('subjects.index', ['class_id' => $c->id]) }}" class="btn btn-sm btn-outline-secondary mb-1">{{ $c->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if($selected_class && $subjects)
                        <table class="table table-bordered datatable-subject-list subjects-table">
                            <thead>
                            <tr>
                                <th class="text-center no-export col-sn">S/N</th>
                                <th>Name</th>
                                <th>Short Name</th>
                                <th>Teacher</th>
                                <th class="text-center no-export col-action">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($subjects as $s)
                                <tr>
                                    <td class="col-sn">{{ ($subjects->currentPage() - 1) * $subjects->perPage() + $loop->iteration }}</td>
                                    <td>{{ $s->name }}</td>
                                    <td>{{ $s->slug }}</td>
                                    <td>{{ $s->teacher->name }}</td>
                                    <td class="col-action text-center align-middle">
                                        @if(Qs::userIsTeamSA() || Qs::userIsSuperAdmin())
                                            <div class="list-icons">
                                                <div class="dropdown">
                                                    <a href="#" class="list-icons-item action-trigger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="icon-menu9"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        @if(Qs::userIsTeamSA())
                                                            <a href="{{ route('subjects.edit', Qs::hash($s->id)) }}" class="dropdown-item"><i class="icon-pencil mr-2"></i> Edit</a>
                                                        @endif
                                                        @if(Qs::userIsSuperAdmin())
                                                            <form action="{{ route('subjects.destroy', Qs::hash($s->id)) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this subject?');" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger"><i class="icon-trash mr-2"></i> Delete</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="mt-2">
                            {{ $subjects->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    <style>
        .class-selector-btns .btn { color: #000 !important; border-color: #adb5bd; }
        .class-selector-btns .btn:hover { color: #000 !important; border-color: #6c757d; background-color: #f8f9fa; }
        .subjects-table thead th { background-color: #002147 !important; color: #fff !important; border-color: #002147; font-weight: 600; padding: 10px 12px; }
        .subjects-table tbody td { vertical-align: middle; }
        .subjects-table .col-sn { min-width: 40px; width: 40px; text-align: center; }
        .subjects-table .col-action { min-width: 70px; width: 70px; text-align: center; }
        .subjects-table .col-action .action-trigger { background: none; border: none; color: #8b6914; padding: 4px 8px; }
        .subjects-table .col-action .action-trigger:hover { color: #6b5010; background: transparent; }
        .subjects-table .col-action .dropdown-menu { min-width: 120px; padding: 0.25rem 0; }
        .subjects-table .col-action .dropdown-item { padding: 0.4rem 1rem; font-size: 0.875rem; }
        .subjects-table .col-action .dropdown-item.text-danger:hover { background-color: rgba(211, 47, 47, 0.08); }
    </style>

    {{--subject List Ends--}}

@endsection

@section('scripts')
<script>
(function() {
    if (!$().DataTable) return;
    var $card = $('#subjects-list-card');
    var schoolName = $card.data('school-name') || 'Subject List';
    var className = $card.data('class-name') || '';
    var sessionName = $card.data('session') || '';
    var address = $card.data('school-address') || '';
    var phone = $card.data('school-phone') || '';
    var phone2 = $card.data('school-phone2') || '';
    var email = $card.data('school-email') || '';
    var telLine = [phone, phone2].filter(Boolean).join(', ');
    var addressBarText = [address, telLine ? ('Tel: ' + telLine) : '', email].filter(Boolean).join(' | ');
    var docDate = new Date().toLocaleDateString();
    var exportLabel = className ? (schoolName + ' - ' + className + ' Subject List') : (schoolName + ' - Subject List');
    var exportFilename = exportLabel.replace(/[/\\:*?"<>|]/g, '-');
    var navyBlue = '#002147';

    function excelSubjectCustomize(xlsx) {
        try {
            var $ = window.jQuery || window.$;
            if (!$ || !xlsx.xl) return;
            var wb = xlsx.xl['workbook.xml'];
            var ws = xlsx.xl.worksheets['sheet1.xml'];
            if (!wb || !ws) return;
            if ($(wb).find && $(wb).find('sheets sheet').length) $(wb).find('sheets sheet').attr('name', 'Subject List');
            var colsEl = ws.getElementsByTagName('cols')[0];
            if (colsEl) {
                var cols = colsEl.getElementsByTagName('col');
                if (cols.length > 0) cols[0].setAttribute('width', '8');
                if (cols.length > 1) cols[1].setAttribute('width', '35');
                if (cols.length > 2) cols[2].setAttribute('width', '15');
                if (cols.length > 3) cols[3].setAttribute('width', '35');
            }
        } catch (e) {
            if (typeof console !== 'undefined' && console.warn) console.warn('Excel customize skipped:', e);
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
                if (doc.content[i].table) { tableElem = doc.content[i]; break; }
            }
            if (!tableElem || !tableElem.table) return;

            var numCols = tableElem.table.body[0] ? tableElem.table.body[0].length : 4;
            tableElem.table.widths = numCols === 4 ? ['7%', '32%', '18%', '43%'] : ['*'];
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
                { text: 'OFFICIAL SUBJECT LIST', fontSize: 14, bold: true, alignment: 'center', margin: [0, 4, 0, 8] },
                { table: { body: [[{ text: 'Class: ' + (className || '—'), fontSize: 10, alignment: 'left' }, { text: 'Academic Year: ' + (sessionName || '—'), fontSize: 10, alignment: 'right' }]], widths: ['*', '*'], heights: 22 }, layout: 'noBorders', margin: [0, 0, 0, 16] }
            ];
            doc.content = customHeader.concat([tableElem]);
            doc.footer = function(currentPage, pageCount) {
                return { text: 'Generated by e-maaree | Page ' + currentPage + ' of ' + pageCount, alignment: 'center', fontSize: 8, color: '#999999', margin: [0, 8, 0, 0] };
            };
        }
    };

    if ($('.datatable-subject-list').length) {
        $('.datatable-subject-list').DataTable({
            autoWidth: false,
            dom: '<"datatable-header"fBl><"datatable-scroll"t><"datatable-footer"ip>',
            language: { search: '<span>Filter:</span> _INPUT_', searchPlaceholder: 'Type to filter...', lengthMenu: '<span>Show:</span> _MENU_', paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' } },
            buttons: {
                dom: { button: { className: 'btn btn-light' } },
                buttons: [
                    { extend: 'excelHtml5', className: 'btn btn-light', exportOptions: { columns: [0, 1, 2, 3] }, filename: exportFilename, title: exportLabel, sheetName: 'Subject List', customize: function(xlsx) { excelSubjectCustomize(xlsx); } },
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
