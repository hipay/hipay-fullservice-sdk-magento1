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
 *
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Rule_Condition_Customer extends Mage_Rule_Model_Condition_Abstract
{
    public function loadAttributeOptions()
    {
        $attributes = array(
            'orders_count' => Mage::helper('hipay')->__('Orders count'),
            'customer_is_guest' => Mage::helper('sales')->__('Customer is guest'),
            'diff_addresses' => Mage::helper('hipay')->__('Billing and shipping addresses are differents'),
            'customer_group' => Mage::helper('adminhtml')->__('Customer Groups')
        );

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'orders_count':
                return 'numeric';
            case 'customer_is_guest':
            case 'diff_addresses':
                return 'boolean';
            case 'customer_group':
                return 'multiselect';
        }

        return 'string';
    }

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'customer_is_guest':
            case 'diff_addresses':
                return 'select';
            case 'customer_group':
                return 'multiselect';
        }

        return 'text';
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'customer_is_guest':
                case 'diff_addresses':
                    $options = Mage::getModel('adminhtml/system_config_source_yesno')
                        ->toOptionArray();
                    break;
                case 'customer_group':
                    $options = Mage::getModel('adminhtml/system_config_source_customer_group_multiselect')
                        ->toOptionArray();
                    break;
                default:
                    $options = array();
            }

            $this->setData('value_select_options', $options);
        }

        return $this->getData('value_select_options');
    }

    /**
     * Validate Address Rule Condition
     *
     * @param Varien_Object|Mage_Sales_Model_Order|Mage_Sales_Model_Quote $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        //Get infos from billing address
        $toValidate = new Varien_Object();

        $customer_id = $object->getCustomerId();
        $orders_count = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter(
            'customer_id',
            $customer_id
        )->count();
        $toValidate->setOrdersCount($orders_count);
        $toValidate->setCustomerIsGuest(is_null($object->getCustomerIsGuest()) ? 0 : $object->getCustomerIsGuest());
        $toValidate->setDiffAddresses($this->_addressesesAreDifferent($object));
        $toValidate->setCustomerGroup($object->getCustomerGroupId());

        return parent::validate($toValidate);

    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return boolean $isDifferent
     */
    protected function _addressesesAreDifferent($order)
    {
        $isDifferent = 0;
        if ($order->getIsVirtual())
            return $isDifferent;


        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $methods = array('getStreetFull', 'getCity', 'getCountryId', 'getPostcode', 'getRegionId');

        foreach ($methods as $method_name) {
            $billingValue = call_user_func(array($billingAddress, $method_name));
            $shippingValue = call_user_func(array($shippingAddress, $method_name));
            if ($billingValue != $shippingValue) {
                $isDifferent = 1;
                break;
            }
        }

        return $isDifferent;
    }

    public function getTypeElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '_' . $this->getPaymentMethodCode() . '__type',
            'hidden',
            array(
                'name' => 'rule_' . $this->getPaymentMethodCode()
                    . '[' . $this->getPrefix() . '][' . $this->getId() . '][type]',
                'value' => $this->getType(),
                'no_span' => true,
                'class' => 'hidden',
            )
        );
    }

    public function getAttributeElement()
    {
        if ($this->getAttribute() === null) {
            foreach ($this->getAttributeOption() as $k => $v) {
                $this->setAttribute($k);
                break;
            }
        }

        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '_' . $this->getPaymentMethodCode() . '__attribute',
            'select',
            array(
                'name' => 'rule_' . $this->getPaymentMethodCode()
                    . '[' . $this->getPrefix() . '][' . $this->getId() . '][attribute]',
                'values' => $this->getAttributeSelectOptions(),
                'value' => $this->getAttribute(),
                'value_name' => $this->getAttributeName(),
            )
        )->setRenderer(Mage::getBlockSingleton('rule/editable'));
    }

    /**
     * Retrieve Condition Operator element Instance
     * If the operator value is empty - define first available operator value as default
     *
     * @return Varien_Data_Form_Element_Select
     */
    public function getOperatorElement()
    {
        $options = $this->getOperatorSelectOptions();
        if ($this->getOperator() ===  null) {
            foreach ($options as $option) {
                $this->setOperator($option['value']);
                break;
            }
        }

        $elementId = sprintf(
            '%s__%s__operator',
            $this->getPrefix(),
            $this->getId() . '_' . $this->getPaymentMethodCode()
        );
        $elementName = sprintf(
            'rule_' . $this->getPaymentMethodCode() . '[%s][%s][operator]',
            $this->getPrefix(),
            $this->getId()
        );
        $element = $this->getForm()->addField(
            $elementId,
            'select',
            array(
                'name' => $elementName,
                'values' => $options,
                'value' => $this->getOperator(),
                'value_name' => $this->getOperatorName(),
            )
        );
        $element->setRenderer(Mage::getBlockSingleton('rule/editable'));

        return $element;
    }

    public function getValueElement()
    {
        $elementParams = array(
            'name' => 'rule_' . $this->getPaymentMethodCode()
                . '[' . $this->getPrefix() . '][' . $this->getId() . '][value]',
            'value' => $this->getValue(),
            'values' => $this->getValueSelectOptions(),
            'value_name' => $this->getValueName(),
            'after_element_html' => $this->getValueAfterElementHtml(),
            'explicit_apply' => $this->getExplicitApply(),
        );
        if ($this->getInputType() == 'date') {
            // date format intentionally hard-coded
            $elementParams['input_format'] = Varien_Date::DATE_INTERNAL_FORMAT;
            $elementParams['format'] = Varien_Date::DATE_INTERNAL_FORMAT;
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '_' . $this->getPaymentMethodCode() . '__value',
            $this->getValueElementType(),
            $elementParams
        )->setRenderer($this->getValueElementRenderer());
    }
}
