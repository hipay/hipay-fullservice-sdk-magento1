<?php
class Allopass_Hipay_Model_PaymentProfile extends Mage_Core_Model_Abstract
{
    
    
    /**
     * Period units
     *
     * @var string
     */
    const PERIOD_UNIT_DAY = 'day';
    const PERIOD_UNIT_WEEK = 'week';
    const PERIOD_UNIT_SEMI_MONTH = 'semi_month';
    const PERIOD_UNIT_MONTH = 'month';
    const PERIOD_UNIT_YEAR = 'year';
    
    /**
     * Payment types
     */
    const PAYMENT_TYPE_SPLIT = 'split_payment';
    const PAYMENT_TYPE_RECURRING = 'recurring_payment';
    
    protected function _construct()
    {
        parent::_construct();
        $this->_init('hipay/paymentProfile');
        $this->setIdFieldName('profile_id');
    }
    
    public function getAllPaymentTypes($withLabels = true)
    {
        $paymenTypes = array(
                self::PAYMENT_TYPE_SPLIT,
                self::PAYMENT_TYPE_RECURRING,
        );
        
        if ($withLabels) {
            $result = array();
            foreach ($paymenTypes as $paymenType) {
                $result[$paymenType] = $this->getPaymentTypeLabel($paymenType);
            }
            return $result;
        }
        return $paymenTypes;
    }
    
    public function getPaymentTypeLabel($paymentType)
    {
        switch ($paymentType) {
            case self::PAYMENT_TYPE_SPLIT:  return Mage::helper('hipay')->__('Split payment');
            case self::PAYMENT_TYPE_RECURRING: return Mage::helper('hipay')->__('Recurring Payment');
        }
        return $paymentType;
    }
    
    /**
     * Getter for available period units
     *
     * @param bool $withLabels
     * @return array
     */
    public function getAllPeriodUnits($withLabels = true)
    {
        $units = array(
                self::PERIOD_UNIT_DAY,
                self::PERIOD_UNIT_WEEK,
                self::PERIOD_UNIT_SEMI_MONTH,
                self::PERIOD_UNIT_MONTH,
                self::PERIOD_UNIT_YEAR
        );
    
        if ($withLabels) {
            $result = array();
            foreach ($units as $unit) {
                $result[$unit] = $this->getPeriodUnitLabel($unit);
            }
            return $result;
        }
        return $units;
    }
    
    /**
     * Render label for specified period unit
     *
     * @param string $unit
     */
    public function getPeriodUnitLabel($unit)
    {
        switch ($unit) {
            case self::PERIOD_UNIT_DAY:  return Mage::helper('payment')->__('Day');
            case self::PERIOD_UNIT_WEEK: return Mage::helper('payment')->__('Week');
            case self::PERIOD_UNIT_SEMI_MONTH: return Mage::helper('payment')->__('Two Weeks');
            case self::PERIOD_UNIT_MONTH: return Mage::helper('payment')->__('Month');
            case self::PERIOD_UNIT_YEAR:  return Mage::helper('payment')->__('Year');
        }
        return $unit;
    }
    
    /**
     * Getter for field label
     *
     * @param string $field
     * @return string|null
     */
    public function getFieldLabel($field)
    {
        switch ($field) {
            case 'subscriber_name':
                return Mage::helper('payment')->__('Subscriber Name');
            case 'start_datetime':
                return Mage::helper('payment')->__('Start Date');
            case 'internal_reference_id':
                return Mage::helper('payment')->__('Internal Reference ID');
            case 'schedule_description':
                return Mage::helper('payment')->__('Schedule Description');
            case 'suspension_threshold':
                return Mage::helper('payment')->__('Maximum Payment Failures');
            case 'bill_failed_later':
                return Mage::helper('payment')->__('Auto Bill on Next Cycle');
            case 'period_unit':
                return Mage::helper('payment')->__('Billing Period Unit');
            case 'period_frequency':
                return Mage::helper('payment')->__('Billing Frequency');
            case 'period_max_cycles':
                return Mage::helper('payment')->__('Maximum Billing Cycles');
            case 'billing_amount':
                return Mage::helper('payment')->__('Billing Amount');
            case 'trial_period_unit':
                return Mage::helper('payment')->__('Trial Billing Period Unit');
            case 'trial_period_frequency':
                return Mage::helper('payment')->__('Trial Billing Frequency');
            case 'trial_period_max_cycles':
                return Mage::helper('payment')->__('Maximum Trial Billing Cycles');
            case 'trial_billing_amount':
                return Mage::helper('payment')->__('Trial Billing Amount');
            case 'currency_code':
                return Mage::helper('payment')->__('Currency');
            case 'shipping_amount':
                return Mage::helper('payment')->__('Shipping Amount');
            case 'tax_amount':
                return Mage::helper('payment')->__('Tax Amount');
            case 'init_amount':
                return Mage::helper('payment')->__('Initial Fee');
            case 'init_may_fail':
                return Mage::helper('payment')->__('Allow Initial Fee Failure');
            case 'method_code':
                return Mage::helper('payment')->__('Payment Method');
            case 'reference_id':
                return Mage::helper('payment')->__('Payment Reference ID');
        }
    }
}
