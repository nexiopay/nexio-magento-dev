<?php
/**
 * Nexio Credit Card Make Default Controller
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

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\Message\ManagerInterface;
use Nexio\OnlinePayment\Model\NexioPaymentCustomToken;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class Makedefault
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class Makedefault extends \Magento\Framework\App\Action\Action
{
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
     * Nexio Payment Token.
     *
     * @var CustomerSession
     */
    protected $nexiopaymenttoken;

    /**
     * The user session for the current file.
     *
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Construct params
     *
     * @param Context                 $context           Helper context.
     * @param ResponseFactory         $responseFactory   For response factory.
     * @param UrlInterface            $url               For url. 
     * @param ManagerInterface        $messageManager    For message.
     * @param NexioPaymentCustomToken $nexiopaymenttoken For nexiopaymenttoken.
     * @param CustomerSession         $customerSession   Current login user.
     */
    public function __construct(
        Context                 $context,
        ResponseFactory         $responseFactory,
        UrlInterface            $url,
        ManagerInterface        $messageManager,
        NexioPaymentCustomToken $nexiopaymenttoken,
        CustomerSession         $customerSession
    ) {
        $this->responseFactory   = $responseFactory;
        $this->_url              = $url;
        $this->messageManager    = $messageManager;
        $this->nexiopaymenttoken = $nexiopaymenttoken;     
        $this->customerSession   = $customerSession;
      
        parent::__construct($context);
    }

    /**
     * Getting order data from cart
     *
     * @return Order Data
     */
    public function execute()
    {
        $tokenPublicHash  = $this->getRequest()->getParam('public_hash');
        $customerId       = $this->customerSession->getCustomer()->getId();
        $vaultPaymentData = $this->nexiopaymenttoken->updateByZero($customerId);
        $vaultPaymentData = $this->nexiopaymenttoken
            ->updateByOne($tokenPublicHash, $customerId);
        $checkOutUrl      = $this->_url->getUrl('vault/cards/listaction');
        $this->responseFactory->create()
            ->setRedirect($checkOutUrl)->sendResponse();
            
        exit;
    }
}
