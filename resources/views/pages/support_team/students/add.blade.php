@extends('layouts.master')
@section('page_title', 'Admit Student')
@section('content')
        <style>
            /* Space between form fields and Previous/Next buttons */
            #ajax-reg .actions,
            .steps-validation .actions,
            #ajax-reg + .actions {
                margin-top: 1.75rem !important;
                padding-top: 0.5rem;
            }
            /* Next button: light style */
            #ajax-reg .actions a[href="#next"],
            .steps-validation .actions a[href="#next"],
            .wizard > .actions a[href="#next"] {
                background-color: #fff !important;
                color: #333 !important;
                border: 1px solid #ddd !important;
            }
            #ajax-reg .actions a[href="#next"]:hover,
            .wizard > .actions a[href="#next"]:hover {
                background-color: #f5f5f5 !important;
                color: #333 !important;
                border-color: #ccc !important;
            }
            /* Submit Form button (Step 2 only): brand red, clear label */
            #ajax-reg .actions a[href="#finish"],
            .steps-validation .actions a[href="#finish"],
            .wizard > .actions a[href="#finish"] {
                background-color: #D32F2F !important;
                color: #fff !important;
                border: 1px solid #D32F2F !important;
                font-weight: 500 !important;
            }
            #ajax-reg .actions a[href="#finish"]:hover,
            .wizard > .actions a[href="#finish"]:hover {
                background-color: #b71c1c !important;
                border-color: #b71c1c !important;
                color: #fff !important;
            }
            /* Row 2: align Gender, Phone, DOB on same baseline with equal-height boxes */
            #ajax-reg .personal-data-row-2 {
                align-items: flex-end;
            }
            #ajax-reg .personal-data-row-2 .form-group {
                display: flex;
                flex-direction: column;
                width: 100%;
            }
            #ajax-reg .personal-data-row-2 .form-group label {
                flex-shrink: 0;
                margin-bottom: 0.35rem;
            }
            #ajax-reg .personal-data-row-2 .form-control {
                height: 2.5rem !important;
                min-height: 2.5rem !important;
                padding: 0.47rem 0.75rem;
                line-height: 1.5;
            }
            #ajax-reg .personal-data-row-2 .select2-container .select2-selection--single {
                height: 2.5rem !important;
                min-height: 2.5rem !important;
            }
            #ajax-reg .personal-data-row-2 .select2-container .select2-selection__rendered {
                line-height: 2.2rem !important;
            }
        </style>
        <div class="card">
            <div class="card-header bg-white header-elements-inline">
                <h6 class="card-title">Please fill The form Below To Admit A New Student</h6>

                {!! Qs::getPanelOptions() !!}
            </div>

            <form id="ajax-reg" method="post" enctype="multipart/form-data" class="wizard-form steps-validation" action="{{ route('students.store') }}" data-fouc>
               @csrf
                <h6>Personal data</h6>
                <fieldset>
                    {{-- Row 1: Full Name, Address (2 columns) --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium">Full Name <span class="text-danger">*</span></label>
                                <input value="{{ old('name') }}" required type="text" name="name" placeholder="Full Name" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium">Address <span class="text-danger">*</span></label>
                                <input value="{{ old('address') }}" class="form-control" placeholder="Address" name="address" type="text" required>
                            </div>
                        </div>
                    </div>
                    {{-- Row 2: Gender, Phone, Date of Birth (3 columns, same baseline) --}}
                    <div class="row mt-3 personal-data-row-2">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium" for="gender">Gender <span class="text-danger">*</span></label>
                                <select class="select form-control" id="gender" name="gender" required data-fouc data-placeholder="Choose...">
                                    <option value=""></option>
                                    <option {{ (old('gender') == 'Male') ? 'selected' : '' }} value="Male">Male</option>
                                    <option {{ (old('gender') == 'Female') ? 'selected' : '' }} value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium">Phone</label>
                                <input value="{{ old('phone') }}" type="text" name="phone" class="form-control" placeholder="Phone number">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium">Date of Birth</label>
                                <input name="dob" value="{{ old('dob') }}" type="text" class="form-control date-pick" placeholder="Select Date...">
                            </div>
                        </div>
                    </div>

                    {{-- Smart Admission: Parent Name & Phone (resolve or create parent in backend) --}}
                    <div class="row mt-3 personal-data-row-2">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium">Parent Name <span class="text-danger">*</span></label>
                                <input value="{{ old('parent_name') }}" required type="text" name="parent_name" placeholder="Parent / Guardian full name" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium">Parent Phone <span class="text-danger">*</span></label>
                                <input value="{{ old('parent_phone') }}" required type="text" name="parent_phone" placeholder="Parent phone number" class="form-control">
                            </div>
                        </div>
                    </div>

                    {{-- Hidden for Somali MVP: Email, Telephone, Nationality, State, LGA, Blood Group, Passport Photo --}}
                    {{--
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Email address: </label>
                                <input type="email" value="{{ old('email') }}" name="email" class="form-control" placeholder="Email Address">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Telephone:</label>
                                <input value="{{ old('phone2') }}" type="text" name="phone2" class="form-control" placeholder="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="nal_id">Nationality: <span class="text-danger">*</span></label>
                                <select data-placeholder="Choose..." required name="nal_id" id="nal_id" class="select-search form-control">
                                    <option value=""></option>
                                    @foreach($nationals as $nal)
                                        <option {{ (old('nal_id') == $nal->id ? 'selected' : '') }} value="{{ $nal->id }}">{{ $nal->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="state_id">State: <span class="text-danger">*</span></label>
                            <select onchange="getLGA(this.value)" required data-placeholder="Choose.." class="select-search form-control" name="state_id" id="state_id">
                                <option value=""></option>
                                @foreach($states as $st)
                                    <option {{ (old('state_id') == $st->id ? 'selected' : '') }} value="{{ $st->id }}">{{ $st->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="lga_id">LGA: <span class="text-danger">*</span></label>
                            <select required data-placeholder="Select State First" class="select-search form-control" name="lga_id" id="lga_id">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bg_id">Blood Group: </label>
                                <select class="select form-control" id="bg_id" name="bg_id" data-fouc data-placeholder="Choose..">
                                    <option value=""></option>
                                    @foreach(App\Models\BloodGroup::all() as $bg)
                                        <option {{ (old('bg_id') == $bg->id ? 'selected' : '') }} value="{{ $bg->id }}">{{ $bg->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="d-block">Upload Passport Photo:</label>
                                <input value="{{ old('photo') }}" accept="image/*" type="file" name="photo" class="form-input-styled" data-fouc>
                                <span class="form-text text-muted">Accepted Images: jpeg, png. Max file size 2Mb</span>
                            </div>
                        </div>
                    </div>
                    --}}

                </fieldset>

                <h6>Student Data</h6>
                <fieldset>
                    {{-- Row 1: Class, Section (side-by-side) --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium" for="my_class_id">Class <span class="text-danger">*</span></label>
                                <select onchange="getClassSections(this.value)" data-placeholder="Choose..." required name="my_class_id" id="my_class_id" class="select-search form-control">
                                    <option value=""></option>
                                    @foreach($my_classes as $c)
                                        <option {{ (old('my_class_id') == $c->id ? 'selected' : '') }} value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium" for="section_id">Section <span class="text-danger">*</span></label>
                                <select data-placeholder="Select Class First" required name="section_id" id="section_id" class="select-search form-control">
                                    <option {{ (old('section_id')) ? 'selected' : '' }} value="{{ old('section_id') }}">{{ (old('section_id')) ? 'Selected' : '' }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: Year Admitted, Admission Number (Parent resolved from Step 1) --}}
                    <div class="row mt-3">
                        {{-- Parent dropdown hidden: parent is set from Step 1 (parent_name + parent_phone) --}}
                        {{--
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium" for="my_parent_id">Parent</label>
                                <select data-placeholder="Choose..." name="my_parent_id" id="my_parent_id" class="select-search form-control">
                                    <option value=""></option>
                                    @foreach($parents as $p)
                                        <option {{ (old('my_parent_id') == Qs::hash($p->id)) ? 'selected' : '' }} value="{{ Qs::hash($p->id) }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        --}}
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium" for="year_admitted">Year Admitted <span class="text-danger">*</span></label>
                                <select data-placeholder="Choose..." required name="year_admitted" id="year_admitted" class="select-search form-control">
                                    <option value=""></option>
                                    @for($y=date('Y', strtotime('- 10 years')); $y<=date('Y'); $y++)
                                        <option {{ (old('year_admitted') == $y) ? 'selected' : '' }} value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="font-weight-medium">Admission Number</label>
                                <input type="text" name="adm_no" placeholder="Student ID (optional)" class="form-control" value="{{ old('adm_no') }}">
                            </div>
                        </div>
                    </div>

                    {{-- Hidden for Somali MVP: Dormitory, Dormitory Room No, Sport House --}}
                    {{--
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label for="dorm_id">Dormitory: </label>
                            <select data-placeholder="Choose..." name="dorm_id" id="dorm_id" class="select-search form-control">
                                <option value=""></option>
                                @foreach($dorms as $d)
                                    <option {{ (old('dorm_id') == $d->id) ? 'selected' : '' }} value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Dormitory Room No:</label>
                                <input type="text" name="dorm_room_no" placeholder="Dormitory Room No" class="form-control" value="{{ old('dorm_room_no') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Sport House:</label>
                                <input type="text" name="house" placeholder="Sport House" class="form-control" value="{{ old('house') }}">
                            </div>
                        </div>
                    </div>
                    --}}
                </fieldset>

            </form>
        </div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var $wizard = $('#ajax-reg');
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
});
</script>
@endsection
