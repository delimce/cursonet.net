@extends('student.layout.app')
@section('title', trans('students.classroom.title'))
@section('content')
    @component("student.components.preloader")
        @slot("text")
            @lang('students.course.info.loading')
        @endslot
    @endcomponent
    <div class="wrapper">

        <nav id="sidebar">
            @include('student.pages.classroom.modules', ['modules' => [["selected"=>true],["selected"=>false],["selected"=>false]]])
        </nav>
        <!-- Page Content Holder -->
        <div id="content">
            @component("student.components.classroombox")

            @endcomponent
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
                $(this).toggleClass('active');
                $(this).find(".plusMinus").toggleClass('fa-arrow-left fa-arrow-right');
                if ($("#sidebar").hasClass('active'))
                    $(".swapText").html("@lang('students.classroom.modules.show')")
                else
                    $(".swapText").html("@lang('students.classroom.modules.hide')")
            });

        });
    </script>
@endpush
