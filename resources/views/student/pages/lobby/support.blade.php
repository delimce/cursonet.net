<div class="modal fade" id="support" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLongTitle">
                    <i class="fas fa-life-ring"></i>
                    @lang('students.support.title')
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                       <span>@lang('students.support.message')</span>
                    </div>

                    <div class="form-group">
                        @component("student.components.richText")
                            @slot("id")
                                msupport
                            @endslot
                        @endcomponent
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" style="width: 150px" id="support-message"
                        class="btn btn-lg btn-block btn-primary">
                    <span class="glyphicon glyphicon-search"></span>
                    @lang('students.inbox.send')
                </button>
            </div>
        </div>
    </div>
</div>