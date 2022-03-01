define(
    [
        'Magento_Checkout/js/model/quote',
        'jquery'
    ],
    function (
        quote,
        $
    ) {
        'use strict';
        return function (paymentMethod) {
            quote.paymentMethod(paymentMethod);
           
            if (paymentMethod.method == 'nexiogrouppayment') {   
                $('body').loader('show');
                var interval = setInterval(
                    function () {
                        if(document.getElementById("iframecontent") != undefined) {
                            const nexioIframe   = document.getElementById("iframecontent").contentWindow.document;
                            var cardholderName  = nexioIframe.getElementById("cardHolderName");
                            if(cardholderName != undefined) {
                                clearInterval(interval);
                                $('body').loader('hide');
                            }
                        }
                    }, 1000
                );
            }
        };
    }
);
