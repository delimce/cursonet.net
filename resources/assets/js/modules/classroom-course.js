$('#course').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
    // do something...
    loadCourseInformation(this.value)
});

$('#file-list').on('click-cell.bs.table', function (field, value, row, $element) {
    if (Number($element.tipo_id) === 0) {
        axios.request({
            responseType: 'blob',
            url: api_url + 'api/student/class/file' + '/' + $element.id,
            method: 'get',
        }).then(function (response) {
            downloadFile(response, String($element.dir))
        }).catch(function (error) {
            showAlert("No es posible encontrar el archivo")
        });
    } else {
        window.open(String($element.dir), '_blank');
    }
});

$('.module-item').on('click', function (e) {
    let topic_id = $(this).data('topic');
    $(".current-module").removeClass("current-module")
    $(this).addClass("current-module");
    axios.get(api_url + 'api/student/class/topic' + '/' + topic_id)
        .then(function (response) {
            $('#myContent').html(response.data.info.content)
            $('#topic-selected').html(response.data.info.title)
            $('#file-list').bootstrapTable('load', response.data.info.files);
            $('#forum-list').bootstrapTable('load', response.data.info.forums);
            $('#project-list').bootstrapTable('load', response.data.info.projects);
            switchForumView();
            switchProjectView();
            goToContent()
        }).catch(function (error) {
            showAlert("error al seleccionar el curso");
        });
})

/**
 * parsing wall's json object
 * @param {*} wall 
 */
const parseCourseWall = function (wall) {

    let messages = '<b>No existen mensajes en la cartelera</b>'
    if (wall.length > 0) {
        messages = '';
        _.forEach(_.orderBy(wall, ['id'], ['desc']), function (item) {
            let myDate = jQuery.timeago(item.fecha_c);
            let temp = item.mensaje + '<hr><span class="written"> Escrito: ' + myDate + "</span><p>&nbsp;</p>"
            messages += temp;
        });
    }
    return messages;
}

/**
 * parsing course info's json object
 * @param {*} courseId 
 */
const loadCourseInformation = function (courseId) {
    axios.post(api_url + 'student/select/course', {
        courseId: courseId
    }).then(function (response) {
        let current = baseName($(location).attr('href'));
        if (current === "home") {
            if (!_.isUndefined(response.data.course)) {
                let detail = response.data.course
                $("#course_wall").html(parseCourseWall(detail.wall))
                $("#course_name").html(detail.nombre);
                $("#course_duration").html(detail.duracion);
                $("#course_group").html(detail.group);
                $("#course_description").html(detail.descripcion);
                $("#course_initdate").html(detail.init);
                $("#course_ntopics").html(detail.ntopics);
                $("#course_professor").html(detail.professor);
                if (detail.ntopics > 0) {
                    $("#course_button").show();
                } else {
                    $("#course_button").hide();
                }
            }
        } else {
            redirect(api_url + 'student/home', false)
        }

    }).catch(function (error) {
        console.log(error)
        showAlert("Error al seleccionar el curso")
        $("#course_button").hide();
    });
}

const goToContent = function () {
    $('.active').removeClass('active');
    $('#myContent').tab('show')
}