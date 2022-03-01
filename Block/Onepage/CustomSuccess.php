<?php
/**
 * Nexio Credit Card module Block to get placed order data
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

namespace Nexio\OnlinePayment\Block\Onepage;

/**
 * Class CustomSuccess
 *
 * @category  Nexio
 * @package   Nexio_OnlinePayment
 * @author    Nexio <kbatchelor@cmsonline.com>
 * @copyright 2020-2022 Nexio
 * @license   https://nex.io Nexio Licence
 * @link      https://nex.io
 */
class CustomSuccess extends \Magento\Checkout\Block\Onepage\Success
{

    /**
     * Order Data
     *
     * @var Order
     */
    protected $orderItemsDetails;

    /**
     * Construct params
     *
     * @param Context                  $context           Helper context.
     * @param Session                  $checkoutSession   Checkout Session.
     * @param Config                   $orderConfig       Order configuration.
     * @param Context                  $httpContext       Http context.
     * @param Order                    $orderItemsDetails Order items details.
     * @param OrderRepositoryInterface $orderRepository   For order repository.
     * @param Currency                 $currency          For currency.
     * @param Data                     $data              For array data.
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Sales\Model\Order $orderItemsDetails,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Directory\Model\Currency $currency,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
            $orderConfig,
            $httpContext,
            $data
        );
        $this->orderItemsDetails = $orderItemsDetails;
        $this->orderRepository   = $orderRepository;
        $this->_currency         = $currency;    
    }

    /**
     * Get order details
     *
     * @return Order data
     */
    public function getOrderItemsDetails()
    {
        $IncrementId  = $this->_checkoutSession
            ->getLastRealOrder()->getIncrementId();
        $order = $this->orderItemsDetails->loadByIncrementId($IncrementId);
       
        return $order;
    }

    /**
     * Get Currency code
     *
     * @return Currency code
     */
    public function getCurrentCurrencySymbol()
    {

        return $this->_currency->getCurrencySymbol();
    }
}
