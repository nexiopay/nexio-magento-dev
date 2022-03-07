define(
    [
    'jquery',
    'Magento_Ui/js/modal/modalToggle',
    'mage/translate'
    ], function ($, modalToggle) {
        'use strict';

        return function (config, deleteButton) {
            config.buttons = [
            {
                text: $.mage.__('Cancel'),
                class: 'action make-default'
            }, {
                text: $.mage.__('Make Default'),
                class: 'action primary',

                /**
                 * Default action on button click
                 */
                click: function (event) {
                    //eslint-disable-line no-unused-vars
                    $(deleteButton.form).submit();
                }
            },
            ];

            modalToggle(config, deleteButton);
        };
    }
);