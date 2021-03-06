<section id="classroom-content">

    <div class="content-header">
         <span id="topic-selected">
            {{$title}}
        </span>
        <a href="#" id="sidebarCollapse">
            <i class="plusMinus fas fa-arrow-left"></i>
            <span class="swapText">@lang('students.classroom.modules.hide')</span>
        </a>
    </div>


    <ul class="nav nav-tabs" id="nav-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#myContent"
               role="tab" aria-controls="contenido" aria-selected="true">
                <span class="subtext">@lang('students.classroom.modules.topics')</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="files-tab" data-toggle="tab" href="#resources" role="tab"
               aria-controls="recursos" aria-selected="false">
                <span class="subtext">@lang('students.classroom.modules.files')</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" id="forum-tab" data-toggle="tab" href="#forum" role="tab"
               aria-controls="foros" aria-selected="false">
                <span class="subtext">@lang('students.classroom.modules.forums')</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" id="eval-tab" data-toggle="tab" href="#eval" role="tab"
               aria-controls="evaluaciones" aria-selected="false">
                <span class="subtext">@lang('students.classroom.modules.exams')</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" id="project-tab" data-toggle="tab" href="#project" role="tab"
               aria-controls="proyectos" aria-selected="false">
                <span class="subtext">@lang('students.classroom.modules.projects')</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="request-tab" data-toggle="tab" href="#request" role="tab"
               aria-controls="consultar" aria-selected="false">
                <span class="subtext"> Consultas </span>
            </a>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="myContent" role="tabpanel"
             aria-labelledby="home-tab">
            @include('student.pages.classroom.content', ['content' => $content])
        </div>
        <div class="tab-pane fade" id="resources" role="tabpanel" aria-labelledby="files-tab">
            @include('student.pages.classroom.files', ['files'=>$files])
        </div>

        <div class="tab-pane fade" id="forum" role="tabpanel" aria-labelledby="forum-tab">
            @include('student.pages.classroom.forums', ['forums' => $forums])
        </div>

        <div class="tab-pane fade" id="eval" role="tabpanel" aria-labelledby="eval-tab">
            @include('student.pages.classroom.exams', ['some' => 'data'])
        </div>

        <div class="tab-pane fade" id="project" role="tabpanel" aria-labelledby="project-tab">
            @include('student.pages.classroom.projects', ['projects' => $projects])
        </div>
        <div class="tab-pane fade" id="request" role="tabpanel" aria-labelledby="request-tab">
            ...
        </div>
    </div>

</section>