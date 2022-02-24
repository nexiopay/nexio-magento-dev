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

namespace Nexio\OnlinePayment\Model\Payment;

/**
 * Class Nexiogrouppayment
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class Nexiogrouppayment extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "nexiogrouppayment";

    const CODE = "nexiogrouppayment";

    protected $_isOffline = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;

    /**
     * Payment method
     *
     * @param CartInterface $quote For cart interface.
     *
     * @return Available payment method
     */
    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }
}
