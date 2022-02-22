<div>

  <div class="forum-toolbox">
    <span class="forum-list-back" data-toggle="tooltip" data-placement="bottom"
      title="@lang('students.classroom.forum.showlist')">
      <i class="fas fa-list-ol"></i>
    </span>

    <span class="forum-list-top" data-toggle="tooltip" data-placement="bottom"
        title="@lang('students.classroom.forum.top')">
        <i class="fas fa-arrow-alt-circle-up"></i>
    </span>

    <span class="forum-list-refresh" data-toggle="tooltip" data-forum="{{$content->id}}" data-placement="bottom"
        title="@lang('students.classroom.forum.reload')">
        <i class="fas fa-sync-alt"></i>
    </span>

      @if($content->status()===$content::STATUS_ACTIVE)
        <span class="forum-list-post" data-toggle="tooltip" data-placement="bottom"
            title="@lang('students.classroom.forum.post.new')">
            <i class="fas fa-edit"></i>
        </span> 
      @else  
      <div class="row">
        {{__('students.classroom.forum.status.message', ['status' => $content->statusName()])}}
      </div>
      @endif 

   </div>

    <div class="forum-content">
        <div class="forum-list-back">
          <p>
           <a href="#">@lang('students.classroom.forum.showlist')</a>
          </p>
        </div>
        <span class="in-title">{!! $content->titulo !!}</span>
        <span>{!! $content->content !!}</span>
    </div>

    @foreach($content->getPostsByPersonLikes(session()->get('myUser')->id) as $post)
        <div class="forum-post @if($post->tipo_sujeto=='admin') admin-border @endif">
            <div class="student-data">
                <?php $person = $post->person()->first() ?>

                @if($post->tipo_sujeto=='est') 
                  @component("student.components.avatar",['data' => $person])
                  @endcomponent
                @else  
                  @component("admin.components.avatar",['data' => $person])
                  @endcomponent
                @endif

                <span class="subtext">Nombre:</span>
                <span>{{$person->nombre.' '.$person->apellido }}</span><br>
                <span class="subtext">Perfil</span>
                <span>{{$post->tipo_sujeto}}</span><br>
                <span class="subtext">Publicado</span>
                <span>{{$post->publishedDate()}}</span><br>
            </div>
            <div class="post">
                {!! $post->content !!}
            </div>

            <div class="post-footer">
                @if($post->tipo_sujeto=='est')
                    <div class="status">
                        <span class="subtext">@lang('students.classroom.forum.post.status')</span>&nbsp;
                        <span>{{$post->statusName()}}</span>
                    </div>
                @endif

                <div class="tools" data-post-id="{{$post->id}}">
                    <span class="forum-tools-reply" data-toggle="tooltip" data-placement="top"
                          title="@lang('students.classroom.forum.post.reply')">
                       <i class="far fa-comment"></i>
                    </span>
                    <span class="forum-tools-like"
                          data-toggle="tooltip" data-placement="top"
                          title="@lang('students.classroom.forum.post.like')">
                        <i class="@if(!is_null($post->opinion)) fas fa-thumbs-up @else far fa-thumbs-up @endif"></i>
                        <span class="nlikes">@if($post->likes>0){{$post->likes}} @endif</span>
                    </span>
                </div>

            </div>
            <span id="post-replies-<?=$post->id?>">
              <ol>
                @foreach($post->replies()->get() as $reply)
                <li>
                  <div class="post-reply">
                    <span class="subtext">Respuesta el {{$reply->date()}}</span><br>
                    <span class="subtext">Por. </span>&nbsp;<span>
                      {{ $reply->person ? $reply->person->fullname(): '' }}
                      </span><br>
                    <span class="subtext">Contenido:</span>&nbsp;<span>{{$reply->content}}</span><br>
                  </div>
                </li>
                @endforeach
              </ol>
            </span>
            <span id="post-new-reply-<?=$post->id?>"></span>

        </div>
    @endforeach

    <div id="new-post">
        <div>
            <span>@lang('students.classroom.forum.post.title')&nbsp;<span
                  class="subtext">{!! $content->titulo !!}</span></span>
            <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="content">
            <textarea name="post_content" id="post_content"></textarea>
        </div>
        <div class="footer">
            <button id="save-post" data-forum="{{$content->id}}" data-person="{{session()->get("myUser")->id}}"
                    data-type="est" type="button" style="width: 150px"
                    class="btn btn-lg btn-block btn-primary">
                <span class="glyphicon glyphicon-search"></span>
                @lang('students.classroom.forum.post.save')
            </button>
        </div>
    </div><!-- modal-content -->
