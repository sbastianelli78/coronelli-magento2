require([
    'jquery'
], function ($) {
    'use strict';

    $(document).ready(function(){
        setTimeout(function(){
            if($('input[name="customer[privacy]"]').length) {
                $('input[name="customer[privacy]"]').attr('disabled', true)
            }
        }, 3000)
    });

});