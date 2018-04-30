'use strict'
var showAlert = function (message) {
    $.notify({
        // options
        icon: 'fas fa-exclamation-circle',
        title:'Error:',
        message: message
    },{
        // settings
        type: 'danger',
        spacing: 10,
        delay: 2000,
        placement: {
            from: "top",
            align: "right"
        },
    });
}

var showInfo = function (message) {
    $.notify({
        // options
        icon: 'fas fa-question-circle',
        title:'Información:',
        message: message
    },{
        // settings
        type: 'info',
        spacing: 10,
        delay: 3500,
        placement: {
            from: "bottom",
            align: "right"
        },
    });
}

var showSuccess = function (message) {
    $.notify({
        // options
        icon: 'fas fa-check-circle',
        message: message
    },{
        // settings
        type: 'success',
        spacing: 10,
        delay: 1500,
        placement: {
            from: "bottom",
            align: "right"
        },
    });
}