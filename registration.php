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

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Nexio_OnlinePayment',
    __DIR__
);
