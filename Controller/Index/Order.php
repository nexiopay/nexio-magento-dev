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
use Magento\Checkout\Model\Session as CheckoutSession;
use Nexio\OnlinePayment\Helper\Data;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Order
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class Order extends \Magento\Framework\App\Action\Action
{
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
     * Variable nexio log factory
     *
     * @var NexioLogFactory
     */
    public $NexioLogFactory;

    /**
     * Construct params
     *
     * @param Context          $context         Helper context.
     * @param QuoteFactory     $quote           For load quote data by ID.
     * @param QuoteManagement  $quoteManagement For quote management.
     * @param QuoteRepository  $quoteRepository For quote data.
     * @param PageFactory      $resultFactory   For result factory.
     * @param Data             $helperData      For helper Data.
     * @param Http             $request         For http request.  
     * @param CheckoutSession  $checkoutSession For checkout session.
     * @param LoggerInterface  $logger          For Log.
     * @param ResponseFactory  $responseFactory For response factory.
     * @param UrlInterface     $url             For url. 
     * @param ManagerInterface $messageManager  For message.
     * @param NexioLogFactory  $nexioanalytics  For nexio analytics.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context      $context,     
        \Magento\Quote\Model\QuoteFactory          $quote,
        \Magento\Quote\Model\QuoteManagement       $quoteManagement,
        QuoteRepository                            $quoteRepository,
        \Magento\Framework\View\Result\PageFactory $resultFactory,
        Data                                       $helperData,
        \Magento\Framework\App\Request\Http        $request,
        CheckoutSession                            $checkoutSession,       
        \Psr\Log\LoggerInterface                   $logger,
        \Magento\Framework\App\ResponseFactory     $responseFactory,
        \Magento\Framework\UrlInterface            $url,
        ManagerInterface                           $messageManager,
        \Nexio\OnlinePayment\Model\NexioLogFactory $nexioanalytics
    ) {      
        $this->quote            = $quote;
        $this->quoteManagement  = $quoteManagement;   
        $this->resultFactory    = $resultFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->helperData       = $helperData;
        $this->request          = $request;
        $this->quoteRepository  = $quoteRepository;
        $this->logger           = $logger;
        $this->responseFactory  = $responseFactory;
        $this->url              = $url;
        $this->messageManager   = $messageManager;
        $this->nexioanalytics= $nexioanalytics;
        parent::__construct($context);
    }
 
    /**
     * Getting order data and place order
     *
     * @return Order id
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $response   = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $response->setHeader('Content-type', 'text/plain');
     
        $quoteId = (int)$this->_checkoutSession->getQuote()->getId();
        $quote   = $this->quoteRepository->get($quoteId);
        
        $quote->setPaymentMethod('nexiogrouppayment');
        $paymentQuote          = $quote->getPayment();
        $additionalInformation = $this->request->getParam('additional_data');

        $customer_login_ID = $this->helperData->getCustomer();
        $itemData          = $this->helperData->productdata();

        // Set Sales Order Payment
        $quote->getPayment()->importData(array ('method' => 'nexiogrouppayment'));
        $transactionId = $this->request->getParam('id');
        $paymentQuote->setAdditionalData($transactionId);
        $paymentQuote->setTransactionId($transactionId);

        $fail3DS    = $this->request->getParam('error');
        $message3DS = $this->request->getParam('message');
        $status3DS  = $this->request->getParam('status');
        
        if (isset($fail3DS) == '435' && $fail3DS == '435') {
            $this->messageManager
                ->addErrorMessage(
                    'Due to '. $message3DS .' 
                    and try again!'
                );
            $checkOutUrl = $this->url->getUrl('checkout/cart');
            $this->responseFactory->create()
                ->setRedirect($checkOutUrl)->sendResponse();
            exit;
        } else { 
        
            // Configure quote
            $quote->setInventoryProcessed(false);
            $quote->collectTotals();
            // Update changes
            $quote->save();
            try {
                $order_id = $this->quoteManagement->placeOrder($quote->getId());
                $orderId  = $quote->getReservedOrderId();
                echo $order_id.":OrderId:".$orderId;
                $checkOutUrl = $this->url
                    ->getUrl('checkout/onepage/success');
                $this->logger->info($checkOutUrl);
                $this->responseFactory->create()
                    ->setRedirect($checkOutUrl)->sendResponse();
                exit;
            } catch (\Throwable $e) {
                $e->getMessage();
            }        
        }
    } 
}
