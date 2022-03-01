<?php
/**
 * Nexio Credit Card module Model
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

namespace Nexio\OnlinePayment\Model;

use Nexio\OnlinePayment\Helper\Data;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\PaymentMethodList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session;
use Psr\Log\LoggerInterface;

/**
 * Class PaymentConfigProvider
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class PaymentConfigProvider implements ConfigProviderInterface
{

    /**
     * Get the helper data.
     *
     * @var Data
     */
    protected $helperData;

    /**
     * Scope config interface
     * 
     * @var ScopeConfigInterface
     */
    private $_config;

    /**
     * Logger for system log.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Construct params
     *
     * @param ScopeConfigInterface  $_config         Config.
     * @param Session               $checkoutSession Session.
     * @param Data                  $helperData      Helper data.
     * @param PaymentMethodList     $paymentmethods  Payment methods.
     * @param StoreManagerInterface $storeManager    For Store data.
     * @param LoggerInterface       $logger          For Log.
     */
    public function __construct(
        ScopeConfigInterface  $_config,
        Session               $checkoutSession,
        Data                  $helperData,
        PaymentMethodList     $paymentmethods,
        StoreManagerInterface $storeManager,
        LoggerInterface       $logger
    ) {
        $this->_config         = $_config;
        $this->checkoutSession = $checkoutSession;
        $this->helperData      = $helperData;
        $this->paymentmethods  = $paymentmethods;
        $this->storeManager    = $storeManager;
        $this->logger          = $logger;
    }

    /**
     * Getting save payment details from vault
     *
     * @return array All saved payment details
     */
    public function getConfig()
    {

        $getCardData = $this->helperData->getSaveCardToken();
        $configs['verify'] = $getCardData;
        if ($getCardData == null) {
            $configs['verify'] = 'No Data';    
        }

        return $configs;
    }
}
