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
 * @copyright   Copyright (c) 2019 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Api_Formatter_ThreeDS_RecurringInfoFormatter implements Allopass_Hipay_Model_Api_Formatter_ApiFormatterInterface
{

    protected $_paymentMethod;
    protected $_payment;
    /**
     * @var Mage_Sales_Model_Order $_order
     */
    protected $_order;

    public function __construct($args)
    {
        $this->_paymentMethod = $args["paymentMethod"];
        $this->_payment = $args["payment"];
        $this->_order = $this->_payment->getOrder();
    }

    /**
     * @return \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\RecurringInfo
     */
    public function generate()
    {
        $recurringInfo = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\RecurringInfo();

        $this->mapRequest($recurringInfo);

        return $recurringInfo;
    }

    /**
     * @param \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\RecurringInfo $recurringInfo
     */
    public function mapRequest(&$recurringInfo)
    {
        /**
         * @var Allopass_Hipay_Helper_Data $_helper
         */
        $_helper = Mage::helper('hipay');

        if($_helper->splitPaymentsExists($this->_order->getId())) {
            /**
             * @var Allopass_Hipay_Model_Resource_PaymentProfile_Collection $profileCollection
             */
            $profileCollection = Mage::getResourceModel('hipay/paymentProfile_collection');
            $profileCollection->addFieldToSelect('*')
                ->addFieldToFilter(
                    'profile_id',
                    $this->_payment->getAdditionalInformation('split_payment_id')
                )
                ->load();

            if ($profileCollection->count() > 0) {
                $profile = $profileCollection->getFirstItem();

                $frequency = 0;
                switch ($profile->getPeriodUnit()) {
                    case Allopass_Hipay_Model_PaymentProfile::PERIOD_UNIT_MONTH:
                        {
                            $frequency = 28 * $profile->getPeriodFrequency();
                            break;
                        }
                    case Allopass_Hipay_Model_PaymentProfile::PERIOD_UNIT_DAY:
                        {
                            $frequency = $profile->getPeriodFrequency();
                            break;
                        }
                    case Allopass_Hipay_Model_PaymentProfile::PERIOD_UNIT_SEMI_MONTH:
                        {
                            $frequency = 14 * $profile->getPeriodFrequency();
                            break;
                        }
                    case Allopass_Hipay_Model_PaymentProfile::PERIOD_UNIT_WEEK:
                        {
                            $frequency = 7 * $profile->getPeriodFrequency();
                            break;
                        }
                    case Allopass_Hipay_Model_PaymentProfile::PERIOD_UNIT_YEAR:
                        {
                            $frequency = 365 * $profile->getPeriodFrequency();
                            break;
                        }
                }

                $recurringInfo->frequency = $frequency;
            }

            $collection = Mage::getModel('hipay/splitPayment')
                ->getCollection()
                ->addFieldToFilter('order_id', $this->_order->getId())
                ->addFieldToSort('date_to_pay', 'desc');

            $splitPayment = $collection->getFirstItem();

            $lastDateToPay = DateTime::createFromFormat('Y-m-d', $splitPayment->getDateToPay());

            $recurringInfo->expiration_date = $lastDateToPay->format('Ymd');
        }
    }
}