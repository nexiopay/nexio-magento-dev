define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/place-order',
        'Magento_Checkout/js/checkout-data',
        'jquery'
    ],
    function (
        quote,
        urlBuilder,
        customer,
        placeOrderService,
        checkoutData,
        $
    ) {
        'use strict';
        return function (paymentData, messageContainer) {
            
            var serviceUrl, payload;
            if (checkoutData.getSelectedPaymentMethod() == 'nexiogrouppayment') {

                //without 3ds place a order with stored payment or else with a new card
                if(parseInt($("#cardstatuscheck").val())) {
                    var defaultToken = $('input[name="tokeforpayment"]:checked').val();
                } else {
                    var saveTokenEx = $("#saveCardtoken").val();
                }
                
                paymentData.additional_data = {            
                    isCreditCardExit: parseInt($("#cardstatuscheck").val()),
                    isCardSave: parseInt($('#cardsavestatus').val()),
                    defaultToken: defaultToken,
                    saveTokenEx:saveTokenEx
                };
            }

            payload = {
                cartId: quote.getQuoteId(),
                billingAddress: quote.billingAddress(),
                paymentMethod: paymentData
            };

            if (customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/carts/mine/payment-information', {});
            } else {
                    serviceUrl = urlBuilder.createUrl(
                        '/guest-carts/:quoteId/payment-information',
                        {
                            quoteId: quote.getQuoteId()

                        }
                    );
                payload.email = quote.guestEmail;
            }

            return placeOrderService(serviceUrl, payload, messageContainer);
        };
    }
);
