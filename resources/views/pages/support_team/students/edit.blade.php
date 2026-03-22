@extends('layouts.master')
@section('page_title', 'Edit Student')
@section('content')

        <style>
            #ajax-reg-edit .actions, .steps-validation.ajax-update .actions { margin-top: 1.75rem !important; padding-top: 0.5rem; }
            #ajax-reg-edit .actions a[href="#next"], .steps-validation.ajax-update .actions a[href="#next"] { background-color: #fff !important; color: #333 !important; border: 1px solid #ddd !important; }
            #ajax-reg-edit .actions a[href="#finish"], .steps-validation.ajax-update .actions a[href="#finish"] { background-color: #D32F2F !important; color: #fff !important; border: 1px solid #D32F2F !important; font-weight: 500 !important; }
            #ajax-reg-edit .actions a[href="#finish"]:hover { background-color: #b71c1c !important; border-color: #b71c1c !important; color: #fff !important; }
            #ajax-reg-edit .personal-data-row-2 { align-items: flex-end; }
            #ajax-reg-edit .personal-data-row-2 .form-group { display: flex; flex-direction: column; width: 100%; }
            #ajax-reg-edit .personal-data-row-2 .form-group label { flex-shrink: 0; margin-bottom: 0.35rem; }
            #ajax-reg-edit .personal-data-row-2 .form-control { height: 2.5rem !important; min-height: 2.5rem !important; padding: 0.47rem 0.75rem; line-height: 1.5; }
            #parent-suggestions-edit { top: 100%; left: 0; margin-top: 4px; background: #fff; border: 1px solid #dee2e6; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 4px 0; }
            #parent-suggestions-edit .parent-suggestion { color: #212529 !important; padding: 0.5rem 0.75rem; border: none; border-radius: 4px; margin: 0 4px; font-size: 0.9rem; }
            #parent-suggestions-edit .parent-suggestion:hover { background-color: #f1f3f5 !important; color: #212529 !important; }
        </style>

        <div class="card">
            <div class="card-header bg-white header-elements-inline">
                <h6 id="ajax-title" class="card-title">Edit record of {{ $sr->user->name }}</h6>
                {!! Qs::getPanelOptions() !!}
            </div>

            <form method="post" id="ajax-reg-edit" enctype="multipart/form-data" class="wizard-form steps-validation ajax-update" data-reload="#ajax-title" action="{{ route('students.update', Qs::hash($sr->id)) }}" data-fouc>
                @csrf @method('PUT')
                <h6>Personal data</h6>
                <fieldset>
                    {{-- Row 1: Full Name, Address --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium">Full Name <span class="text-danger">*</span></label>
                                <input value="{{ old('name', $sr->user->name) }}" required type="text" name="name" placeholder="Full Name" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium">Address <span class="text-danger">*</span></label>
                                <input value="{{ old('address', $sr->user->address) }}" class="form-control" placeholder="Address" name="address" type="text" required>
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: Gender, Phone, Date of Birth (col-md-4 each) --}}
                    <div class="row mt-3 personal-data-row-2">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium" for="gender">Gender <span class="text-danger">*</span></label>
                                <select class="select form-control" id="gender" name="gender" required data-fouc data-placeholder="Choose...">
                                    <option value=""></option>
                                    <option {{ old('gender', $sr->user->gender) == 'Male' ? 'selected' : '' }} value="Male">Male</option>
                                    <option {{ old('gender', $sr->user->gender) == 'Female' ? 'selected' : '' }} value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium">Phone</label>
                                <input value="{{ old('phone', $sr->user->phone) }}" type="text" name="phone" class="form-control" placeholder="Phone number">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium">Date of Birth</label>
                                <input name="dob" value="{{ old('dob', $sr->user->dob) }}" type="text" class="form-control date-pick" placeholder="Select Date...">
                            </div>
                        </div>
                    </div>

                    {{-- Smart Parent: Name & Phone (pre-filled from current parent) --}}
                    <div class="row mt-3 personal-data-row-2" id="parent-fields-row-edit">
                        <div class="col-md-6">
                            <div class="form-group mb-0 position-relative">
                                <label class="font-weight-medium">Parent Name <span class="text-danger">*</span></label>
                                <input value="{{ old('parent_name', $sr->my_parent->name ?? '') }}" required type="text" id="parent_name_edit" name="parent_name" placeholder="Parent / Guardian full name" class="form-control" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0 position-relative">
                                <label class="font-weight-medium">Parent Phone <span class="text-danger">*</span></label>
                                <input value="{{ old('parent_phone', $sr->my_parent->phone ?? '') }}" required type="text" id="parent_phone_edit" name="parent_phone" placeholder="Parent phone number" class="form-control" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-12 position-relative" style="min-height: 0;">
                            <div id="parent-suggestions-edit" class="position-absolute" style="display: none; z-index: 1050; max-height: 220px; overflow-y: auto; min-width: 320px;"></div>
                        </div>
                    </div>

                    {{-- Hidden for MVP: Email, Telephone, Nationality, State, LGA, Blood Group, Passport Photo --}}
                    {{--
                    <div class="row">
                        <div class="col-md-3"><div class="form-group"><label>Email address:</label><input value="{{ $sr->user->email }}" type="email" name="email" class="form-control"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Telephone:</label><input value="{{ $sr->user->phone2 }}" type="text" name="phone2" class="form-control"></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3"><label for="nal_id">Nationality:</label><select name="nal_id" id="nal_id" class="select-search form-control">...</select></div>
                        <div class="col-md-3"><label for="state_id">State:</label><select name="state_id" id="state_id">...</select></div>
                        <div class="col-md-3"><label for="lga_id">LGA:</label><select name="lga_id" id="lga_id">...</select></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><label for="bg_id">Blood Group:</label><select name="bg_id" id="bg_id">...</select></div>
                        <div class="col-md-6"><label>Upload Passport Photo:</label><input type="file" name="photo" class="form-input-styled"></div>
                    </div>
                    --}}
                </fieldset>

                <h6>Student Data</h6>
                <fieldset>
                    {{-- Row 1: Class, Section --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium" for="my_class_id">Class <span class="text-danger">*</span></label>
                                <select onchange="getClassSections(this.value)" name="my_class_id" required id="my_class_id" class="form-control select-search" data-placeholder="Select Class">
                                    <option value=""></option>
                                    @foreach($my_classes as $c)
                                        <option {{ old('my_class_id', $sr->my_class_id) == $c->id ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium" for="section_id">Section <span class="text-danger">*</span></label>
                                <select name="section_id" required id="section_id" class="form-control select" data-placeholder="Select Section">
                                    <option value="{{ $sr->section_id }}">{{ $sr->section->name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: Year Admitted, Admission Number (Parent from Step 1) --}}
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium" for="year_admitted">Year Admitted <span class="text-danger">*</span></label>
                                <select name="year_admitted" data-placeholder="Choose..." id="year_admitted" class="select-search form-control">
                                    <option value=""></option>
                                    @for($y=date('Y', strtotime('- 10 years')); $y<=date('Y'); $y++)
                                        <option {{ old('year_admitted', $sr->year_admitted) == $y ? 'selected' : '' }} value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium">Admission Number</label>
                                <input type="text" name="adm_no" placeholder="Student ID (optional)" class="form-control" value="{{ old('adm_no', $sr->adm_no) }}">
                            </div>
                        </div>
                    </div>

                    {{-- Hidden for MVP: Dormitory, Room No, Sport House --}}
                    {{--
                    <div class="row mt-3">
                        <div class="col-md-4"><label for="dorm_id">Dormitory:</label><select name="dorm_id" id="dorm_id">...</select></div>
                        <div class="col-md-4"><div class="form-group"><label>Dormitory Room No:</label><input name="dorm_room_no" class="form-control" value="{{ $sr->dorm_room_no }}"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Sport House:</label><input name="house" class="form-control"></div></div>
                    </div>
                    --}}
                </fieldset>
            </form>
        </div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wizard button labels (Next / Submit visible, red Submit)
    setTimeout(function() {
        var $wizard = $('#ajax-reg-edit');
        if ($wizard.length && $wizard.find('.actions').length) {
            var $links = $wizard.find('.actions a');
            $links.each(function() {
                var $a = $(this);
                var text = ($a.text() || '').trim();
                if (text === '' || ($a.find('i').length && text.length < 2)) {
                    if ($a.attr('href') === '#next' || $a.hasClass('next')) {
                        $a.html('Next');
                    } else if ($a.attr('href') === '#finish' || $a.hasClass('finish')) {
                        $a.html('Submit');
                    }
                }
            });
        }
    }, 400);

    var searchUrl = '{{ route("students.search_parents") }}';
    var $parentName = $('#parent_name_edit');
    var $parentPhone = $('#parent_phone_edit');
    var $suggestions = $('#parent-suggestions-edit');
    var debounceTimer = null;

    function searchParents(q) {
        q = (q || '').trim();
        if (q.length < 2) { $suggestions.hide().empty(); return; }
        $.get(searchUrl, { q: q }, function(data) {
            if (!data || data.length === 0) { $suggestions.hide().empty(); return; }
            var html = '';
            data.forEach(function(p) {
                var name = (p.name || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                var phone = (p.phone || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                html += '<a href="#" class="parent-suggestion d-block text-decoration-none" data-name="' + name + '" data-phone="' + phone + '">' + name + ' - ' + phone + '</a>';
            });
            $suggestions.html(html).show();
        }).fail(function() { $suggestions.hide().empty(); });
    }

    function onParentInput() {
        var q = $parentName.val() || $parentPhone.val();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() { searchParents(q); }, 300);
    }

    $parentName.add($parentPhone).on('input', onParentInput).on('focus', onParentInput);

    $(document).on('click', '#parent-suggestions-edit .parent-suggestion', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $parentName.val($(this).data('name') || '');
        $parentPhone.val($(this).data('phone') || '');
        $suggestions.hide().empty();
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#parent-fields-row-edit').length) $suggestions.hide().empty();
    });
});
</script>
@endsection
