<?php


class Allopass_Hipay_Model_Rule_Condition_Address extends Mage_Rule_Model_Condition_Abstract
{
    public function loadAttributeOptions()
    {
        $useOrderCurrency = Mage::getStoreConfig('hipay/hipay_api/currency_transaction', Mage::app()->getStore());

        if ($useOrderCurrency) {
            $attributes = array(
                'base_subtotal' => Mage::helper('sales')->__('Subtotal'),
                'grand_total' => Mage::helper('sales')->__('Grand Total'),
                'currency_code' => Mage::helper('adminhtml')->__('Currency'),
                'items_qty' => Mage::helper('salesrule')->__('Total Items Quantity'),
                'weight' => Mage::helper('salesrule')->__('Total Weight'),
                'created_at' => Mage::helper('hipay')->__("Order's time"),
                'shipping_method' => Mage::helper('salesrule')->__('Shipping Method'),
                'billing_postcode' => Mage::helper('hipay')->__('Billing Postcode'),
                'billing_region' => Mage::helper('hipay')->__('Billing Region'),
                'billing_region_id' => Mage::helper('hipay')->__('Billing State/Province'),
                'billing_country_id' => Mage::helper('hipay')->__('Billing Country'),
            );
        }else{
            $attributes = array(
                'base_subtotal' => Mage::helper('sales')->__('Subtotal'),
                'base_grand_total' => Mage::helper('sales')->__('Grand Total'),
                'base_currency_code' => Mage::helper('adminhtml')->__('Currency'),
                'items_qty' => Mage::helper('salesrule')->__('Total Items Quantity'),
                'weight' => Mage::helper('salesrule')->__('Total Weight'),
                'created_at' => Mage::helper('hipay')->__("Order's time"),
                'shipping_method' => Mage::helper('salesrule')->__('Shipping Method'),
                'billing_postcode' => Mage::helper('hipay')->__('Billing Postcode'),
                'billing_region' => Mage::helper('hipay')->__('Billing Region'),
                'billing_region_id' => Mage::helper('hipay')->__('Billing State/Province'),
                'billing_country_id' => Mage::helper('hipay')->__('Billing Country'),
            );
        }

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getInputType()
    {
        $useOrderCurrency = Mage::getStoreConfig('hipay/hipay_api/currency_transaction', Mage::app()->getStore());

        if ($useOrderCurrency) {
            switch ($this->getAttribute()) {
                case 'subtotal':
                case 'weight':
                case 'total_qty':
                case 'base_grandtotal':
                    return 'numeric';
                case 'shipping_method':
                case 'billing_country_id':
                case 'billing_region_id':
                case 'currency_code':
                    return 'select';
                case 'created_at':
                    return 'boolean';
            }

            return 'string';
        } else {
            switch ($this->getAttribute()) {
                case 'base_subtotal':
                case 'weight':
                case 'total_qty':
                case 'base_grandtotal':
                    return 'numeric';
                case 'shipping_method':
                case 'billing_country_id':
                case 'billing_region_id':
                case 'base_currency_code':
                    return 'select';
                case 'created_at':
                    return 'boolean';
            }
            return 'string';
        }
    }

    public function getValueElementType()
    {
        $useOrderCurrency = Mage::getStoreConfig('hipay/hipay_api/currency_transaction', Mage::app()->getStore());

        if ($useOrderCurrency) {
            switch ($this->getAttribute()) {
                case 'shipping_method':
                case 'billing_country_id':
                case 'billing_region_id':
                case 'currency_code':
                case 'created_at':
                    return 'select';
            }
            return 'text';
        } else {
            switch ($this->getAttribute()) {
                case 'shipping_method':
                case 'billing_country_id':
                case 'billing_region_id':
                case 'base_currency_code':
                case 'created_at':
                    return 'select';
            }
            return 'text';
        }
    }


    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'billing_country_id':
                    $options = Mage::getModel('adminhtml/system_config_source_country')
                        ->toOptionArray();
                    break;

                case 'billing_region_id':
                    $options = Mage::getModel('adminhtml/system_config_source_allregion')
                        ->toOptionArray();
                    break;

                case 'shipping_method':
                    $options = Mage::getModel('adminhtml/system_config_source_shipping_allmethods')
                        ->toOptionArray();
                    break;

                case 'currency_code':
                    $options = Mage::getModel('adminhtml/system_config_source_currency')
                        ->toOptionArray(false);
                    break;
                case 'created_at':
                    $options = array(
                        array("value" => "00::8", "label" => Mage::helper('hipay')->__("Midnight - 8:00 a.m.")),
                        array("value" => "8::15", "label" => Mage::helper('hipay')->__("8:00 a.m. - 3:00 p.m.")),
                        array("value" => "15::20", "label" => Mage::helper('hipay')->__("3:00 pm. - 8:00 p.m.")),
                        array("value" => "20::23", "label" => Mage::helper('hipay')->__("8:00 p.m. - 11:59 p.m.")),
                    );
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
        $quote = $object;

        if (!($object instanceof Mage_Sales_Model_Quote)) {
            $quote = Mage::getModel('sales/quote')->load($object->getQuoteId());
        }

        $address = $quote->getBillingAddress();

        $useOrderCurrency = Mage::getStoreConfig('hipay/hipay_api/currency_transaction', Mage::app()->getStore());

        //Get infos from billing address
        $toValidate = new Varien_Object();

        if ($useOrderCurrency) {
            $toValidate->setSubtotal($quote->getSubtotal());
            $toValidate->setGrandTotal($quote->getGrandTotal());
            $toValidate->setCurrencyCode($quote->getCurrencyCode());
        } else {
            $toValidate->setBaseSubtotal($quote->getBaseSubtotal());
            $toValidate->setBaseGrandTotal($quote->getBaseGrandTotal());
            $toValidate->setBaseCurrencyCode($quote->getBaseCurrencyCode());
        }
        $toValidate->setBillingPostcode($address->getPostcode());
        $toValidate->setBillingRegion($address->getRegion());
        $toValidate->setBillingRegionId($address->getRegionId());
        $toValidate->setBillingCountryId($address->getCountryId());

        if (!$quote->isVirtual()) {//Get infos from shipping address
            $address = $quote->getShippingAddress();
        }

        $toValidate->setWeight($address->getWeight());
        $toValidate->setShippingMethod($address->getShippingMethod());

        $toValidate->setTotalQty($quote->getItemsQty());
        $toValidate->setCreatedAt($this->_getFormatCreatedAt($object));

        return parent::validate($toValidate);
    }

