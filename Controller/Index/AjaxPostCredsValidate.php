<?php
namespace Nexio\OnlinePayment\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Nexio\OnlinePayment\Helper\Data;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AjaxPostCredsValidate extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    private $scopeConfig;
    protected $configWriter;
    protected $encryptor;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor

    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->encryptor = $encryptor;
        $this->_curl = $curl;
        parent::__construct($context);
        $this->resultJsonFactory= $resultJsonFactory;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $resultJson = $this->resultJsonFactory->create();

        $areCredsValid = false;
        $message = "";
        $isError = false;
        $isEnabledForAccountUpdater = false;
        $isFraudCheckEnabled = false;

        try {
            $userName = $params['username'];
            $password = $params['password'];
            $apiURL = $params['apiURL'];

            $responseJSON = AjaxValidateNexioCreds::validateNexioCreds($userName, $password, $apiURL, $this->_curl);
            $areCredsValid = $responseJSON['are_creds_valid'];
            $isEnabledForAccountUpdater = $responseJSON['isEnabledForAccountUpdater'];
            $isFraudCheckEnabled = $responseJSON['isFraudCheckEnabled'];


        }catch(\Exception $e) {
            $areCredsValid = false;
            $message = "Invalid Credentials";
            $isError = true;
        }


        return $resultJson->setData([
            'are_creds_valid' => $areCredsValid,
            'isEnabledForAccountUpdater' => $isEnabledForAccountUpdater,
            'isFraudCheckEnabled' => $isFraudCheckEnabled,
            'message' => $message,
            'error' => $isError
        ]);
    }
}
