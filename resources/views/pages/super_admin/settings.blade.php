@extends('layouts.master')
@section('page_title', 'Manage System Settings')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">Update System Settings</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <form method="post" action="{{ route('settings.update') }}">
                @csrf @method('PUT')
            <div class="row align-items-start">
                <div class="col-md-6 border-right-2 border-right-blue-400">
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Name of School <span class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input name="system_name" value="{{ $s['system_name'] }}" required type="text" class="form-control" placeholder="Name of School">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="current_session" class="col-lg-3 col-form-label font-weight-semibold">Current Session <span class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <select data-placeholder="Choose..." required name="current_session" id="current_session" class="select-search form-control">
                                    <option value=""></option>
                                    @for($y=date('Y', strtotime('- 3 years')); $y<=date('Y', strtotime('+ 1 years')); $y++)
                                        <option {{ ($s['current_session'] == (($y-=1).'-'.($y+=1))) ? 'selected' : '' }}>{{ ($y-=1).'-'.($y+=1) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">School Acronym</label>
                            <div class="col-lg-9">
                                <input name="system_title" value="{{ $s['system_title'] }}" type="text" class="form-control" placeholder="School Acronym">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Phone</label>
                            <div class="col-lg-9">
                                <input name="phone" value="{{ $s['phone'] }}" type="text" class="form-control" placeholder="Primary phone number">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">School Email</label>
                            <div class="col-lg-9">
                                <input name="system_email" value="{{ $s['system_email'] }}" type="email" class="form-control" placeholder="School Email">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">School Address <span class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input required name="address" value="{{ $s['address'] }}" type="text" class="form-control" placeholder="School Address">
                            </div>
                        </div>
                </div>
                <div class="col-md-6">
                    {{--Fees--}}
                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label font-weight-semibold">School Weekend Days</label>
                        <div class="col-lg-9">
                            <select name="weekend_type" class="form-control select-search">
                                <option value="sat_sun" {{ ($s['weekend_type'] ?? 'sat_sun') === 'sat_sun' ? 'selected' : '' }}>Saturday &amp; Sunday</option>
                                <option value="thu_fri" {{ ($s['weekend_type'] ?? 'sat_sun') === 'thu_fri' ? 'selected' : '' }}>Thursday &amp; Friday</option>
                            </select>
                        </div>
                    </div>

               <fieldset>
                   <legend><strong>Next Term Fees</strong></legend>
                   @foreach($class_types as $ct)
                   <div class="form-group row">
                       <label class="col-lg-3 col-form-label font-weight-semibold">{{ $ct->name }}</label>
                       <div class="col-lg-9">
                           <input class="form-control" value="{{ $s['next_term_fees_'.$ct->id] ?? $s['next_term_fees_'.strtolower($ct->code)] ?? '' }}" name="next_term_fees_{{ $ct->id }}" placeholder="{{ $ct->name }} fee" type="text">
                       </div>
                   </div>
                   @endforeach
               </fieldset>
                </div>
            </div>

                <hr class="divider">

                <div class="text-right">
                    <button type="submit" class="btn btn-danger">Submit form <i class="icon-paperplane ml-2"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header header-elements-inline" style="background-color: #1A1A1A;">
            <h6 class="card-title font-weight-semibold text-white">Term assessment control</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-2">Switch which assessment (month/slot) is open for editing school-wide. All other slots become readonly in Marks Entry.</p>
            <form method="post" action="{{ route('settings.active_slot') }}" class="row align-items-end">
                @csrf
                @method('put')
                <div class="col-md-4 mb-2">
                    <label class="font-weight-semibold">Select Grading Scheme</label>
                    <select id="active-scheme-select" name="active_scheme_id" class="form-control form-control-sm">
                        <option value="" selected disabled>Choose scheme</option>
                        @foreach(($assessment_schemes ?? []) as $scheme)
                            <option value="{{ $scheme['id'] }}" {{ (int) ($current_active_scheme_id ?? 0) === (int) $scheme['id'] ? 'selected' : '' }}>{{ $scheme['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="font-weight-semibold">Open for editing</label>
                    <select id="active-assessment-title-select" name="active_assessment_title" class="form-control form-control-sm">
                        <option value="" selected disabled>Choose assessment</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <button id="set-active-assessment-btn" type="submit" class="btn btn-sm btn-danger" style="min-width: 180px;">Set active assessment</button>
                </div>
            </form>
        </div>
    </div>

    {{--Settings Edit Ends--}}

@endsection

@section('scripts')
<script>
(function () {
    var schemeSelect = document.getElementById('active-scheme-select');
    var titleSelect = document.getElementById('active-assessment-title-select');
    var submitBtn = document.getElementById('set-active-assessment-btn');
    if (!schemeSelect || !titleSelect || !submitBtn) {
        return;
    }

    var schemeMap = @json($scheme_assessment_map ?? []);
    var currentSchemeId = @json((string) ($current_active_scheme_id ?? ''));
    var currentTitle = @json($current_active_assessment_title ?? '');

    function populateTitles(selectedSchemeId, preferredTitle) {
        var titles = schemeMap[String(selectedSchemeId)] || [];
        titleSelect.innerHTML = '';

        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = titles.length ? 'Choose assessment' : 'No assessments in this scheme';
        placeholder.disabled = true;
        placeholder.selected = true;
        titleSelect.appendChild(placeholder);

        titles.forEach(function (title) {
            var option = document.createElement('option');
            option.value = title;
            option.textContent = title;
            if (preferredTitle && preferredTitle === title) {
                option.selected = true;
            }
            titleSelect.appendChild(option);
        });

        if (!preferredTitle && titles.length) {
            titleSelect.value = titles[0];
        }

        titleSelect.disabled = titles.length === 0;
        submitBtn.disabled = !selectedSchemeId || titles.length === 0 || !titleSelect.value;
    }

    schemeSelect.addEventListener('change', function () {
        populateTitles(schemeSelect.value, '');
    });

    titleSelect.addEventListener('change', function () {
        submitBtn.disabled = !schemeSelect.value || !titleSelect.value;
    });

    populateTitles(currentSchemeId || schemeSelect.value, currentTitle);
})();
</script>
@endsection
