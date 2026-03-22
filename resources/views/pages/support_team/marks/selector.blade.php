<form method="post" action="{{ route('marks.selector') }}">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-5 col-lg-4">
                    <div class="form-group">
                        <label for="term" class="col-form-label font-weight-bold">Term:</label>
                        <select required id="term" name="term" class="form-control select">
                            <option value="">Select Term</option>
                            @foreach($terms ?? [1 => 'Term 1', 2 => 'Term 2'] as $val => $label)
                                <option {{ ($selected && isset($term) && $term == $val) ? 'selected' : '' }} value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-5 col-lg-4">
                    <div class="form-group">
                        <label for="my_class_id" class="col-form-label font-weight-bold">Class:</label>
                        <select required onchange="getClassSubjects(this.value)" id="my_class_id" name="my_class_id" class="form-control select">
                            <option value="">Select Class</option>
                            @foreach($my_classes as $c)
                                <option {{ ($selected && isset($my_class_id) && $my_class_id == $c->id) ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5 col-lg-4">
                    <div class="form-group">
                        <label for="section_id" class="col-form-label font-weight-bold">Section:</label>
                        <select required id="section_id" name="section_id" data-placeholder="Select Section" class="form-control select">
                            @if($selected && isset($my_class_id))
                                @foreach($sections->where('my_class_id', $my_class_id) as $s)
                                    <option {{ isset($section_id) && $section_id == $s->id ? 'selected' : '' }} value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-md-5 col-lg-4">
                    <div class="form-group">
                        <label for="subject_id" class="col-form-label font-weight-bold">Subject:</label>
                        <select required id="subject_id" name="subject_id" data-placeholder="Select Subject" class="form-control select-search">
                            @if($selected && isset($my_class_id))
                                @foreach($subjects->where('my_class_id', $my_class_id) as $s)
                                    <option {{ isset($subject_id) && $subject_id == $s->id ? 'selected' : '' }} value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary" style="background-color: #D32F2F; border-color: #D32F2F; color: #fff;">
                        Manage Marks <i class="icon-paperplane ml-2" style="color: #fff;"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
