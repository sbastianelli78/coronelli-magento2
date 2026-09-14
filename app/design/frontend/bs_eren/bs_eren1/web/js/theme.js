/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/smart-keyboard-handler',
    'mage/mage',
    'mage/ie-class-fixer',
    'domReady!',
    'mage/validation'
], function ($, keyboardHandler) {
    'use strict';

    if ($('body').hasClass('checkout-cart-index')) {
        if ($('#co-shipping-method-form .fieldset.rates').length > 0 && $('#co-shipping-method-form .fieldset.rates :checked').length === 0) {
            $('#block-shipping').on('collapsiblecreate', function () {
                $('#block-shipping').collapsible('forceActivate');
            });
        }
    }

    // giorg 20170911 - validazione codice fiscale  -- FIX ME: dovrebbe andare nel nostro tema ma non prende
    if ($('#fiscalcode').length) {
        $('#fiscalcode').addClass('validate-alphanum minimum-length-16 maximum-length-16');
        $('#fiscalcode').attr('data-validate',"{required: true, 'validate-alphanum': true, 'validate-length': true}")
    }

    $('.cart-summary').mage('sticky', {
        container: '#maincontent'
    });

    $('.panel.header > .header.links').clone().appendTo('#store\\.links');

    keyboardHandler.apply();
});
