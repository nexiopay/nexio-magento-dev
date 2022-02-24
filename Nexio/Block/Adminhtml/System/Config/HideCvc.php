<?php
/**
 * Nexio Credit Card module Block
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

namespace Nexio\OnlinePayment\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;;

/**
 * Class HideCvc
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class HideCvc extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * Constant varriable for hide cvc
     */
    const CONFIG_PATH = 'onlinepayment/processingoptions/hide_cvc';

    /**
     * For template
     * 
     * @var template
     */
    protected $_template = 'Nexio_OnlinePayment::system/config/checkFraud.phtml';

    /**
     * For value
     * 
     * @var values
     */
    protected $_values = null;

    /**
     * Checkbox constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context Helper context
     * @param array                                   $data    For array data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
    
    /**
     * Retrieve element HTML markup.
     *
     * @param AbstractElement $element For element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setNamePrefix($element->getName())
            ->setHtmlId($element->getHtmlId());

        return $this->_toHtml();
    }

    /**
     * Get the hide cvc
     *
     * @return Hide cvc from config
     */
    public function getValues()
    {
        $values = [];
        $optionArray = \Nexio\OnlinePayment\Model\Config\Source\CheckFraud::toOptionArray();
        foreach ($optionArray as $value) {
            $values[$value['value']] = $value['label'];
        }

        return $values;
    }

    /**
     * Get checked value.
     *
     * @param $name for get name the checked value
     * 
     * @return boolean
     */
    public function getIsChecked($name)
    {
        
        return in_array($name, $this->getCheckedValues());
    }

    /**
     * Retrieve the checked values from config
     * 
     * @return values
     */
    public function getCheckedValues()
    {
        if (is_null($this->_values)) {
            $data = $this->getConfigData();
            if (isset($data[self::CONFIG_PATH])) {
                $data = $data[self::CONFIG_PATH];
            } else {
                $data = '';
            }
            $this->_values = explode(',', $data);
        }

        return $this->_values;
    }
}
