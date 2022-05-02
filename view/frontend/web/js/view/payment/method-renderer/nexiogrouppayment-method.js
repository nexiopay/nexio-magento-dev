define(
    [
        'jquery',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Ui/js/model/messageList',
        'mage/translate',
        'domReady!'
    ],
    function (
        $,
        checkoutData,
        Component,
        url,
        additionalValidators,
        redirectOnSuccessAction,
        messageList,$t
    ) {
        'use strict';
        return Component.extend(
            {
                defaults: {
                    redirectAfterPlaceOrder: false,                    
                    template: 'Nexio_OnlinePayment/payment/nexiogrouppayment'
                },

                getMailingAddress: function () {

                    return window.checkoutConfig.payment.checkmo.mailingAddress;
                },
                
                /**
                 * Get the current base url and call function in html for iframe src.
                 */
                getBaseUrl: function () {

                    return url.build('onlinepayment/index/config');
                },

                /**
                 * Get the check3DS value from config.
                 */
                get3dsValue: function () {

                    return window.check3DS;
                },

                /**
                 * New card click function.
                 */
                iframe:function () {
                    $('#show_iframe').click(
                        function () {
                            $('#iframe_data').css("display", "block");
                            $('#cardstatuscheck').val(0);
                            $('#save_data').hide();
                            $('#back_save').css("display", "block");                      
                        }
                    );
                },

                 /**
                  * Get save card value.
                  */
                savedata:function () {
                    if($("input[type='checkbox'].radioBtnClass").is(':checked')==true) {
                        var card_type = $("input[type='checkbox'].radioBtnClass:checked").val();
                        $('#cardsavestatus').val(1);
                    } else {
                        $('#cardsavestatus').val(0);
                    }    
                },   
                
                /**
                 * Click on back to show save card details.
                 */
                saveDetails:function () {
                    $('#back_save').click(
                        function () {
                            $('#iframe_data').css("display", "none");
                            $('#cardstatuscheck').val(1);
                            $('#save_data').show();
                            $('#back_save').hide();                      
                        }
                    );
                },

                /**
                 * Get the save card token data.
                 */
                getSaveData: function () {
                    $('#save_data').css("display", "block");
                    var textData = "";
                    var data = window.checkoutConfig.verify;
                    if (data == 'No Data') {
                        $('#iframe_data').show();
                        $("#cardstatuscheck").val(0);
                        if ($("#cardstatuscheck").val()==0) {
                            $('#save_data').hide();
                        }

                        return false;
                    } else if (data.length) {
                        if (data.length >= 5){
                            $('#new_card').css("display", "none");
                        }else{
                            $('#new_card').css("display", "block");
                        }
                        textData = data;
                        var html = "";
                        data.forEach(
                            element => {
                                var cardType = element.cardType.toLowerCase();
                                var cardTypeName = element.cardType.charAt(0).toUpperCase() + cardType.slice(1);
                                if (parseInt(element.make_default)) {
                                    html += '<div><input name="tokeforpayment" value="'+element.tokenex.token+'" id="'+element.card.cardHolderName+','+element.tokenex.lastFour+','+element.tokenex.firstSix+','+element.card.expirationMonth+','+element.card.expirationYear+'" type="radio" checked="checked"/><span type="tel" class="cc-num '+cardType+' identified errorField" name="cc-num"></span> <span>'+cardTypeName+'</span> ending in '+element.tokenex.lastFour+'<span class="deflt-btn"> (Default)</span></div>';
                                } else {
                                    html += '<div><input name="tokeforpayment" value="'+element.tokenex.token+'" id="'+element.card.cardHolderName+','+element.tokenex.lastFour+','+element.tokenex.firstSix+','+element.card.expirationMonth+','+element.card.expirationYear+'" type="radio" /><span type="tel" class="cc-num '+cardType+' identified errorField" name="cc-num"></span> <span>'+cardTypeName+'</span> ending in '+element.tokenex.lastFour+'</div>';
                                }
                            }
                        );
                        $("#cardstatuscheck").val(1);
                        $('#show_iframe').show();
                        $('#responsefromajax').html(html);
                        
                    } else {
                        $("#cardstatuscheck").val(0);
                        $('#show_iframe').hide();
                        $('#iframe_data').show();
                    }
                },

                /**
                 * Popup for check3DS value true in Place order.
                 */
                placeOrder: function (data, event) {
                    var self = this;
                    var that = this;
                    if (event) {
                        event.preventDefault();
                    }

                    if (this.validate() 
                        && additionalValidators.validate() 
                        && this.isPlaceOrderActionAllowed() === true
                    ) {
                        if(window.check3DS == 'true') {
                            if (checkoutData.getSelectedPaymentMethod() == 'nexiogrouppayment') {

                                //with 3DS place a order with stored payment or else with a new card
                                if(parseInt($("#cardstatuscheck").val())) {
                                    var defaultToken = $('input[name="tokeforpayment"]:checked').val();
                                } else {
                                    var saveTokenEx = $("#saveCardtoken").val();
                                }
                                var additional_data = {
                                    isCreditCardExit: parseInt($("#cardstatuscheck").val()),
                                    isCardSave: parseInt($('#cardsavestatus').val()),
                                    defaultToken: defaultToken,
                                    saveTokenEx:saveTokenEx
                                };

                            }
                            setTimeout(
                                function () {
                                    $('.loading-mask').show(); }, 0
                            );
                            $.ajax(
                                {
                                    url: url.build('onlinepayment/index/threeds'),
                                    type: 'POST',
                                    dataType: 'json',
                                    showLoader: true,
                                    data: {'additional_data':additional_data},
                                    complete: function (response) {
                                        if(response.responseJSON.gatewayResponse != undefined  && response.responseJSON.gatewayResponse.status == 'declined' && response.responseJSON.error == '435') {
                                            var message = response.responseJSON.message;
                                            setTimeout(
                                                function () {
                                                    messageList.addErrorMessage({ message: $t(message) });    
                                                },1000
                                            );
                                            location.reload();
                                        
                                        } else if(response.responseJSON.kountResponse != undefined  && response.responseJSON.kountResponse.status == 'declined') {
                                            var message = response.responseJSON.message;
                                            setTimeout(
                                                function () {
                                                    messageList.addErrorMessage({ message: $t(message) });    
                                                },1000
                                            );
                                            location.reload();                                    

                                        } else if(response.responseJSON.error != undefined  && response.responseJSON.error == '438') {
                                            var message = response.responseJSON.message;
                                            setTimeout(
                                                function () {
                                                    messageList.addErrorMessage({ message: $t(message) });    
                                                },1000
                                            );
                                            location.reload();
                                        } else if(response.responseJSON.error != undefined  && response.responseJSON.error == '440') {
                                            var message = response.responseJSON.message;
                                            setTimeout(
                                                function () {
                                                    messageList.addErrorMessage({ message: $t(message) });    
                                                },1000
                                            );
                                            location.reload();

                                        } else if(response.responseJSON.error != undefined  && response.responseJSON.error == '436') {
                                            var message = response.responseJSON.message;
                                            setTimeout(
                                                function () {
                                                    messageList.addErrorMessage({ message: $t(message) });    
                                                },1000
                                            );
                                            location.reload();

                                        } else {
                                            var stores = response.responseJSON.redirectUrl;                        
                                            localStorage.removeItem("mage-cache-storage");
                                            $.mage.redirect(stores);
                                        }
                               
                                    },
                                    error: function (xhr, status, errorThrown) {
                                    
                                        console.error('Error happens. Try again.');
                                    }
                                }
                            );

                        } else {
                            that.isPlaceOrderActionAllowed(false);
            
                            that.getPlaceOrderDeferredObject()
                            .done(
                                function () {
                                    self.afterPlaceOrder();
                                    redirectOnSuccessAction.execute();
                                }
                            ).always(
                                function () {
                                    self.isPlaceOrderActionAllowed(true);
                                }
                            );
                        }

                        return false;
                    }
        
                    return false;   
                }
            }
        );
    }
);
