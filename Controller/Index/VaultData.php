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

use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Model\QuoteFactory;    
use Magento\Checkout\Model\Session as CheckoutSession;
use Nexio\OnlinePayment\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Vault\Model\CreditCardTokenFactory;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Customer\Model\Session;

/**
 * Class VaultData
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class VaultData extends \Magento\Framework\App\Action\Action
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
     * Variable credit card token factory
     *
     * @var CreditCardTokenFactory
     */
    protected $creditCardTokenFactory;

    /**
     * Variable to payment Token
     *
     * @var PaymentTokenRepositoryInterface
     */
    protected $paymentTokenRepository;

    /**
     * The curl for the current file.
     *
     * @var Curl
     */
    protected $curl;

    /**
     * The user session for the current file.
     *
     * @var Session
     */
    protected $customer_Session;

    /**
     * Construct params
     *
     * @param Context                         $context                Helper context.
     * @param Data                            $jsonHelper             Json helper.
     * @param Data                            $helperData             Helper Data.
     * @param Http                            $request                Http request.
     * @param LoggerInterface                 $logger                 For Log.
     * @param CheckoutSession                 $checkoutSession        For checkout session.
     * @param PageFactory                     $resultFactory          For result factory.
     * @param UrlInterface                    $url                    For url. 
     * @param ManagerInterface                $messageManager         For message.
     * @param CreditCardTokenFactory          $creditCardTokenFactory For creditCardTokenFactory.
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository For paymentTokenRepository
     * @param NexioPaymentCustomToken         $nexiopaymenttoken      For nexiopaymenttoken.
     * @param Curl                            $curl                   Curl to make a curl request.
     * @param Session                         $customerSession        Current login user.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context              $context,
        \Magento\Framework\Json\Helper\Data                $jsonHelper,
        Data                                               $helperData,
        \Magento\Framework\App\Request\Http                $request,
        \Psr\Log\LoggerInterface                           $logger,
        CheckoutSession                                    $checkoutSession,
        \Magento\Framework\View\Result\PageFactory         $resultFactory,
        \Magento\Framework\UrlInterface                    $url,
        ManagerInterface                                   $messageManager,
        CreditCardTokenFactory                             $creditCardTokenFactory,
        PaymentTokenRepositoryInterface                    $paymentTokenRepository,
        \Nexio\OnlinePayment\Model\NexioPaymentCustomToken $nexiopaymenttoken,
        Curl                                               $curl,
        Session                                            $customerSession
    ) {
        $this->jsonHelper             = $jsonHelper;
        $this->_checkoutSession       = $checkoutSession;
        $this->helperData             = $helperData;
        $this->request                = $request;
        $this->logger                 = $logger;
        $this->resultFactory          = $resultFactory;
        $this->url                    = $url;
        $this->messageManager         = $messageManager;
        $this->creditCardTokenFactory = $creditCardTokenFactory;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->nexiopaymenttoken      = $nexiopaymenttoken;
        $this->curl                   = $curl;
        $this->customer_Session       = $customerSession;
        parent::__construct($context);
    }

    /**
     * Save the data in stored payment
     *
     * @return save iframe Data
     */
    public function execute()
    {
        
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $response   = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $response->setHeader('Content-type', 'text/plain');

        $usename       = $this->helperData->getUsername('username');
        $password      = $this->helperData->getPassword('password');
        $authorization = base64_encode("$usename:$password");
        
        $apiUrl  = $this->helperData->getApiUrl();
        $quoteid = $this->_checkoutSession->getQuote()->getId();
        
        //Get additional data from js for save stored payment details
        $vaultInformation = $this->request->getParam('additional_data');
        $vaultData        = $vaultInformation['savedCardToken'];
        
        $headers = [
            "Content-Type" => "application/json",
            "Authorization" => "Basic $authorization"
        ];

        $this->curl->setHeaders($headers);
        $this->curl->get($apiUrl.'pay/v3/vault/card/'.$vaultData);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $savedCardTokens = json_decode($this->curl->getBody(), true);

        $cardType = $savedCardTokens['tokenex']['cardType'];
        $cardCode = '';

        if ($cardType == 'visa') {
            $cardCode = 'VI';
        } else if ($cardType == 'masterCard') {
            $cardCode = 'MC';
        } else if ($cardType == 'discover') {
            $cardCode = 'DI';
        } else if ($cardType == 'americanExpress') {
            $cardCode = 'AE';
        } else if ($cardType == 'jcb') {
            $cardCode = 'JCB'; 
        } else if ($cardType == 'unknown') {
            $cardCode = 'unknown'; 
        } else {
            $cardCode = 'null';  
        }
        
        $paymentToken = $this->creditCardTokenFactory->create();
        $paymentToken->setExpiresAt(date('Y-m-d H:i:s', strtotime("+30 day")));

        $paymentToken->setGatewayToken($savedCardTokens['tokenex']['token']);
        $paymentToken->setDetails(
            '{"type":"'.$cardCode.'","card":"{'.ucfirst($cardType).'}","maskedCC":"'.$savedCardTokens['tokenex']['lastFour'].'","cardHolderName":"'.$savedCardTokens['card']['cardHolderName'].'","expirationDate": "'.$savedCardTokens['card']['expirationMonth'].'/'.$savedCardTokens['card']['expirationYear'].'"}'
        );
        $paymentToken->setIsActive(true);
        $paymentToken->setIsVisible(true);
        $customerId       = $this->customer_Session->getCustomer()->getId();
        $vaultPaymentData = $this->nexiopaymenttoken
            ->getByPublicCustomHash1($customerId);

        if (!empty($vaultPaymentData)) {
            $paymentToken->setMakeDefault(0);
        } else {
            $paymentToken->setMakeDefault(1);
        }
        $paymentToken->setType('card');
        $paymentToken->setPaymentMethodCode('nexiogrouppayment');
        $customerId = $this->customer_Session->getCustomer()->getId();
        $paymentToken->setCustomerId($customerId);
        $paymentToken->setPublicHash(
            $this->helperData->generateRandomString($length = 10)
        );
        $this->paymentTokenRepository->save($paymentToken);
    } 
}
