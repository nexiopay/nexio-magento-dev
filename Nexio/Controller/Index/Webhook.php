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

namespace Nexio\OnlinePayment\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Service\CreditmemoService;

/**
 * Class Webhook
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class Webhook extends Action
{

    /**
     * Get http data
     * 
     * @var Http
     */
    protected $request;

    /**
     * Logger for system log
     * 
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Order repository
     * 
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Invoice service
     * 
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * Invoice sender
     * 
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * Transactions
     * 
     * @var Transaction
     */
    protected $transaction;

    /**
     * Order
     * 
     * @var Order
     */
    protected $order;

    /**
     * Order management interface
     * 
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * Invoice
     * 
     * @var Invoice
     */
    protected $invoice;

    /**
     * Credit memo factory
     * 
     * @var CreditmemoFactory
     */
    protected $creditmemofactory;

    /**
     * Credit memo service
     * 
     * @var CreditmemoService
     */
    protected $creditmemoservice;

    /**
     * Construct params
     *
     * @param Context                  $context           Access to all objects.
     * @param PageFactory              $resultPageFactory Result page factory.
     * @param Http                     $request           For request.
     * @param LoggerInterface          $logger            For logs.
     * @param OrderRepositoryInterface $orderRepository   Order repository.
     * @param InvoiceService           $invoiceService    Invoice service.
     * @param InvoiceSender            $invoiceSender     Invoice sender.
     * @param Transaction              $transaction       For transactions.
     * @param Order                    $order             For order.
     * @param OrderManagementInterface $orderManagement   For order management.
     * @param Invoice                  $invoice           For invoice.
     * @param CreditmemoFactory        $creditmemofactory For credit memo factory.
     * @param CreditmemoService        $creditmemoservice For credit memo service.
     */
    public function __construct(
        Context                             $context,
        PageFactory                         $resultPageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Psr\Log\LoggerInterface            $logger,
        OrderRepositoryInterface            $orderRepository,
        InvoiceService                      $invoiceService,
        InvoiceSender                       $invoiceSender,
        Transaction                         $transaction,
        Order                               $order,
        OrderManagementInterface            $orderManagement,
        Invoice                             $invoice,
        CreditmemoFactory                   $creditmemofactory,
        CreditmemoService                   $creditmemoservice
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->request           = $request;
        $this->logger            = $logger;
        $this->orderRepository   = $orderRepository;
        $this->invoiceService    = $invoiceService;
        $this->transaction       = $transaction;
        $this->invoiceSender     = $invoiceSender;
        $this->order             = $order;
        $this->orderManagement   = $orderManagement;
        $this->invoice           = $invoice;
        $this->creditmemofactory = $creditmemofactory;
        $this->creditmemoservice = $creditmemoservice;
        parent::__construct($context);
    }

    /**
     * Function to get webhook resppnse 
     *
     * @return order status
     */
    public function execute()
    {

        $webhookData = json_decode(file_get_contents('php://input'), true);
        $this->logger->info("Webhook Data".json_encode($webhookData));
        
        /**
         * TRANSACTION_AUTHORIZED Declined
         */
        if (isset($webhookData['data']['error']) && $webhookData['data']['error'] == "435") {

            return false;
        }

        /**
         * TRANSACTION_AUTHORIZED kount declined
         */
        if (isset($webhookData['data']['error']) && $webhookData['data']['error'] == "431") {

            return false;
        }

        /**
         * TRANSACTION_AUTHORIZED
         */
        if (isset($webhookData['eventType']) && $webhookData['eventType'] == "TRANSACTION_AUTHORIZED") {          
            $incrementId = $webhookData['data']['data']['customer']['orderNumber'];
            $order       = $this->order->loadByIncrementId($incrementId);        
            $orderId     = $order->getId();

            $order    = $this->order->load($orderId);         
            $newState = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT;
            $order->setState($newState)->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
            $order->save();
        }

        /**
         * TRANSACTION_VOIDED
         */
        if (isset($webhookData["eventType"]) && $webhookData['eventType'] == "TRANSACTION_VOIDED") {
            $incrementId = $webhookData['data']['data']['customer']['orderNumber'];
            $order       = $this->order->loadByIncrementId($incrementId);        
            $orderId     = $order->getId();

            $order    = $this->order->load($orderId);         
            $newState = \Magento\Sales\Model\Order::STATE_CANCELED;
            $order->setState($newState)->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
            $order->save();
            $this->orderManagement->cancel($orderId);
        }

        /**
         * TRANSACTION_CAPTURED
         */
        if (isset($webhookData["eventType"]) && $webhookData['eventType'] == "TRANSACTION_CAPTURED") {
            $incrementId = $webhookData['data']['data']['customer']['orderNumber'];
            $order       = $this->order->loadByIncrementId($incrementId);        
            $orderId     = $order->getId();

            $order = $this->orderRepository->get($orderId);
            if ($order->canInvoice()) {
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $invoice->save();
                $transactionSave = $this->transaction->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                
                $transactionSave->save();
                $this->invoiceSender->send($invoice);
                //Send Invoice mail to customer
                $order->addStatusHistoryComment(
                    __('Notified customer about invoice creation #%1.', $invoice->getId())
                )
                    ->setIsCustomerNotified(true)
                    ->save();
            }
        }

        /**
         * TRANSACTION_REFUNDED
         */
        if (isset($webhookData["eventType"]) && $webhookData['eventType'] == "TRANSACTION_REFUNDED") {
            $incrementId = $webhookData['data']['data']['customer']['orderNumber'];
            $order = $this->order->loadByIncrementId($incrementId);
            try {
                $invoices = $order->getInvoiceCollection();
                foreach ($invoices as $invoice) {
                    $invoiceincrementid = $invoice->getIncrementId();
                }
                if (isset($invoiceincrementid)) {
                    $invoiceobj = $this->invoice
                        ->loadByIncrementId($invoiceincrementid);
                    $creditmemo = $this->creditmemofactory->createByOrder($order);
                    $creditmemo->setInvoice($invoiceobj);
                    $this->creditmemoservice->refund($creditmemo);
                    $this->logger->info("CreditMemo Succesfully Created For Order: ".$incrementId);
                } else {
                    $this->logger->info("CreditMemo Cannot Be Created For Order: ".$incrementId);
                }
            } catch (\Exception $e) {
                $this->logger->info("Creditmemo Not Created". $e->getMessage());
            }
        }
       
    }
}