    protected function _getFormatCreatedAt($object)
    {
        $created_at = $object->getCreatedAt();

        if (!$created_at instanceof Zend_Date) {
            $created_at = Mage::app()->getLocale()->storeDate($object->getStoreId(), $created_at, true);
        }

        $hour = (int)$created_at->toString("H");

        switch (true) {
            case ($hour >= 0 && $hour <= 8):
                return '00::8';
            case ($hour > 8 && $hour <= 15):
                return '8::15';
            case ($hour > 15 && $hour <= 20):
                return '15::20';
            case ($hour > 20 && $hour <= 23):
                return '20::23';

        }

        return '';
    }

    public function getTypeElement()
    {
        return $this->getForm()->addField($this->getPrefix() . '__' . $this->getId() . '_' . $this->getPaymentMethodCode() . '__type',
            'hidden', array(
                //'name'    => 'rule_' . $this->getPaymentMethodCode() . '[' . $this->getPrefix() . '][' . $this->getId().'_'. $this->getPaymentMethodCode() . '][type]',
                'name' => 'rule_' . $this->getPaymentMethodCode() . '[' . $this->getPrefix() . '][' . $this->getId() . '][type]',
                'value' => $this->getType(),
                'no_span' => true,
                'class' => 'hidden',
            ));
    }

    public function getAttributeElement()
    {
        if (is_null($this->getAttribute())) {
            foreach ($this->getAttributeOption() as $k => $v) {
                $this->setAttribute($k);
                break;
            }
        }
        return $this->getForm()->addField($this->getPrefix() . '__' . $this->getId() . '_' . $this->getPaymentMethodCode() . '__attribute',
            'select', array(
                //'name'=>'rule_' . $this->getPaymentMethodCode() . '['.$this->getPrefix().']['.$this->getId().'_'. $this->getPaymentMethodCode().'][attribute]',
                'name' => 'rule_' . $this->getPaymentMethodCode() . '[' . $this->getPrefix() . '][' . $this->getId() . '][attribute]',
                'values' => $this->getAttributeSelectOptions(),
                'value' => $this->getAttribute(),
                'value_name' => $this->getAttributeName(),
            ))->setRenderer(Mage::getBlockSingleton('rule/editable'));
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
        if (is_null($this->getOperator())) {
            foreach ($options as $option) {
                $this->setOperator($option['value']);
                break;
            }
        }

        $elementId = sprintf('%s__%s__operator', $this->getPrefix(),
            $this->getId() . '_' . $this->getPaymentMethodCode());
        //$elementName = sprintf('rule_'.$this->getPaymentMethodCode().'[%s][%s][operator]', $this->getPrefix(), $this->getId().'_'. $this->getPaymentMethodCode());
        $elementName = sprintf('rule_' . $this->getPaymentMethodCode() . '[%s][%s][operator]', $this->getPrefix(),
            $this->getId());
        $element = $this->getForm()->addField($elementId, 'select', array(
            'name' => $elementName,
            'values' => $options,
            'value' => $this->getOperator(),
            'value_name' => $this->getOperatorName(),
        ));
        $element->setRenderer(Mage::getBlockSingleton('rule/editable'));

        return $element;
    }

    public function getValueElement()
    {
        $elementParams = array(
            //'name'               => 'rule_'.$this->getPaymentMethodCode().'['.$this->getPrefix().']['.$this->getId().'_'. $this->getPaymentMethodCode().'][value]',
            'name' => 'rule_' . $this->getPaymentMethodCode() . '[' . $this->getPrefix() . '][' . $this->getId() . '][value]',
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
        return $this->getForm()->addField($this->getPrefix() . '__' . $this->getId() . '_' . $this->getPaymentMethodCode() . '__value',
            $this->getValueElementType(),
            $elementParams
        )->setRenderer($this->getValueElementRenderer());
    }
}
