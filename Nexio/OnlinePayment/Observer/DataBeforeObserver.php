<?php
/**
 * Nexio Credit Card module Observer
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

namespace Nexio\OnlinePayment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Nexio\OnlinePayment\Helper\Data;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\QuoteFactory;
use Magento\OfflinePayments\Model\Banktransfer;
use Psr\Log\LoggerInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\UrlInterface;
use Magento\Webapi\Controller\Rest\InputParamsResolver;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Checkout\Model\Session;

/**
 * Class DataBeforeObserver
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class DataBeforeObserver implements ObserverInterface
{
    /**
     * Get the helper data.
     *
     * @var Data
     */
    protected $helperData;

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
     * Logger for system log.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Variable to show message
     *
     * @var ManagerInterface
     */
    protected $messageManager;

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
     * Variable for Input Params Resolver.
     *
     * @var InputParamsResolver
     */
    protected $inputParamsResolver;
    
    /**
     * Variable for State.
     *
     * @var State
     */
    protected $state;

    /**
     * Variable for session.
     *
     * @var Session
     */
    protected $checkoutSession;

    /**
     * Construct params
     *
     * @param Data                $helperData          For helper Data.
     * @param QuoteRepository     $quoteRepository     For quote data.
     * @param QuoteFactory        $quoteData           For load quote data by ID.
     * @param LoggerInterface     $logger              For Log.
     * @param ManagerInterface    $messageManager      For message.
     * @param ResponseFactory     $responseFactory     For response.
     * @param UrlInterface        $url                 For url.
     * @param InputParamsResolver $inputParamsResolver For Retrieving input data.
     * @param State               $state               For Sate code.
     * @param Session             $checkoutSession     For checkout session
     */
    public function __construct(
        Data                $helperData,
        QuoteRepository     $quoteRepository,
        QuoteFactory        $quoteData,
        LoggerInterface     $logger,
        ManagerInterface    $messageManager,
        ResponseFactory     $responseFactory,
        UrlInterface        $url,
        InputParamsResolver $inputParamsResolver, 
        State               $state,
        Session $checkoutSession
    ) {
        $this->helperData          = $helperData;
        $this->quoteRepository     = $quoteRepository;
        $this->quoteData           = $quoteData;
        $this->logger              = $logger;
        $this->messageManager      = $messageManager;
        $this->responseFactory     = $responseFactory;
        $this->url                 = $url;
        $this->inputParamsResolver = $inputParamsResolver;       
        $this->state               = $state;
        $this->checkoutSession = $checkoutSession;
      
    }

    /**
     * Place an order
     *
     * @param Observer $observer Observer for get address and order data
     *
     * @return Order Data
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        try {            
            $customer_login_ID = $this->helperData->getCustomer();
            $order             = $observer->getEvent()->getOrder();
            $quote             = $this->quoteRepository->get($order->getQuoteId());
            $paymentQuote      = $quote->getPayment();
            $method            = $paymentQuote->getMethodInstance()->getCode();
            $get3DS            = $this->helperData->getCheck3DS();

            if ($get3DS == 'false') {               
                if ($method == 'nexiogrouppayment') {
                    $amount          = $order->getGrandTotal();
                    $orderNumber     = $order->getIncrementId();
                    $shippingAddress = $order->getShippingAddress();
                    $state           = $shippingAddress->getRegion();
                    $custFirsName    = $shippingAddress->getFirstname();
                    $custLastName    = $shippingAddress->getLastname();
                    $email           = $shippingAddress->getEmail();
                    $phone           = $shippingAddress->getTelephone();
                    $currencyCode    = $order->getOrderCurrencyCode();
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

                    $quoteid               = $order->getQuoteId();
                    $quotedata             = $this->quoteData->create()
                        ->load($quoteid);
                    $additionalInformation = $quotedata->getPayment()
                        ->getAdditionalInformation();
                    $inputParams = $this->inputParamsResolver->resolve();
                    foreach ($inputParams as $inputParam) {
                        if ($inputParam instanceof \Magento\Quote\Model\Quote\Payment) {
                            $paymentData = $inputParam->getData('additional_data');                                                
                        }
                    }                
                    $additionalInformation=$paymentData;                

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
                        'cardData' => $paymentData
                    );
                    $ccData = $this->helperData
                        ->runTransaction($getTransactionData, $quoteid);

                    if (isset($ccData["gatewayResponse"]["status"]) && $ccData["gatewayResponse"]["status"] == "declined") {
                        $message = __($ccData["message"]);
                        $this->messageManager
                            ->addErrorMessage($message);
                            
                        exit;
                    } else if (isset($ccData["kountResponse"]["status"]) && $ccData["kountResponse"]["status"] == "declined") {
                        $message = __($ccData["message"]);
                        $this->messageManager
                            ->addErrorMessage($message);

                        return $this->_goBack($this->url->getUrl('checkout/cart'));
                    } else if (isset($ccData["error"]) && $ccData["error"] == "438") {
                        $this->messageManager
                            ->addErrorMessage($message);

                        exit;
                    } else if (isset($ccData["error"]) && $ccData["error"] == "440") {
                        $message = __($ccData["message"]);
                        $this->messageManager
                            ->addErrorMessage($message);

                        exit;
                    } else if (isset($ccData["error"]) && $ccData["error"] == "436") {
                        $message = __($ccData["message"]);
                        $this->messageManager
                            ->addErrorMessage($message);

                        exit;
                    } else if (isset($ccData["error"]) && $ccData["error"] == "435") {
                        $message = __($ccData["message"]);
                        $this->messageManager
                            ->addErrorMessage($message);

                        exit;
                    } else {
                        $customColumnValue = $ccData["id"];
                        $paymentQuote->setAdditionalData($customColumnValue);
                        $paymentQuote->save();
                        $paymentQuote->setTransactionId($customColumnValue);
                        $paymentQuote->save(); 
                    }            
                }
            }
        } catch (\Exception $e) {
            $this->logger->info("Place order error".$e->getMessage());
        }
    }
}
