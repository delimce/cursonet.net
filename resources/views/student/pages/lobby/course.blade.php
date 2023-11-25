<section>
    <span class="in-title">@lang('students.course.info')</span>

    <div>
        <span class="subtext">@lang('students.course.name'):</span>
        <span id="course_name"></span>
    </div>
    <div>
        <span class="subtext">@lang('students.course.long'):</span>
        <span id="course_duration"></span>
    </div>
    <div id="course_description">
      
    </div>

    <div style="float: left; width: 50%">
        <div>
            <span class="subtext">@lang('commons.created_at')</span>
            <span id="course_initdate"></span>
        </div>
        <div>
            <span class="subtext">@lang('commons.group')</span>
            <span id="course_group"></span>
        </div>
        <div>
            <span class="subtext">@lang('students.classroom.modules.ntopics')</span>
            <span id="course_ntopics"></span>
        </div>
        <div>
            <span class="subtext">@lang('commons.teacher')</span>
            <span id="course_professor"></span>
        </div>
    </div>

    <div id="course_button" style="float: right; width: 50%; padding-top: 20px;display:none">
        <div class="col-sm-auto">
            <button id="btocontent" class="btn btn-lg btn-block btn-primary"
                    type="submit">@lang('students.course.goto')</button>
        </div>
    </div>
    
</section>
@push('scripts')
    <script>
        $("#btocontent").click(function () {
            redirect('{!! url('student/classroom') !!}');
        })
    </script>
@endpush