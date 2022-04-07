<?php
namespace Nexio\OnlinePayment\Controller\Index;

class AjaxValidateNexioCreds
{
    public static function validateNexioCreds($userName, $password, $apiURL, $curl)
    {
        $curl->setCredentials($userName, $password);
        $curl->get($apiURL."user/v3/account/whoAmI");
        $response = $curl->getBody();
        $responseJSON = json_decode($response);
        if ( empty($responseJSON) || !empty($responseJSON->message) ){
            throw new \Exception($responseJSON->message);
        }

        if ( $responseJSON->enabled != 1 ){
            throw new \Exception("Account is not enabled");
        }else{
            $areCredsValid = true;
        }

        $isEnabledForAccountUpdater = $responseJSON->isEnabledForAccountUpdater;
        $isEnabledForCybersourceAccountUpdater = $responseJSON->isEnabledForCybersourceAccountUpdater;

        if ( $isEnabledForAccountUpdater || $isEnabledForCybersourceAccountUpdater ){
            $isEnabledForAccountUpdater = true;
        }


        $curl->get($apiURL."merchant/v3");
        $response = $curl->getBody();
        $responseJSON = json_decode($response);
        if ( !empty($responseJSON) && !empty($responseJSON[0]->kount) && !empty($responseJSON[0]->kount->id )){
            $isFraudCheckEnabled = true;
        }

        $responseJSON['are_creds_valid'] =  $areCredsValid;
        $responseJSON['isEnabledForAccountUpdater'] =  $isEnabledForAccountUpdater;
        $responseJSON['isFraudCheckEnabled'] =  $isFraudCheckEnabled;

        return $responseJSON;
    }
}