</div>

<script>
  (function($) {
    $('[data-toggle="tooltip"]').tooltip();

    $('.forum-list-back').on('click', function() {
      switchForumView();
    });

    $('.close').on('click', function() {
      $('#new-post').hide();
    });

    $(".forum-list-top").click(function(event) {
      event.preventDefault();
      $("html, body").animate({scrollTop: 0}, 800);
      return false;
    });

    $('.forum-list-post').on('click', function() {
      $('#new-post').show();
      $('#forum-title').html($(".forum-content").find(".in-title").html());
    });

    $('.forum-list-refresh').on('click', function() {
      $(this).tooltip('hide');
      let forum_id = $(this).data("forum");
      forumReload(forum_id);

    });

    ///forum tools
    $('.forum-tools-like').on('click', function() {
      let me = $(this);
      let like = me.find(".nlikes");
      let current = Number(like.html());
      me.tooltip('hide');
      let post_id = me.parent().data('post-id');
      axios.request({
                      method: 'put',
                      url:    '{!! url('api/student/class/forum/post/like') !!}',
                      data:   {"post": post_id}
                    }).then(function(response) {
        $("i", me).toggleClass("far fas");
        current = (Boolean(response.data.message)) ? current + 1 : current - 1;
        current = (current === 0) ? "" : current;
        like.html(current);
      }).catch(function(error) {
        showAlert(error.response.data.message);
      });
    });

    ///new reply
    $('.forum-tools-reply').on('click', function() {
      let me = $(this);
      let forum_id = $('.forum-list-refresh').data("forum");
      let post_id = me.parent().data('post-id');
      let user_id = $('#save-post').data("person");
      let reply = $("#post-new-reply-" + post_id);
      let comment = '<span class="new-reply-'+post_id+'">';
      comment += '<textarea id="my-reply-' + post_id + '" cols="40" rows="2"></textarea><br>';
      comment += '<button onClick="javascript:clearReply(' + post_id +')" class="btn btn-secondary">@lang('commons.close')</button>&nbsp;&nbsp;';
      comment += '<button onClick="javascript:sendPostReply(' + post_id + ',' + user_id + ',' + forum_id + ')" class="btn btn-lg btn-primary" style="width: 200px">' +
        '@lang('students.classroom.forum.post.reply')' +
        '</button>';
      comment += '</span>';
      reply.html(comment);
      me.tooltip('hide');

    });

    $('#save-post').on('click', function() {
      $(this).prop('disabled', true);
      let dataPost = {};
      dataPost.forum = $(this).data("forum");
      dataPost.person = $(this).data("person");
      dataPost.type = $(this).data("type");
      dataPost.content = CKEDITOR.instances.post_content.getData();
      saveForumPost(dataPost);
      $(this).prop('disabled', false);
    });

    CKEDITOR.replace('post_content', {
      width:    '100%',
      height:   110,
      toolbar:  [
        {name: 'mode', items: ['Source']},
        {name: 'clipboard', items: ['PasteText', 'Undo', 'Redo']},
        {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
        {name: 'basicstyles', items: ['Bold', 'Italic', 'Subscript', 'Superscript', 'RemoveFormat']},
        {name: 'paragraph', items: ['NumberedList', 'BulletedList']},
        {name: 'tools', items: ['Maximize', 'ShowBlocks']},
      ],
      language: 'es'
    });

  }(jQuery));

</script>