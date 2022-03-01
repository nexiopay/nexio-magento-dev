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

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\OfflinePayments\Model\Banktransfer;

/**
 * Class DataAssignObserver
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class DataAssignObserver implements ObserverInterface
{
    /**
     * Input Params Resolver.
     *
     * @var inputParamsResolver
     */
    protected $inputParamsResolver;

    /**
     * Quote Repository.
     *
     * @var quoteRepository
     */
    protected $quoteRepository;

    /**
     * Logger.
     *
     * @var logger
     */
    protected $logger;

    /**
     * State.
     *
     * @var state
     */
    protected $state;

    /**
     * Construct params
     *
     * @param InputParamsResolver $inputParamsResolver Retrieving input data.
     * @param QuoteRepository     $quoteRepository     Get Qoute data.
     * @param LoggerInterface     $logger              Message to the logs.
     * @param State               $state               Sate code.
     */
    public function __construct(
        \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\State $state
    ) {
        $this->inputParamsResolver = $inputParamsResolver;
        $this->quoteRepository     = $quoteRepository;
        $this->logger              = $logger;
        $this->state               = $state;
    }

    /**
     * Save the iframe data
     *
     * @param Observer $observer Observer for save data
     *
     * @return Save Data
     */
    public function execute(EventObserver $observer)
    {
        $paymentOrder = $observer->getEvent()->getPayment();
        $order        = $paymentOrder->getOrder();
        $quote        = $this->quoteRepository
            ->get($order->getQuoteId());
        $paymentQuote = $quote->getPayment();
        $method       = $paymentQuote->getMethodInstance()->getCode();
        
        if ($method == 'nexiogrouppayment') {
            $inputParams = $this->inputParamsResolver->resolve();
            $areaCode    = $this->state->getAreaCode();
            if ($areaCode != \Magento\Framework\App\Area::AREA_ADMINHTML) {
                
                foreach ($inputParams as $inputParam) {
                    if ($inputParam instanceof \Magento\Quote\Model\Quote\Payment) {
                    
                        $paymentData  = $inputParam->getData('additional_data');

                        $paymentQuote->setAdditionalInformation(
                            $paymentData
                        );                                        
                    }
                }
            }
        }
    }
}
