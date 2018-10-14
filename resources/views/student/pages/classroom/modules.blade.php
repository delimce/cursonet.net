<section>
    <span class="in-title">@lang('students.classroom.modules.list')</span>
    @if($topics->count()==0)
        <span>@lang('students.classroom.modules.nofound')</span>
    @else
        @foreach ($topics as $i => $module)
            <div class="module-item @if ($i==0) current-module @endif" data-topic="{{$module->id}}">
                <span>{{$i+1}}.&nbsp;{{$module->titulo}}</span>
            </div>
        @endforeach
    @endif
</section>

@push('scripts')
    <script>
        $('.module-item').on('click', function (event) {
            var topic_id = $(this).data('topic');
            $(".current-module").removeClass("current-module")
            $(this).addClass("current-module");
            axios.get('{!! url('api/student/class/topic') !!}' + '/' + topic_id + '/group/' + '{{session()->get('groupId')}}')
                .then(function (response) {
                    $('#myContent').html(response.data.info.contenido)
                    $('#topic-selected').html(response.data.info.titulo)
                    $('#file-list').bootstrapTable('load', {
                        data: response.data.info.files
                    });

                    switchForumView();
                    $('#forum-list').bootstrapTable('load', {
                        data: response.data.info.forums
                    });

                }).catch(function (error) {
                showAlert("@lang('students.classroom.topic.select.error')");
            });
        })
    </script>
@endpush