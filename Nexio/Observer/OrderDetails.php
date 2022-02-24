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
use Magento\Quote\Model\QuoteRepository;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order;

use Magento\Sales\Model\Order\Payment\TransactionFactory;

/**
 * Class OrderDetails
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class OrderDetails implements ObserverInterface
{

    /**
     * Get the quote data.
     *
     * @var QuoteRepository
     */
    protected $quoteRepository;   

    /**
     * Logger for system log.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Order data.
     *
     * @var Order
     */
    protected $order;
   
    /**
     * Transaction data.
     *
     * @var TransactionFactory
     */
    protected $transaction;

    /**
     * Construct params
     *
     * @param QuoteRepository    $quoteRepository For quote data.   
     * @param LoggerInterface    $logger          For Log.
     * @param Order              $order           For order.
     * @param TransactionFactory $transaction     For transaction.
     */
    public function __construct(             
        QuoteRepository    $quoteRepository, 
        LoggerInterface    $logger,
        Order              $order,       
        TransactionFactory $transaction
    ) {
           
        $this->quoteRepository = $quoteRepository;
        $this->logger          = $logger;
        $this->order           = $order;
        $this->transaction     = $transaction;
    }

    /**
     * Getting order data
     *
     * @param Observer $observer Observer for get order data
     *
     * @return Save transaction data for backend in sales order payment
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $orderId      = $observer->getEvent()->getOrderIds();
        $order        = $this->order->load($orderId);
        $entityId     = $order->getPayment()->getEntityId();
        $parentId     = $order->getPayment()->getParentId();     
        $quote        = $this->quoteRepository->get($order->getQuoteId());
        $paymentQuote = $quote->getPayment();
        $method       = $paymentQuote->getMethodInstance()->getCode();
        $addData      =  $paymentQuote->getAdditionalInformation();
        $transaction  = $this->transaction->create();
        $transaction->setOrderId($parentId);
        $transaction->setPaymentId($entityId);
        $transaction->setData('additional_information', $addData);      
        $transaction->save();        
    }   
}
