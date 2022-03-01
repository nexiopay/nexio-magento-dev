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

namespace Nexio\OnlinePayment\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\QuoteFactory;    
use Magento\Checkout\Model\Session as CheckoutSession;
use Nexio\OnlinePayment\Helper\Data;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class ThreeDs
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class ThreeDs extends \Magento\Framework\App\Action\Action
{
    /**
     * Get the quote data.
     *
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Load quote data by ID.
     *
     * @var QuoteFactory
     */
    protected $quoteData;
    
    /**
     * Get checkout session data
     * 
     * @var CheckoutSession
     */
    private $_checkoutSession;

    /**
     * Get the helper data.
     *
     * @var Data
     */
    protected $helperData;
    
    /**
     * Get http data
     * 
     * @var Http
     */
    protected $request;

    /**
     * Get store data
     * 
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Logger for system log.
     * 
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Variable for response factory
     *
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * Variable for getting url
     *
     * @var UrlInterface
     */
    protected $url;   

    /**
     * Variable to show message
     *
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Construct params
     *
     * @param Context               $context         Helper context.
     * @param Data                  $jsonHelper      Json helper.
     * @param QuoteRepository       $quoteRepository For quote data.
     * @param QuoteFactory          $quoteData       For load quote data by ID.
     * @param Data                  $helperData      For helper Data.
     * @param Http                  $request         For http request.
     * @param StoreManagerInterface $storeManager    For store manager.
     * @param LoggerInterface       $logger          For Log.
     * @param CheckoutSession       $checkoutSession For checkout session.
     * @param PageFactory           $resultFactory   For result factory.
     * @param ResponseFactory       $responseFactory For response factory.
     * @param UrlInterface          $url             For url. 
     * @param ManagerInterface      $messageManager  For message.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context      $context,
        \Magento\Framework\Json\Helper\Data        $jsonHelper,
        QuoteRepository                            $quoteRepository,
        QuoteFactory                               $quoteData,
        Data                                       $helperData,
        \Magento\Framework\App\Request\Http        $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface                   $logger,
        CheckoutSession                            $checkoutSession,
        \Magento\Framework\View\Result\PageFactory $resultFactory,
        \Magento\Framework\App\ResponseFactory     $responseFactory,
        \Magento\Framework\UrlInterface            $url,
        ManagerInterface                           $messageManager
    ) {
        $this->jsonHelper       = $jsonHelper;
        $this->quoteRepository  = $quoteRepository;
        $this->quoteData        = $quoteData;
        $this->_checkoutSession = $checkoutSession;
        $this->helperData       = $helperData;
        $this->storeManager     = $storeManager;
        $this->request          = $request;
        $this->logger           = $logger;
        $this->resultFactory    = $resultFactory;
        $this->responseFactory  = $responseFactory;
        $this->_url             = $url;
        $this->messageManager   = $messageManager;
        parent::__construct($context);
    }

    /**
     * Getting order data from cart
     *
     * @return Order Data
     */
    public function execute()
    {
        /** 
         * Create result page 
         *
         * @var \Magento\Framework\View\Result\Page $resultPage For result page
         */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        /**
         * Get raw data 
         * 
         * @var \Magento\Framework\Controller\Result\Raw $response For raw result
         */
        $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $response->setHeader('Content-type', 'text/plain');
     
        $quoteId      = (int)$this->_checkoutSession->getQuote()->getId();
        $quote        = $this->quoteRepository->get($quoteId);
        $paymentQuote = $quote->getPayment();

        $additionalInformation = $this->request->getParam('additional_data'); 
        $paymentQuote->setAdditionalInformation($additionalInformation);

        $customer_login_ID = $this->helperData->getCustomer();

        $quote->reserveOrderId()->save();
        $amount          = $quote->getGrandTotal();
        $orderNumber     = $quote->getReservedOrderId();
        $shippingAddress = $quote->getShippingAddress();
        $state           = $shippingAddress->getRegion();
        $custFirsName    = $shippingAddress->getFirstname();
        $custLastName    = $shippingAddress->getLastname();
        $email           = $shippingAddress->getEmail();
        $phone           = $shippingAddress->getTelephone();
        $currencyCode    = $this->storeManager
            ->getStore()->getCurrentCurrency()->getCode();
        $postcode        = $shippingAddress->getPostcode();
        $city            = $shippingAddress->getCity();
        $country         = $shippingAddress->getCountryID();
        $address         = $shippingAddress->getStreet();
        $address1        = $address[0];
    
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

        $quotedata             = $this->quoteData->create()
            ->load($quoteId);                    

        $getTransactionData = array(
            "customer_login_ID" => $customer_login_ID,
            "amount" => $amount,
            "orderNumber" => $orderNumber,
            "state" => $state,
            "custFirsName" => $custFirsName,
            "custLastName" => $custLastName,
            "email" => $email,
            "phone" => $phone,
            "currencyCode" => $currencyCode,
            "country" => $country,
            "address1" => $address1,
            "address4" => $address4,
            "postcode" => $postcode,
            "city" => $city,
            'cardData' => $additionalInformation
        );

        $ccData = $this->helperData
            ->runTransaction($getTransactionData, $quoteId);

        if (isset($ccData['kountResponse']['status']) && $ccData['kountResponse']['status'] == 'declined') {

            $response->setContents(
                $this->jsonHelper->jsonEncode(
                    $ccData
                )
            );

            return $response;           
        } 
        if (isset($ccData['gatewayResponse']['status']) && $ccData['gatewayResponse']['status'] == 'declined') {
            
            $response->setContents(
                $this->jsonHelper->jsonEncode(
                    $ccData
                )
            );

            return $response;           
        } 
        if (isset($ccData['error']) && $ccData['error'] == '438') {
            
            $response->setContents(
                $this->jsonHelper->jsonEncode(
                    $ccData
                )
            );

            return $response;           
        } 
        if (isset($ccData['error']) && $ccData["error"] == '435') {

            $response->setContents(
                $this->jsonHelper->jsonEncode(
                    $ccData
                )
            );

            return $response;
        }
        if (isset($ccData['error']) && $ccData['error'] == '440') {
            
            $response->setContents(
                $this->jsonHelper->jsonEncode(
                    $ccData
                )
            );

            return $response;           
        } if (isset($ccData['error']) && $ccData['error'] == '436') {
            
            $response->setContents(
                $this->jsonHelper->jsonEncode(
                    $ccData
                )
            );

            return $response;           
        } else {        
            $this->logger->info("CC DATA controller".json_encode($ccData));  
            $response->setContents(
                $this->jsonHelper->jsonEncode(
                    $ccData
                )
            );                
            
            return $response;
        }
    } 
}
