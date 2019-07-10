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

use HiPay\Fullservice\Enum\Transaction\TransactionState;

/**
 *
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_SplitPayment extends Mage_Core_Model_Abstract
{

    const SPLIT_PAYMENT_STATUS_PENDING = 'pending';
    const SPLIT_PAYMENT_STATUS_FAILED = 'failed';
    const SPLIT_PAYMENT_STATUS_COMPLETE = 'complete';

    protected function _construct()
    {
        parent::_construct();
        $this->_init('hipay/splitPayment');
        $this->setIdFieldName('split_payment_id');
    }


    static function getStatues()
    {
        $statues = array(
            self::SPLIT_PAYMENT_STATUS_PENDING => Mage::helper('sales')->__('Pending'),
            self::SPLIT_PAYMENT_STATUS_FAILED => Mage::helper('sales')->__('Failed'),
            self::SPLIT_PAYMENT_STATUS_COMPLETE => Mage::helper('sales')->__('Complete')
        );

        return $statues;
    }

    /**
     * @return $this
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function pay()
    {

        if (!$this->canPay()) {
            Mage::throwException("This split payment is already paid!");
        }

        if (!$this->getId()) {
            Mage::throwException("Split Payment not found!");
        }

        try {
            $response = $this->getMethodInstance()->paySplitPayment($this);
            switch ($response->getState()) {
                case TransactionState::COMPLETED:
                case TransactionState::PENDING:
                case TransactionState::FORWARDING:
                    $this->setStatus(self::SPLIT_PAYMENT_STATUS_COMPLETE);
                    break;
                case TransactionState::DECLINED:
                case TransactionState::ERROR:
                default:
                    $this->setStatus(self::SPLIT_PAYMENT_STATUS_FAILED);
                    $this->sendErrorEmail();
                    break;
            }
        } catch (Exception $e) {
            if ($e->getMessage() != 'Code: 3010004. Message: This order has already been paid') {
                $this->setStatus(self::SPLIT_PAYMENT_STATUS_FAILED);
                $this->setAttempts($this->getAttempts() + 1);
                $this->save();
                throw $e;
            } else { //Order is already paid, so we set complete status
                $this->setStatus(self::SPLIT_PAYMENT_STATUS_COMPLETE);
            }
        }

        $this->setAttempts($this->getAttempts() + 1);
        $this->save();
        return $this;

    }

    public function sendErrorEmail()
    {
        /* @var $helperCheckout Mage_Checkout_Helper_Data */
        $helperCheckout = Mage::helper('checkout');
        $order = Mage::getModel('sales/order')->load($this->getOrderId());
        $message = Mage::helper('hipay')->__(
            "Error on request split Payment HIPAY. Split Payment Id: " . $this->getSplitPaymentId()
        );
        $helperCheckout->sendPaymentFailedEmail($order, $message, 'Split Payment Hipay');
    }

    /**
     * @return Allopass_Hipay_Model_Method_Abstract
     */
    public function getMethodInstance()
    {
        list($moduleName, $methodClass) = explode("_", $this->getMethodCode());
        //Fix bug due to upper letter in class name
        if (strpos($methodClass, 'xtimes') !== false) {
            $methodClass = str_replace("x", "X", $methodClass);
        }

        return Mage::getSingleton($moduleName . "/method_" . $methodClass);
    }

    public function canPay()
    {
        return $this->getStatus() == self::SPLIT_PAYMENT_STATUS_FAILED
            || $this->getStatus() == self::SPLIT_PAYMENT_STATUS_PENDING;
    }


}
