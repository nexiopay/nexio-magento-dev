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

use Magento\Vault\Model\ResourceModel\PaymentToken;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\PaymentTokenRepository;

/**
 * Class NexioPaymentCustomToken
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class NexioPaymentCustomToken extends PaymentTokenRepository
{

    /**
     * Get all valut payment data.
     *
     * @param int $customerId Customer ID.
     * 
     * @return array Card details
     * 
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByPublicCustomHash($customerId)
    {
        $connection = $this->resourceModel->getConnection();
        $select = $connection
            ->select()
            ->from($this->resourceModel->getMainTable())
            ->where('is_active = ?', 1)
            ->where('customer_id = ?', $customerId);
        
        return $connection->fetchAll($select);
    }
    
    /**
     * Get make default value.
     *
     * @param int $customerId Customer ID.
     * 
     * @return Make default
     */
    public function getByPublicCustomHash1($customerId)
    {
        $makeDefault = $this->resourceModel->getConnection()
            ->fetchAll('SELECT make_default FROM vault_payment_token where `make_default`= 1');
   
        return $makeDefault;
    }

    /**
     * Update make default value by zero.
     *
     * @param int $customerId Customer ID.
     * 
     * @return Make default
     */
    public function updateByZero($customerId)
    {                    
        $connection = $this->resourceModel->getConnection();
        $tableName = 'vault_payment_token';        
        $connection->query("Update  " . $tableName . " SET `make_default`= 0 where `customer_id`=".$customerId);        
    }
    
    /**
     * Update make default value by one.
     *
     * @param int $tokenPublicHash Public hash
     * @param int $customerId      Customer ID
     * 
     * @return Make default
     */
    public function updateByOne($tokenPublicHash, $customerId)
    {
        $tokenDetails = $this->resourceModel->getByPublicHash($tokenPublicHash, $customerId);
        $token = $this->getById($tokenDetails['entity_id']);
        $token->setData('make_default', 1);

        $token->save();
    }
}
