<?php
/**
 * Nexio Credit Card module Helper
 *
 * NOTICE OF LICENSE
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade nexio to newer
 * versions in the future. If you wish to customize nexio for your
 * needs please refer to Nexio for more information.
 *
 * PHP version 7.4
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */

namespace Nexio\OnlinePayment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Session;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\QuoteFactory;
use Nexio\OnlinePayment\Logger\Logger as NexioLogger;
use Magento\Framework\Data\Form\FormKey;
use Magento\Vault\Model\CreditCardTokenFactory;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Nexio\OnlinePayment\Model\NexioPaymentCustomToken;
use Nexio\OnlinePayment\Model\NexioLogFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\View\Asset\Repository;

/**
 * Class Data
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class Data extends AbstractHelper
{
    /**
     * The scope config interface for the current file.
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * The encryptor interface for the current file.
     *
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * The curl for the current file.
     *
     * @var Curl
     */
    protected $curl;

    /**
     * The user session for the current file.
     *
     * @var Session
     */
    protected $customer_Session;

    /**
     * Logger for system log.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Get Order data.
     *
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * Get Quote data.
     *
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * Get Store data.
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Variable to store nexio logs
     *
     * @var Logger
     */
    protected $nexioLogger;

    /**
     * Variable credit card token factory
     *
     * @var CreditCardTokenFactory
     */
    protected $creditCardTokenFactory;

    /**
     * Variable to payment Token
     *
     * @var PaymentTokenRepositoryInterface
     */
    protected $paymentTokenRepository;

    /**
     * Variable nexioLog factory
     *
     * @var NexioLogFactory
     */
    public $NexioLogFactory;

    /**
     * Cart
     *
     * @var Cart
     */
    protected $cart;


    protected $repository;
    
    /**
     * Constant variable for API username
     */
    const API_USERNAME = 'onlinepayment/credentials/username';

    /**
     * Constant variable for API password
     */
    const API_PASSWORD = 'onlinepayment/credentials/password';

    /**
     * Constant variable for API URL
     */
    const API_URL = 'onlinepayment/credentials/api_url';


    /**
     * Constant variable for Iframe height
     */
    const IFRAME_HEIGHT = 'onlinepayment/general/iframe_height';

    /**
     * Constant variable for Iframe width
     */
    const IFRAME_WIDTH = 'onlinepayment/general/iframe_width';


    /**
     * Constant variable for Sandbox public key
     */
    const PUBLIC_KEY = 'onlinepayment/general/public_key';

    /**
     * Constant variable for valid creds
     */
    const VALID_CREDS = 'onlinepayment/general/valid_creds';


    /**
     * Constant variable for isAuthOnly
     */
    const ISAUTHONLY = 'onlinepayment/processingoptions/isAuthOnly';

    /**
     * Constant variable for check3ds
     */
    const CHECK3DS = 'onlinepayment/processingoptions/check3ds';

    /**
     * Constant variable for checkFraud
     */
    const CHECKFRAUD = 'onlinepayment/processingoptions/checkFraud';

    /**
     * Constant variable for webhookFailUrl
     */
    const WEBHOOKFAILURL = 'onlinepayment/processingoptions/webhookFailUrl';

    /**
     * Constant variable for webhookUrl
     */
    const WEBHOOKURL = 'onlinepayment/processingoptions/webhookUrl';

    /**
     * Constant variable for merchant id
     */
    const MERCHANTID = 'onlinepayment/processingoptions/merchantid';

    /**
     * Constant variable for custom css
     */
    const CUSTOM_CSS = 'onlinepayment/general/custom_css';

    /**
     * Constant variable for custom text
     */
    const CUSTOM_TEXT = 'onlinepayment/general/custom_text';

    /**
     * Constant variable for hide billing
     */
    const HIDEBILLING = 'onlinepayment/processingoptions/hidebilling';

    /**
     * Constant variable for require cvc
     */
    const REQUIRE_CVC = 'onlinepayment/processingoptions/require_cvc';

    /**
     * Constant variable for hide cvc
     */
    const HIDE_CVC = 'onlinepayment/processingoptions/hide_cvc';

    /**
     * Construct params
     *
     * @param Session                         $customerSession        Login user.
     * @param Context                         $context                Helper context.
     * @param Config                          $scopeConfig            Configuration.
     * @param Encryption                      $encryptor              Encrypt.
     * @param Curl                            $curl                   Curl request.
     * @param LoggerInterface                 $logger                 For Log.
     * @param OrderFactory                    $orderFactory           For Order data.
     * @param QuoteFactory                    $quoteFactory           For Quote data.
     * @param StoreManagerInterface           $storeManager           For Store data.
     * @param Logger                          $nexioLogger            Logs.
     * @param FormKey                         $formKey                For formKey.
     * @param CreditCardTokenFactory          $creditCardTokenFactory Credit Factory.
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository Payment token.
     * @param NexioPaymentCustomToken         $nexiopaymenttoken      Nexio token.
     * @param NexioLogFactory                 $nexioanalytics         Analytics data.
     * @param Cart                            $cart                   Cart data.
     */
    public function __construct(
        Session                         $customerSession,
        Context                         $context,
        ScopeConfigInterface            $scopeConfig,
        EncryptorInterface              $encryptor,
        Curl                            $curl,
        LoggerInterface                 $logger,
        OrderFactory                    $orderFactory,
        QuoteFactory                    $quoteFactory,
        StoreManagerInterface           $storeManager,
        NexioLogger                     $nexioLogger,
        FormKey                         $formKey,
        CreditCardTokenFactory          $creditCardTokenFactory,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        NexioPaymentCustomToken         $nexiopaymenttoken,
        NexioLogFactory                 $nexioanalytics,
        Cart                            $cart,
        Repository                      $repository
    ) {

        $this->customer_Session       = $customerSession;
        $this->scopeConfig            = $scopeConfig;
        $this->encryptor              = $encryptor;
        $this->curl                   = $curl;
        $this->logger                 = $logger;
        $this->orderFactory           = $orderFactory;
        $this->quoteFactory           = $quoteFactory;
        $this->storeManager           = $storeManager;
        $this->nexioLogger            = $nexioLogger;
        $this->formKey                = $formKey;
        $this->creditCardTokenFactory = $creditCardTokenFactory;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->nexiopaymenttoken      = $nexiopaymenttoken;
        $this->nexioanalytics         = $nexioanalytics;
        $this->cart                   = $cart;
        $this->repository             = $repository;
        parent::__construct($context);
    }

    /**
     * Getting API username from config
     *
     * @return Username from config
     */
    public function getUsername()
    {
        try {
            
            return $this->scopeConfig
                ->getValue(self::API_USERNAME, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Getting API Password from config
     *
     * @return Password from config
     */
    public function getPassword()
    {
        try {
            $password = $this->scopeConfig
                ->getValue(self::API_PASSWORD, ScopeInterface::SCOPE_STORE);

            return $this->encryptor->decrypt($password);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Get the iframe height
     *
     * @return Iframe height from config
     */
    public function getHeight()
    {
        try {
            return $this->scopeConfig
                ->getValue(self::IFRAME_HEIGHT, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Get the iframe Width
     *
     * @return Iframe Width from config
     */
    public function getWidth()
    {
        try {

            return $this->scopeConfig
                ->getValue(self::IFRAME_WIDTH, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

     /**
      * Get the Base URL
      *
      * @return Base URL
      */
    public function getBaseUrl()
    {
        try {
            return $this->storeManager->getStore()->getBaseUrl();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }


     /**
      * Get the Vault URL
      *
      * @return Vault form URL
      */
    public function getVaultUrl()
    {
        try {
            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            $url     = 'onlinepayment/index/config';
            $formUrl =  $baseUrl.$url;
            return $formUrl;
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Get the API URL https://api.nexiopaysandbox.com/
     *
     * @return API URL from config
     */
    public function getApiUrl()
    {
        try {

            return $this->scopeConfig
                ->getValue(self::API_URL, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Get the VALID CREDS https://api.nexiopaysandbox.com/
     *
     * @return VALID CREDS from config
     */
    public function getValidCreds()
    {
        try {

            return $this->scopeConfig
                ->getValue(self::VALID_CREDS, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }


    /**
     * Getting Public key from config
     *
     * @return Public key from config
     */
    public function getSPublicKey()
    {
        try {

            return $this->scopeConfig
                ->getValue(self::PUBLIC_KEY, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Get the api authonly
     *
     * @return Api authonly from config
     */
    public function getisAuthOnly()
    {
        try {

            return $this->scopeConfig
                ->getValue(self::ISAUTHONLY, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }            

    }

    /**
     * Get the 3DS transaction value
     *
     * @return Api 3DS transaction value from config
     */
    public function getCheck3DS()
    {
        try {

            return $this->scopeConfig
                ->getValue(self::CHECK3DS, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }            

    }

    /**
     * Get the check fraud value
     *
     * @return Api check fraud value from config
     */
    public function getCheckFraud()
    {
        try {

            return $this->scopeConfig
                ->getValue(self::CHECKFRAUD, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }            

    }

    /**
     * Get the web hook fail url
     *
     * @return Web hook fail url from config
     */
    public function getWebHookFUrl()
    {
        try {

            return $this->scopeConfig
                ->getValue(self::WEBHOOKFAILURL, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }            

    }

    /**
     * Get the web hook url
     *
     * @return Web hook url from config
     */
    public function getWebHookUrl()
    {
        try {

            return $this->scopeConfig
                ->getValue(self::WEBHOOKURL, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }            

    }

    /**
     * Get the merchant id
     *
     * @return Merchant id
     */
    public function getMerchantId()
    {
        try {

            return $this->scopeConfig
                ->getValue(self::MERCHANTID, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }            

    }
    
    /**
     * Get the custom css
     *
     * @return Custom css url
     */
    public function getCustomCss()
    {
        try {

            return $this->scopeConfig
                ->getValue(self::CUSTOM_CSS, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }            

    }

    /**
     * Get the custom text
     *
     * @return Custom text
     */
    public function getCustomText()
    {
        try {

            return $this->scopeConfig
                ->getValue(self::CUSTOM_TEXT, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }            

    }

    /**
     * Get the hide billing
     *
     * @return Hide billing value
     */
    public function getHideBilling()
    {
        try {

            return $this->scopeConfig
                ->getValue(self::HIDEBILLING, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }            

    }
 
    /**
     * Get the require cvc
     *
     * @return Require cvc value
     */
    public function getRequireCvc()
    {
        try {

            return $this->scopeConfig
                ->getValue(self::REQUIRE_CVC, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }            

    }

    /**
     * Get the hide cvc
     *
     * @return Hide cvc value
     */
    public function getHideCvc()
    {
        try {

            return $this->scopeConfig
                ->getValue(self::HIDE_CVC, ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }            

    }
    /**
     * Get Current customer data
     *
     * @return Login customer id
     */
    public function getCustomer()
    {
        try {
            $customerSession   = $this->customer_Session->get();
            $customerData      = $this->customer_Session->getCustomer();
            $customer_login_ID = $this->customer_Session->getCustomer()->getId();
        
            return $customer_login_ID;
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Get Checkout quote id
     *
     * @param $quoteid Quote id
     *
     * @return Checkout quote id
     */
    public function getShippingaddress($quoteid)
    {
        try {
            
            return $this->quoteFactory->create()->load($quoteid);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Curl implementation to generate token and call to api
     *
     * @param $quoteid Quote id
     *
     * @return Curl implementation
     */
    public function curlRequest($quoteid)
    {
        $quote               = $this->getShippingaddress($quoteid);
        $customer_login_ID   = $this->getCustomer();
        $customer_address_id = $quote->getShippingAddress()->getCustomerId();
        $apiUrl               = $this->getApiUrl();
        $hideBilling         = $this->getHideBilling();
        $customCss           = $this->getCustomCss();
        if ( empty($customCss) ){
            $customCss = $this->repository->getUrl('Nexio_OnlinePayment::css/nexioDefault.css');
        }

        $requireCvc          = $this->getRequireCvc();
        $hideCvc             = $this->getHideCvc();
        $customText          = $this->getCustomText();

        $usename       = $this->getUsername('username');
        $password      = $this->getPassword('password');
        $authorization = base64_encode("$usename:$password");

        try {
            $phone        = $quote->getShippingAddress()->getTelephone();
            $first_name   = $quote->getShippingAddress()->getFirstname();
            $last_name    = $quote->getShippingAddress()->getLastname();
            $city_name    = $quote->getShippingAddress()->getCity();
            $state        = $quote->getShippingAddress()->getRegion();
            $postal       = $quote->getShippingAddress()->getPostcode();
            $country_name = $quote->getShippingAddress()->getCountryID();
            $email        = $quote->getShippingAddress()->getEmail();
            $address      = $quote->getShippingAddress()->getStreet();
            $address1     = $address[0];
            if (!empty($address[1])) {
                $address2 = $address[1];
            } else {
                $address2 = '';
            }
            if (!empty($address[2])) {
                $address3 = $address[2];
            } else {
                $address3 = '';
            }
            $address4 = $address2 . ' ' . $address3;
        
            $iframe_address_data = '{
                "data": {
                    "customer": {
                        "customerRef": "' . $customer_address_id . '",
                        "firstName": "' . $first_name . '",
                        "lastName": "' . $last_name . '",
                        "phone": "' . $phone . '",
                        "email": "' . $email . '",
                        "billToAddressOne": "' . $address1 . '",
                        "billToAddressTwo": "' . $address4 . '",
                        "billToCity": "' . $city_name . '",
                        "billToState": "' . $state . '",
                        "billToPostal": "' . $postal . '",
                        "billToCountry": "' . $country_name . '",
                        "billToPhone": "' . $phone . '",
                        "shipToAddressOne": "' . $address1 . '",
                        "shipToAddressTwo": "' . $address4 . '",
                        "shipToCity": "' . $city_name . '",
                        "shipToState": "' . $state . '",
                        "shipToPostal": "' . $postal . '",
                        "shipToCountry": "' . $country_name . '",
                        "shipToPhone": "' . $phone . '"
                    }
                },
                "uiOptions": {
                    "css": "'.$customCss.'",
                    "customTextUrl": "'.$customText.'",
                    "displaySubmitButton": false,
                    "hideBilling": '.$hideBilling.',
                    "hideCvc": '.$hideCvc.',
                    "requireCvc": '.$requireCvc.'
                }
            }
            ';

            $headers = [
                "Content-Type" => "application/json",
                "Content-Length" => strlen($iframe_address_data),
                "Authorization" => "Basic $authorization"
            ];

            $this->curl->setHeaders($headers);
            $this->curl->post($apiUrl."pay/v3/token", $iframe_address_data);
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $apiData = json_decode($this->curl->getBody(), true);
            $response = [];
            if (isset($apiData['token'])) {
                $response['success'] = $apiData['token'];
            } else {
                $response['error'] = $apiData['message'];
            }

            return $response;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'An error occurred on token.Please try to place the order again.'
                ), $e
            );
            $this->logger->info("One time token error".$e->getMessage());
        }
    }

    /**
     * Get Iframe data
     *
     * @param $quoteid Quote id
     *
     * @return Iframe form
     */
    public function saveCardTokenIframe($quoteid)
    {
        try {
            $tokenData  = $this->curlRequest($quoteid);
            $apiUrl = $this->getApiUrl();
        
            if (isset($tokenData['success'])) {
                $token      = $tokenData['success'];
                $iframe_api = $apiUrl.""."pay/v3/saveCard?token="."". $token;
               
                return $iframe_api;
            }

            if (isset($tokenData['error']) || empty($tokenData)) {
                
                return "Payemnt form is not loaded.Please check admin configuration";
            }
        } catch (\Exception $e) {
            
            $this->logger->info("CC form error".$e->getMessage());
        }
    }
    
    /**
     * Generate random string
     *
     * @param $length Length
     * 
     * @return Random string
     */
    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Run Transaction API request for sending order data to NexioGroup
     *
     * @param $transData Get the transcation data
     * @param $quoteid   Quote id
     *
     * @return Run Transaction implementation
     */
    public function runTransaction($transData, $quoteid)
    {
        $isAuthOnly     = $this->getisAuthOnly();
        $check3ds       = $this->getCheck3DS();
        $checkFraud     = $this->getCheckFraud();
        $webhookFailUrl = $this->getWebHookFUrl();
        $webhookUrl     = $this->getWebHookUrl();
        $apiUrl         = $this->getApiUrl();
        $base_url       = $this->getBaseUrl();
        $oneTimetoken   = $this->curlRequest($quoteid);
        $nexioCartItem  = $this->productdata();
        $usename        = $this->getUsername('username');
        $password       = $this->getPassword('password');
        $authorization  = base64_encode("$usename:$password");

        try {

            $isCreditCardExit = isset(
                $transData['cardData']['isCreditCardExit']
            )?$transData['cardData']['isCreditCardExit']:"";

            $isCardSave = isset(
                $transData['cardData']['isCardSave']
            )?$transData['cardData']['isCardSave']:"";

            $defaultPaymentToken = isset(
                $transData['cardData']['defaultToken']
            )?$transData['cardData']['defaultToken']:"";         
            
            if ($isCreditCardExit) {
                $cardTokenForTransaction = $defaultPaymentToken;        
            } else {
                $cardTokenForTransaction = isset(
                    $transData['cardData']['saveTokenEx']
                )?$transData['cardData']['saveTokenEx']:"";

                $headers = [
                    "Content-Type" => "application/json",
                    "Authorization" => "Basic $authorization"
                ];
    
                $this->curl->setHeaders($headers);
                $this->curl->get($apiUrl.'pay/v3/vault/card/'.$cardTokenForTransaction);
                $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
                $savedCardTokens = json_decode($this->curl->getBody(), true);

                if ($isCardSave) {

                    $cardType = $savedCardTokens['tokenex']['cardType'];
                    $cardCode = '';
                    if ($cardType == 'visa') {
                        $cardCode = 'VI';
                    } else if ($cardType == 'masterCard') {
                        $cardCode = 'MC';
                    } else if ($cardType == 'discover') {
                        $cardCode = 'DI';
                    } else if ($cardType == 'americanExpress') {
                        $cardCode = 'AE';
                    } else if ($cardType == 'jcb') {
                        $cardCode = 'JCB'; 
                    } else if ($cardType == 'unknown') {
                        $cardCode = 'unknown'; 
                    } else {
                        $cardCode = 'null';  
                    }                    
                    $paymentToken = $this->creditCardTokenFactory->create();
                    $paymentToken->setExpiresAt(
                        date('Y-m-d H:i:s', strtotime("+30 day"))
                    );
                    $paymentToken->setGatewayToken($savedCardTokens['tokenex']['token']);
                    $paymentToken->setDetails(
                        '{"type":"'.$cardCode.'","card":"{'.ucfirst($cardType).'}","maskedCC":"'.$savedCardTokens['tokenex']['lastFour'].'","cardHolderName":"'.$savedCardTokens['card']['cardHolderName'].'","expirationDate": "'.$savedCardTokens['card']['expirationMonth'].'/'.$savedCardTokens['card']['expirationYear'].'"}'
                    );
                 
                    $paymentToken->setIsActive(true);
                    $paymentToken->setIsVisible(true);
                    $customerId       = $this->customer_Session
                        ->getCustomer()->getId();
                    $vaultPaymentData = $this->nexiopaymenttoken
                        ->getByPublicCustomHash1($customerId);
                    if (!empty($vaultPaymentData)) {
                        $paymentToken->setMakeDefault(0);
                    } else {
                        $paymentToken->setMakeDefault(1);
                    }
                    $paymentToken->setType('card');
                    $paymentToken->setPaymentMethodCode('nexiogrouppayment');
                    $customerId = $this->customer_Session->getCustomer()->getId();
                    $paymentToken->setCustomerId($customerId);
                    $paymentToken->setPublicHash($this->generateRandomString($length = 10));
                    $this->paymentTokenRepository->save($paymentToken);
            
                }
            }            
            $runTransaction_request = '{
                "isAuthOnly": '.$isAuthOnly.', 
                "shouldUpdateCard": true,
                "data": {
                 "amount": "' . $transData['amount'] . '",
                  "currency": "' . $transData['currencyCode'] . '",
                  "cart": {
                    "items": [
                      {
                         "item": "'.$nexioCartItem['itemNumber'].'",
                          "description": "'.$nexioCartItem['productName'].'",
                          "quantity": '.$nexioCartItem['quantity'].',
                          "price": ' . $nexioCartItem['price'] . ',
                          "type": "sale"
                      }
                    ]
                  },
                  "customer": {
                    "invoice":"' . $transData['orderNumber'] . '",
                    "orderNumber":"' . $transData['orderNumber'] . '",
                    "customerRef":"' . $transData['customer_login_ID'] . '",
                    "firstName":"' . $transData['custFirsName'] . '",
                    "lastName":"' . $transData['custLastName'] . '",
                    "email": "' . $transData['email'] . '",
                    "phone": "' . $transData['phone'] . '",
                    "billToAddressOne": "' . $transData['address1'] . '",
                    "billToAddressTwo": "' . $transData['address4'] . '",
                    "billToCity": "' . $transData['city'] . '",
                    "billToState": "' . $transData['state'] . '",
                    "billToPostal": "' . $transData['postcode'] . '",
                    "billToCountry": "' . $transData['country'] . '",
                    "shipToAddressOne": "' . $transData['address1'] . '",
                    "shipToAddressTwo": "' . $transData['address4'] . '",
                    "shipToCity": "' . $transData['city'] . '",
                    "shipToCountry": "' . $transData['country'] . '",
                    "shipToPhone": "' . $transData['phone'] . '",
                    "shipToPostal": "' . $transData['postcode'] . '",
                    "shipToState": "' . $transData['state'] . '"
                  },
                  "lodging": {
                    "noShow": false,
                    "advanceDeposit": false,
                    "checkInDate": "",
                    "checkOutDate": "",
                    "roomNumber": "",
                    "roomRate": 0
                  }
                },
                "tokenex": {
                  "token":"'.$cardTokenForTransaction.'" 
                },
                "clientIp": null,
                "processingOptions": {
                  "check3ds": '.$check3ds.',
                  "checkFraud": '.$checkFraud.',
                  "customerRedirectUrl":"'.$base_url.'onlinepayment/index/order/",                      
                  "saveCardToken": true,
                  "shouldUseFingerprint": true,
                  "verboseResponse": true,
                  "webhookFailUrl": "'.$webhookFailUrl.'",
                  "webhookUrl": "'.$webhookUrl.'"
                }
            }';

            $headers = [
                "Content-Type" => "application/json",
                "Content-Length" => strlen($runTransaction_request),
                "Authorization" => "Basic $authorization"
            ];

            $this->curl->setHeaders($headers);
            $this->curl->post($apiUrl."pay/v3/process", $runTransaction_request);
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
            
            $transResponse = json_decode($this->curl->getBody(), true);
            
            $transactionData= $this->nexioanalytics->create();
            $transactionData->setOrderId($transData['orderNumber']);
            $transactionData->setEndpointInfomation($apiUrl."pay/v3/process");
            if (isset($transResponse["id"]) || $check3ds == true) {
                $transactionData->setApiResponse(json_encode($transResponse));
            }
            $transactionData->setTransactionHeader(json_encode($headers));
            $transactionData->setLogging();            
         
            $transactionData->save();
            
            if (isset($transResponse["gatewayResponse"]["status"]) && $transResponse["gatewayResponse"]["status"] == "declined") {
                $transactionData->setErrorHandling($transResponse["error"]);
                $transactionData->setApiResponse(strtok($transResponse["message"], '.'));
                $transactionData->save();
               
                return $transResponse;
            } else if (isset($transResponse["kountResponse"]["status"]) && $transResponse["kountResponse"]["status"] == "declined") {
                $transactionData->setErrorHandling($transResponse["error"]);
                $transactionData->setApiResponse(strtok($transResponse["message"], '.'));
                $transactionData->save();
                
                return $transResponse;
            } else if (isset($transResponse["error"]) && $transResponse["error"] == "438") {
                $transactionData->setErrorHandling($transResponse["error"]);
                $transactionData->setApiResponse($transResponse["message"]);
                $transactionData->save();
               
                return $transResponse;
            } else if (isset($transResponse["error"]) && $transResponse["error"] == "440") {
                $transactionData->setErrorHandling($transResponse["error"]);
                $transactionData->setApiResponse($transResponse["message"]);
                $transactionData->save();
            
                return $transResponse;
            } else if (isset($transResponse["error"]) && $transResponse["error"] == "436") {
                $transactionData->setErrorHandling($transResponse["error"]);
                $transactionData->setApiResponse($transResponse["message"]);
                $transactionData->save();
            
                return $transResponse;
            } else if (isset($transResponse["error"]) && $transResponse["error"] == "435") {
                $transactionData->setErrorHandling($transResponse["error"]);
                $transactionData->setApiResponse($transResponse["message"]);
                $transactionData->save();
            
                return $transResponse;                
            } else {
                
                return $transResponse;                
            }

        } catch (\Exception $e) {
            $this->logger->info("Run transaction response error".$e->getMessage());
        }
    }

    /**
     * Get the cart data and set in api request
     *
     * @return Cart data
     */
    public function productdata()
    {  
        $items = $this->cart->getQuote()->getAllItems();    
        
        $tempArray = [];  
        foreach ($items as $item) {
            $productName = $item->getName();           
            $quantity    = $item->getQty();    
            $itemNumber  = $item->getItemId(); 
            $productType = $item->getProductType();
            $price       = $item->getPrice();             
        }
        
        $nexioCartItem  = array(
            "productName" => $productName,
            "quantity" => $quantity,
            "itemNumber" => $itemNumber,
            "product_id" => $itemNumber,
            "qty" => $quantity,
            "productType" => $productType,
            "price"=>$price
        );

        return $nexioCartItem;
    }

    /**
     * Get save card token and show details on checkout
     *
     * @return stored card details
     */
    public function getSaveCardToken()
    {
       
        $usename          = $this->getUsername('username');
        $password         = $this->getPassword('password');
        $apiUrl           = $this->getApiUrl();
        $authorization    = base64_encode("$usename:$password");
        $customerId       = $this->customer_Session->getCustomer()->getId();
        //get save token from vault payment table
        $vaultPaymentData = $this->nexiopaymenttoken
            ->getByPublicCustomHash($customerId);
        if (!empty($vaultPaymentData)) {
            $response=array();
            foreach ($vaultPaymentData as $item) {
                $cardToken   = json_encode($item['gateway_token']);
                $tokenOfCard =  str_replace('"', '', $cardToken);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apiUrl.'pay/v3/vault/card/'.$tokenOfCard);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        
                $headers   = array();
                $headers[] = 'Accept: application/json';
                $headers[] = 'Authorization: Basic '.$authorization.'';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
                $response                 = curl_exec($ch);
                $dataCore                 = json_decode($response, true);
                $dataCore["make_default"] = $item["make_default"];
                $dataCore["cardType"]     = substr(
                    json_decode($item["details"], true)["card"], 1, -1
                );
                $finalRespone[] = $dataCore;
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                }
                curl_close($ch);      
            } 
            return $finalRespone; 
        } else {
            echo 'No Data';
        }       
    }
}
