<?php
/**
 * Nexio Credit Card module plugin
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

namespace Nexio\OnlinePayment\Plugin;

/**
 * Class CsrfValidatorSkip
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class CsrfValidatorSkip
{
    /**
     * For generate CSRF token
     * 
     * @param \Magento\Framework\App\Request\CsrfValidator $subject Csrf validator
     * @param \Closure                                     $proceed Closure
     * @param \Magento\Framework\App\RequestInterface      $request Request interface
     * @param \Magento\Framework\App\ActionInterface       $action  Action interface
     * 
     * @return Csrf token
     */
    public function aroundValidate(
        $subject,
        \Closure $proceed,
        $request,
        $action
    ) {

        if ($request->getModuleName() == 'onlinepayment') {
           
            return; // Skip CSRF check
        }
     
        $proceed($request, $action);
    }
}
