import '../css/dropzone.scss';

import $ from 'jquery';

import * as Dropzone from 'dropzone'; 

Dropzone.autoDiscover = false;
$(document).ready(function() {
    initializeDropzone();
});

function initializeDropzone() {
    var formElement = document.querySelector('.dropzone');
    if (!formElement) {
        return;
    }
    var dropzone = new Dropzone(formElement, {
        paramName: 'imageFile',
        init: function() {
            this.on('error', function(file, data) {
                if (data.detail) {
                    this.emit('error', file, data.detail);
                }
            });
        }
    });
}
