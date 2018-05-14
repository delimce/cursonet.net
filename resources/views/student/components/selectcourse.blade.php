<div id="nav-selects">
    <select class="adv-select" name="course" id="course">
        @if(count($courses)>0)
            @foreach ($courses as $course)
                <option value="{{ $course["id"] }}"
                        @if(session()->has("courseSelected") && (session()->get("courseSelected") == $course["id"]))
                        selected @endif>
                    {{ $course["alias"] }}</option>
            @endforeach
        @endif
    </select>
</div>
@push('scripts')
    <script>
        $('.adv-select').select2({
            placeholder: "Seleccone un curso",
            allowClear: false
        });

        $('#course').on('change', function () {
            console.log(this.value)
            axios.post('{!! url('student/select/course') !!}', {
                courseId: this.value
            }).then(function (response) {

                @if(basename(url::current())=="home")
                $("#course_name").html(response.data.course.nombre);
                $("#course_duration").html(response.data.course.duracion);
                $("#course_description").html(response.data.course.descripcion);
                $("#course_button").show();
                @else
                redirect('{!! url('student/home') !!}', false)
                @endif

            }).catch(function (error) {
                showAlert("error al seleccionar el curso")
                $("#course_button").hide();
            });
        })
    </script>
@endpush