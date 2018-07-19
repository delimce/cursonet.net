<section>
    <h2>@lang('students.classroom.modules.select')</h2>
    @foreach ($topics as $i => $module)
        <div class="module-item @if ($i==0) current-module @endif" data-topic="{{$module->id}}">
            <span>{{$i+1}}.&nbsp;{{$module->titulo}}</span>
        </div>
    @endforeach
</section>

@push('scripts')
    <script>
        $('.module-item').on('click', function (event) {
            var topic_id = $(this).data('topic');
            $(".current-module").removeClass("current-module")
            $(this).addClass("current-module");

        })
    </script>
@endpush