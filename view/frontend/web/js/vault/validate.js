define(
    [
        'jquery',
        'mage/url'
    ],
    function (
        $,
        url
    ) {
        'use strict';
        var indexCount = 0;
            return {                
                validate: function () {                           
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
                                $('body').trigger('processStart');
                            }
                            if (event.data.event != undefined && event.data.event === "error") {
                                $("#cardsavebutton").hide();
                            }
                            if (event.data.event != undefined && event.data.event != "formValidations" && event.data.event != "submit" && event.data.event === "cardSaved") {

                                $('#iframe_data').addClass("nexio");
                                if ($('#iframe_data').hasClass("nexio") && !indexCount) {
                                    indexCount++;
                                    $('body').trigger('processStart');
                                    if(indexCount == 1) {
                                        var savedCardToken = event.data.data.token.token;
                                        var additional_data = {                                    
                                            savedCardToken:savedCardToken
                                        };
                                        $.ajax(
                                            {
                                                url: url.build('onlinepayment/index/vaultdata'),
                                                type: 'POST',
                                                dataType: 'json',
                                                data: {'additional_data':additional_data},
                                                complete: function (response) {
                                                    $('body').trigger('processStop');
                                                    $('.action-close').trigger('click');
                                                    $('body').trigger('processStart');
                                                    location.reload();
                                                },                        
                                                error: function (xhr, status, errorThrown) {
                                                            
                                                    console.log('Error happens. Try again.');
                                                    location.reload();
                                                    $('body').trigger('processStop');
                                                }  
                                                }
                                        );
                                    }
                                }
                            }
                            }
                        );
                        var setUrl = url.build('onlinepayment/index/config');
                        iframe.contentWindow.postMessage('posted', setUrl);

                        return false;
                    } else {
                        return true;
                    }
                }
        };
    },
);
