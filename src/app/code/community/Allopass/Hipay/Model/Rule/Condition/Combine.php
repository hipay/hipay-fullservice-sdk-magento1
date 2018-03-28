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
 * Hipay Rule Combine Condition data model
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Rule_Condition_Combine extends Mage_Rule_Model_Condition_Combine
{
    protected $_paymentMethodCode = null;

    public function __construct()
    {
        parent::__construct();
        $this->setType('hipay/rule_condition_combine');
    }


    public function getNewChildSelectOptions()
    {
        $addressCondition = Mage::getModel('hipay/rule_condition_address');
        $addressAttributes = $addressCondition->loadAttributeOptions()->getAttributeOption();
        $attributes = array();
        foreach ($addressAttributes as $code => $label) {
            $attributes[] = array(
                'value' => 'hipay/rule_condition_address|' . $code . '|' . $this->getPaymentMethodCode(),
                'label' => $label
            );
        }

        $customerCondition = Mage::getModel('hipay/rule_condition_customer');
        $customerAttributes = $customerCondition->loadAttributeOptions()->getAttributeOption();
        $cAttributes = array();
        foreach ($customerAttributes as $code => $label) {
            $cAttributes[] = array(
                'value' => 'hipay/rule_condition_customer|' . $code . '|' . $this->getPaymentMethodCode(),
                'label' => $label
            );
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            array(
                array(
                    'value' => 'hipay/rule_condition_product_found||' . $this->getPaymentMethodCode(),
                    'label' => Mage::helper('salesrule')->__('Product attribute combination')
                ),
                array(
                    'value' => 'hipay/rule_condition_product_subselect||' . $this->getPaymentMethodCode(),
                    'label' => Mage::helper('salesrule')->__('Products subselection')
                ),
                array(
                    'value' => 'hipay/rule_condition_combine||' . $this->getPaymentMethodCode(),
                    'label' => Mage::helper('salesrule')->__('Conditions combination')
                ),
                array('label' => Mage::helper('hipay')->__('Order Attribute'), 'value' => $attributes),
                array('label' => Mage::helper('hipay')->__('Customer Attribute'), 'value' => $cAttributes),
            )
        );

        $additional = new Varien_Object();
        Mage::dispatchEvent('hipay_rule_condition_combine', array('additional' => $additional));
        if ($additionalConditions = $additional->getConditions()) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
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
        if (is_null($this->getAttribute())) {
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
        if (is_null($this->getOperator())) {
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

    public function getNewChildElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '_' . $this->getPaymentMethodCode() . '__new_child',
            'select',
            array(
                'name' => 'rule_' . $this->getPaymentMethodCode()
                    . '[' . $this->getPrefix() . '][' . $this->getId() . '][new_child]',
                'values' => $this->getNewChildSelectOptions(),
                'value_name' => $this->getNewChildName(),
            )
        )->setRenderer(Mage::getBlockSingleton('rule/newchild'));
    }

    public function getAggregatorElement()
    {
        if (is_null($this->getAggregator())) {
            foreach ($this->getAggregatorOption() as $k => $v) {
                $this->setAggregator($k);
                break;
            }
        }

        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '_' . $this->getPaymentMethodCode() . '__aggregator',
            'select',
            array(
                'name' => 'rule_' . $this->getPaymentMethodCode()
                    . '[' . $this->getPrefix() . '][' . $this->getId() . '][aggregator]',
                'values' => $this->getAggregatorSelectOptions(),
                'value' => $this->getAggregator(),
                'value_name' => $this->getAggregatorName(),
            )
        )->setRenderer(Mage::getBlockSingleton('rule/editable'));
    }

    public function asHtmlRecursive()
    {
        $html = $this->asHtml() . '<ul id="' . $this->getPrefix() . '__'
            . $this->getId() . '_' . $this->getPaymentMethodCode() . '__children" class="rule-param-children">';
        foreach ($this->getConditions() as $cond) {
            $cond->setPaymentMethodCode($this->getPaymentMethodCode());
            $html .= '<li>' . $cond->asHtmlRecursive() . '</li>';
        }

        $html .= '<li>' . $this->getNewChildElement()->getHtml() . '</li></ul>';
        return $html;
    }


    public function getPaymentMethodCode()
    {

        return $this->_paymentMethodCode;
    }

    public function setPaymentMethodCode($methodCode)
    {
        $this->_paymentMethodCode = $methodCode;
        return $this;
    }
}
