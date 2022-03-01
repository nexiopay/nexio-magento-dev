define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {

        'use strict';
        rendererList.push(
            {
                type: 'nexiogrouppayment',
                component: 'Nexio_OnlinePayment/js/view/payment/method-renderer/nexiogrouppayment-method'
            }
        );

        return Component.extend(
            {

            }
        );
    }
);
