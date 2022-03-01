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

use Nexio\OnlinePayment\Helper\Data;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Directory\Model\Currency;
use Psr\Log\LoggerInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
Use Magento\Sales\Api\OrderManagementInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class CancelOrderService
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class CancelOrderService implements ObserverInterface
{
    /**
     * Helper Data
     *
     * @var Data
     */
    protected $helperData;

    /**
     * Currency
     *
     * @var Currency
     */
    protected $currency;

    /**
     * Logger for system log.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * For order cancel.
     *
     * @var Order
     */
    protected $order;

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
     * The curl for the current file.
     *
     * @var Curl
     */
    protected $curl;

    /**
     * Variable to show message
     *
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Constructor params
     *
     * @param Data                         $helperData      Data
     * @param OrderItemRepositoryInterface $orderItem       OrderItem
     * @param Currency                     $currency        Currency
     * @param LoggerInterface              $logger          Log.
     * @param Order                        $order           Order data.
     * @param OrderManagementInterface     $orderManagement Order management.
     * @param QuoteRepository              $quoteRepository Quote repository data.
     * @param QuoteFactory                 $quoteData       For quote data.
     * @param Curl                         $curl            Curl request.
     * @param ManagerInterface             $messageManager  For message.
     */
    public function __construct(
        Data                         $helperData,
        OrderItemRepositoryInterface $orderItem,
        Currency                     $currency,
        LoggerInterface              $logger,
        Order                        $order,
        OrderManagementInterface     $orderManagement,
        QuoteRepository              $quoteRepository,
        QuoteFactory                 $quoteData,
        Curl                         $curl,
        ManagerInterface             $messageManager
    ) {
        $this->helperData      = $helperData;
        $this->orderItem       = $orderItem;
        $this->currency        = $currency;
        $this->logger          = $logger;
        $this->order           = $order;
        $this->orderManagement = $orderManagement;
        $this->quoteRepository = $quoteRepository;
        $this->quoteData       = $quoteData;
        $this->curl            = $curl;
        $this->messageManager  = $messageManager;
       
    }

    /**
     * Getting order amount data
     *
     * @param Observer $observer Observer for placed order data
     *
     * @return Order refund Data
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $order = $observer->getEvent()->getOrder();

        $usename       = $this->helperData->getUsername('username');
        $password      = $this->helperData->getPassword('password');
        $authorization = base64_encode("$usename:$password");
        $apiUrl        = $this->helperData->getApiUrl();

        $orderNumber  = $order->getIncrementId();
        $state        = $order->getState();    
        $amount       = $order->getGrandTotal();
        $quote        = $this->quoteRepository->get($order->getQuoteId());
        $paymentQuote = $quote->getPayment();
        $quoteid      = $order->getQuoteId();
        $quotedata    = $this->quoteData->create()
            ->load($quoteid);
        $method       = $quotedata->getPayment()->getMethod(); 
        $transId      = $quotedata->getPayment()->getTransactionId();
       
        if ($method == 'nexiogrouppayment' && $state == 'canceled') {
            
            $refundRequest = '{
                "id": "'.$transId.'",
                "data": {
                "amount": '.$amount.'
                }
            }';

            $headers = [
                "Content-Type" => "application/json",
                "Content-Length" => strlen($refundRequest),
                "Authorization" => "Basic $authorization"
            ];
            
            $this->curl->setHeaders($headers);
            $this->curl->post($apiUrl.'pay/v3/refund', $refundRequest);
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
            
            $refundResponse = json_decode($this->curl->getBody(), true);

            if (isset($refundResponse["gatewayResponse"]["status"])=="error") {
                $message = __($refundResponse["message"]);
                throw new CouldNotSaveException(__($message));

                return false;  
            } else if (isset($refundResponse["error"]) && $refundResponse["error"] == "436") {
                $message = __($refundResponse["message"]);
                throw new CouldNotSaveException(__($message));

                return false;
            } else {

                return  $refundResponse;
            }
        }
    }
}
