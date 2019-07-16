<?php
/**
 * HiPay Fullservice SDK Magento 1
 *
 * 2018 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2018 HiPay
 * @license   https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 */

/**
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Api_Formatter_Request_Maintenance extends Allopass_Hipay_Model_Api_Formatter_ApiFormatterAbstract
{

    protected $_cartFormatterClass = 'hipay/api_formatter_cart_cartMaintenanceFormatter';

    protected $_operation;
    protected $_operationId;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->_operation = $args["operation"];
        $this->_operationId = $args["operationId"];
    }

    /**
     * @return \HiPay\Fullservice\Gateway\Request\Maintenance\MaintenanceRequest
     */
    public function generate()
    {
        $maintenance = new \HiPay\Fullservice\Gateway\Request\Maintenance\MaintenanceRequest();

        $this->mapRequest($maintenance);

        return $maintenance;
    }

    /**
     * @param \HiPay\Fullservice\Gateway\Request\Maintenance\MaintenanceRequest $maintenance
     * @return void
     */
    public function mapRequest(&$maintenance)
    {
        parent::mapRequest($maintenance);

        $maintenance->amount = $this->_amount;
        $maintenance->operation = $this->_operation;
        $maintenance->operation_id = $this->_operationId;

        if (Mage::helper('hipay')->isSendCartItemsRequired($this->_payment->getCcType())) {
            $maintenance->basket = $this->getBasket($this->_operation);
        }
    }
}
