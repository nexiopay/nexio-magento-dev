<?php

/**
 * Nexio Credit Card module Controller for analytics
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

namespace Nexio\OnlinePayment\Controller\Adminhtml\NexioLog;

use \Magento\Backend\App\Action\Context as Context;
use \Magento\Framework\View\Result\PageFactory as PageFactory;
use \Magento\Backend\App\Action as Action;

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
     * Page factory
     * 
     * @var PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * Construct params
     *
     * @param Context     $context           context.
     * @param PageFactory $resultPageFactory For page factory.
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * For admin grid title
     *
     * @return Result page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Logs')));

        return $resultPage;
    }

    /**
     * For is allowed
     *
     * @return Is allowed
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Nexio_OnlinePayment::nexiolog');
    }
}
