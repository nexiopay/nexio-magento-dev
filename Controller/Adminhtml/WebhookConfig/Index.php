<?php
/**
 * Nexio Credit Card module Controller
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

namespace Nexio\OnlinePayment\Controller\Adminhtml\WebhookConfig;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Nexio\OnlinePayment\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\Client\Curl;

/**
 * Class Index
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class Index extends Action
{

    /**
     * Constant variable for scope
     */
    const SCOPE_TYPE_DEFAULT = 'default';

    /**
     * Get the helper data.
     *
     * @var Data
     */
    protected $helperData;

    /**
     * Json factory
     * 
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Logger for system log
     * 
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * The curl for the current file.
     *
     * @var Curl
     */
    protected $curl;

    /**
     * Construct params
     *
     * @param Context               $context           Helper context.
     * @param Data                  $helperData        Helper Data.
     * @param StoreManagerInterface $store             Store.
     * @param WriterInterface       $configWriter      Config writer.
     * @param ScopeConfigInterface  $config            For scope config.
     * @param JsonFactory           $resultJsonFactory For json factory.
     * @param LoggerInterface       $logger            For logger.
     * @param Curl                  $curl              Curl request.
     */
    public function __construct(
        Context               $context,
        Data                  $helperData,
        StoreManagerInterface $store,
        WriterInterface       $configWriter,
        ScopeConfigInterface  $config,
        JsonFactory           $resultJsonFactory,
        LoggerInterface       $logger,
        Curl                  $curl
    ) {
        parent::__construct($context);
        $this->helperData        = $helperData;
        $this->store             = $store;
        $this->configWriter      = $configWriter;       
        $this->config            = $config;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger            = $logger;
        $this->curl              = $curl;
    }

    /**
     * Configure webhooks with merchant id
     * 
     * @return Webhook config
     */
    public function execute()
    {   

        $result                = $this->resultJsonFactory->create();
        $customerApprovedRoute = $this->store->getStore()->getBaseUrl().'onlinepayment/index/webhook';
        
        $scopeId = $this->store->getStore()->getStoreId();
        $this->configWriter->save(
            'onlinepayment/webhookconfig/webhookurlconfig', 
            $customerApprovedRoute, 
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 
            $scopeId = 0
        );
        
        $usename       = $this->helperData->getUsername('username');
        $password      = $this->helperData->getPassword('password');
        $authorization = base64_encode("$usename:$password");
        $apiUrl        =  $this->helperData->getApiUrl();
        $merchantID    = $this->helperData->getMerchantId();

        //Webhook services configure merchant
        $merchantWebhookReq = '{
            "merchantId": "'.$merchantID.'",
            "webhooks": {
                "CARD_SAVED": {
                    "url": "'.$customerApprovedRoute.'"
                },
                "TRANSACTION_AUTHORIZED": {
                    "url": "'.$customerApprovedRoute.'"
                },
                "TRANSACTION_REFUNDED": {
                    "url": "'.$customerApprovedRoute.'"
                },
                "TRANSACTION_CAPTURED": {
                    "url": "'.$customerApprovedRoute.'"
                },
                "TRANSACTION_SETTLED": {
                    "url": "'.$customerApprovedRoute.'"
                },
                "TRANSACTION_VOIDED": {
                    "url": "'.$customerApprovedRoute.'"
                },
                "TRANSACTION_PENDING": {
                    "url": "'.$customerApprovedRoute.'"
                }    
            }
        }';
        $headers = [
            "Content-Type" => "application/json",
            "Content-Length" => strlen($merchantWebhookReq),
            "Authorization" => "Basic $authorization"
        ];

        $this->curl->setHeaders($headers);
        $this->curl->post($apiUrl.'webhook/v3/config', $merchantWebhookReq);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $merchantWebhookRes = json_decode($this->curl->getBody(), true);
        if (isset($merchantWebhookRes['message']) &&  $merchantWebhookRes['message'] == "Unauthorized") {
            $unauth = 'error';

            return $result->setData(['message' => $unauth]);  
        }

        $response = [];
        if (isset($merchantWebhookRes['merchantId'])) {
            $response['success'] = $merchantWebhookRes['merchantId'];

        } else {
            $response['error'] = $merchantWebhookRes['message'];

            return $result->setData(
                [
                    'message' => $merchantWebhookRes['message'],
                    'error' => $merchantWebhookRes['error']                
                ]
            );
        }
        
        $headers = [
            "Content-Type" => "application/json",
            "Authorization" => "Basic $authorization"
        ];

        $this->curl->setHeaders($headers);
        $this->curl->get($apiUrl.'webhook/v3/config/'.$merchantID);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $merchantResponse = json_decode($this->curl->getBody(), true);
        $configResponse = [];
        if (isset($merchantResponse['merchantId'])) {
            $configResponse['success'] = $merchantResponse['merchantId'];
            $messageResponse = 'success';

            return $result->setData(['message' => $messageResponse]);          
        } else {
            $configResponse['error'] = $merchantResponse['message'];

            return $result->setData(
                [
                    'message' => $merchantResponse['message'],
                    'error' => $merchantResponse['error']
                ]
            );
        }
    }
}