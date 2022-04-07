<?php
namespace Nexio\OnlinePayment\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Nexio\OnlinePayment\Helper\Data;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;

class AjaxGetAreCredsValid extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    protected $scopeConfig;
    protected $areCredsValid;
    protected $message = '';
    protected $isError = false;
    protected $isEnabledForAccountUpdater = false;
    protected $isFraudCheckEnabled = false;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Nexio\OnlinePayment\Helper\Data $helperData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\HTTP\Client\Curl $curl
    )
    {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
        $this->resultJsonFactory= $resultJsonFactory;
        $this->_curl = $curl;
        $this->helperData = $helperData;

    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $username       = $this->helperData->getUsername('username');
        $password      = $this->helperData->getPassword('password');
        $apiUrl        =  $this->helperData->getApiUrl();

        try{
            $responseJSON = AjaxValidateNexioCreds::validateNexioCreds($username, $password, $apiUrl, $this->_curl);
            $this->areCredsValid = $responseJSON['are_creds_valid'];
            $this->isEnabledForAccountUpdater = $responseJSON['isEnabledForAccountUpdater'];
            $this->isFraudCheckEnabled = $responseJSON['isFraudCheckEnabled'];

        }catch(\Exception $e) {
            $this->areCredsValid = false;
            $this->message = "Invalid Credentials";
            $this->isError = true;
        }

        return $resultJson->setData([
            'are_creds_valid' => $this->areCredsValid,
            'isEnabledForAccountUpdater' => $this->isEnabledForAccountUpdater,
            'isFraudCheckEnabled' => $this->isFraudCheckEnabled,
            'message' => $this->message,
            'error' => $this->isError
        ]);
    }
}
