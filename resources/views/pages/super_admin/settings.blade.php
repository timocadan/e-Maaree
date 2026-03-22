@extends('layouts.master')
@section('page_title', 'Manage System Settings')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">Update System Settings</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <form enctype="multipart/form-data" method="post" action="{{ route('settings.update') }}">
                @csrf @method('PUT')
            <div class="row">
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
                            <label class="col-lg-3 col-form-label font-weight-semibold">Secondary Phone</label>
                            <div class="col-lg-9">
                                <input name="phone2" value="{{ $s['phone2'] ?? '' }}" type="text" class="form-control" placeholder="Secondary phone number">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">School Website</label>
                            <div class="col-lg-9">
                                <input name="website" value="{{ $s['website'] ?? '' }}" type="text" class="form-control" placeholder="https://example-school.com">
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
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">This Term Ends</label>
                            <div class="col-lg-6">
                                <input name="term_ends" value="{{ $s['term_ends'] }}" type="text" class="form-control date-pick" placeholder="Date Term Ends">
                            </div>
                            <div class="col-lg-3 mt-2">
                                <span class="font-weight-bold font-italic">M-D-Y or M/D/Y </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Next Term Begins</label>
                            <div class="col-lg-6">
                                <input name="term_begins" value="{{ $s['term_begins'] }}" type="text" class="form-control date-pick" placeholder="Date Term Ends">
                            </div>
                            <div class="col-lg-3 mt-2">
                                <span class="font-weight-bold font-italic">M-D-Y or M/D/Y </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="lock_exam" class="col-lg-3 col-form-label font-weight-semibold">Lock Exam</label>
                            <div class="col-lg-3">
                                <select class="form-control select" name="lock_exam" id="lock_exam">
                                    <option {{ $s['lock_exam'] ? 'selected' : '' }} value="1">Yes</option>
                                    <option {{ $s['lock_exam'] ?: 'selected' }} value="0">No</option>
                                </select>
                            </div>
                            <div class="col-lg-6">
                                    <span class="font-weight-bold font-italic text-info-800">{{ __('msg.lock_exam') }}</span>
                            </div>
                        </div>
                </div>
                <div class="col-md-6">
                    {{--Fees--}}
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
                    <hr class="divider">

                    {{--Logo--}}
                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label font-weight-semibold">Change Logo:</label>
                        <div class="col-lg-9">
                            <div class="mb-3">
                                @php
                                    $logoPath = $s['logo'] ?? '';
                                    if (!empty($logoPath)) {
                                        if (strpos($logoPath, 'http') === 0) {
                                            $logoUrl = $logoPath;
                                        } else {
                                            $logoUrl = rtrim(config('app.url'), '/') . '/storage/' . ltrim($logoPath, '/');
                                        }
                                    } else {
                                        $logoUrl = '';
                                    }
                                @endphp
                                @if(!empty($logoUrl))
                                    <img style="width: 100px; height: 100px; object-fit: contain;" src="{{ $logoUrl }}" alt="School logo">
                                @else
                                    <div style="width: 100px; height: 100px; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center; color: #999; font-size: 12px;">No logo</div>
                                @endif
                            </div>
                            <input name="logo" accept="image/*" type="file" class="file-input" data-show-caption="false" data-show-upload="false" data-fouc>
                        </div>
                    </div>
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
            <form method="post" action="{{ route('settings.active_slot') }}" class="form-inline">
                @csrf
                @method('put')
                <label class="mr-2 font-weight-semibold">Open for editing:</label>
                <select name="active_slot" class="form-control form-control-sm mr-2" style="min-width: 140px;">
                    @for($i = 0; $i <= 5; $i++)
                        <option value="{{ $i }}" {{ ($current_active_slot ?? 0) == $i ? 'selected' : '' }}>{{ $i === 0 ? 'Slot 1 (Month 1)' : ($i === 1 ? 'Slot 2 (Month 2)' : ($i === 2 ? 'Slot 3 (Month 3)' : ($i === 3 ? 'Slot 4 (Month 4)' : ($i === 4 ? 'Exam' : 'Slot ' . ($i + 1)))) }}</option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-sm" style="background-color: #D32F2F; color: #fff; border-color: #D32F2F;">Set active slot</button>
            </form>
        </div>
    </div>

    {{--Settings Edit Ends--}}

@endsection
