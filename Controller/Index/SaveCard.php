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
 * needs please refer to NexioGroup for more information.
 *
 * PHP version 7.4
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    NexioGroup <kbatchelor@cmsonline.com>
 * @copyright 2020-2021 NexioGroup
 * @license   https://www.nexiopaysandbox.com Nexio Licence
 * @link      https://www.nexiopaysandbox.com/
 */

namespace Nexio\OnlinePayment\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Nexio\OnlinePayment\Helper\Data;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;

/**
 * Class SaveCard
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    NexioGroup <kbatchelor@cmsonline.com>
 * @copyright 2020-2021 NexioGroup
 * @license   https://www.nexiopaysandbox.com Nexio Licence
 * @link      https://www.nexiopaysandbox.com/
 */
class SaveCard extends Action
{
    /**
     * Get the helper data.
     *
     * @var Data
     */
    protected $helperData;

    /**
     * Checkout session data.
     *
     * @var CheckoutSession
     */
    private $_checkoutSession;

    /**
     * Construct params
     *
     * @param Context         $context           Access to all objects.
     * @param Data            $helperData        To get the helper data.
     * @param CheckoutSession $_checkoutSession  Checkout session data.
     * @param PageFactory     $resultPageFactory Result page factory.
     * @param RawFactory      $resultRawFactory  Result raw factory.
     */
    public function __construct(
        Context $context,
        Data $helperData,
        CheckoutSession $_checkoutSession,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory
    ) {
        $this->helperData        = $helperData;
        $this->_checkoutSession  = $_checkoutSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRawFactory  = $resultRawFactory;
        parent::__construct($context);
    }

    /**
     * Function to get save card token
     *
     * @return Save card token data
     */
    public function execute()
    {
        $result      = $this->resultRawFactory->create();
        $quoteid     = $this->_checkoutSession->getQuote()->getId();
        $getCardData = $this->helperData->getSaveCardToken();
        echo json_encode($getCardData);
        die;    
    }
}
