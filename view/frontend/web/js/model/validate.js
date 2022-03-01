define(
    [
        'jquery',
        'mage/url',
        'Magento_Checkout/js/checkout-data'
    ],
    function (
        $,
        url,
        checkoutData
    ) {
        'use strict';
        var indexCount = 0;
            return {
                validate: function () {
                    if (checkoutData.getSelectedPaymentMethod() == 'nexiogrouppayment') {
                        var validation = $('#cardstatuscheck').val(); 
                        if (!(parseInt(validation))) {  
                            var iframe = window.document.getElementById('iframecontent');
                            if ($('#iframe_data').hasClass("nexio")) {
                                return true;
                            }

                            window.addEventListener(
                                'message', function messageListener(event)
                                {
                                if (event.data.event != undefined && event.data.event != "formValidations") {
                                      
                                    setTimeout(
                                        function () {
                                            $('.loading-mask').show(); }, 0
                                    );
                                }
                                if (event.data.event != undefined && event.data.event === "error") {
                                    $('body').trigger('processStart');
                                }
                                if (event.data.event != undefined && event.data.event != "formValidations" && event.data.event != "submit" && event.data.event === "cardSaved" && event.data.event != "error") {
                                    $('#iframe_data').addClass("nexio");
                                    indexCount++;
                                    if(indexCount == 1) {
                                        var savedCardToken = event.data.data.token.token;
                                        $('#saveCardtoken').val(savedCardToken);
                                        $("#submit").trigger("click");
                                    }
                                }
                                }
                            );

                            var setUrl = url.build('onlinepayment/index/config');
                            iframe.contentWindow.postMessage('posted', setUrl);

                            return false;
                        }
                    } else {

                        return true;
                    }
                }
        };
    },
);
