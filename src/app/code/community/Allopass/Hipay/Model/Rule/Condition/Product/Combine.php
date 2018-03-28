<?php

class Allopass_Hipay_Model_Rule_Condition_Product_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('hipay/rule_condition_product_combine');
    }

    public function getNewChildSelectOptions()
    {
        $productCondition = Mage::getModel('hipay/rule_condition_product');
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $pAttributes = array();
        $iAttributes = array();
        foreach ($productAttributes as $code => $label) {
            if (strpos($code, 'order_item_') === 0) {
                $iAttributes[] = array(
                    'value' => 'hipay/rule_condition_product|' . $code . '|' . $this->getPaymentMethodCode(),
                    'label' => $label
                );
            } else {
                $pAttributes[] = array(
                    'value' => 'hipay/rule_condition_product|' . $code . '|' . $this->getPaymentMethodCode(),
                    'label' => $label
                );
            }
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            array(
                array(
                    'value' => 'hipay/rule_condition_product_combine||' . $this->getPaymentMethodCode(),
                    'label' => Mage::helper('catalog')->__('Conditions Combination')
                ),
                array('label' => Mage::helper('hipay')->__('Order Attribute'), 'value' => $iAttributes),
                array('label' => Mage::helper('catalog')->__('Product Attribute'), 'value' => $pAttributes),
            )
        );
        return $conditions;
    }

    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }

    public function getTypeElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '_' . $this->getPaymentMethodCode() . '__type',
            'hidden',
            array(
                //'name'    => 'rule_' . $this->getPaymentMethodCode() . '[' . $this->getPrefix() . '][' . $this->getId().'_'. $this->getPaymentMethodCode() . '][type]',
                'name' => 'rule_' . $this->getPaymentMethodCode() . '[' . $this->getPrefix() . '][' . $this->getId(
                    ) . '][type]',
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
                //'name'=>'rule_' . $this->getPaymentMethodCode() . '['.$this->getPrefix().']['.$this->getId().'_'. $this->getPaymentMethodCode().'][attribute]',
                'name' => 'rule_' . $this->getPaymentMethodCode() . '[' . $this->getPrefix() . '][' . $this->getId(
                    ) . '][attribute]',
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
        //$elementName = sprintf('rule_'.$this->getPaymentMethodCode().'[%s][%s][operator]', $this->getPrefix(), $this->getId().'_'. $this->getPaymentMethodCode());
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
            //'name'               => 'rule_'.$this->getPaymentMethodCode().'['.$this->getPrefix().']['.$this->getId().'_'. $this->getPaymentMethodCode().'][value]',
            'name' => 'rule_' . $this->getPaymentMethodCode() . '[' . $this->getPrefix() . '][' . $this->getId(
                ) . '][value]',
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
                //'name'=>'rule_'.$this->getPaymentMethodCode().'['.$this->getPrefix().']['.$this->getId().'_'. $this->getPaymentMethodCode().'][new_child]',
                'name' => 'rule_' . $this->getPaymentMethodCode() . '[' . $this->getPrefix() . '][' . $this->getId(
                    ) . '][new_child]',
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
                // 'name'=>'rule_'.$this->getPaymentMethodCode().'['.$this->getPrefix().']['.$this->getId().'_'. $this->getPaymentMethodCode().'][aggregator]',
                'name' => 'rule_' . $this->getPaymentMethodCode() . '[' . $this->getPrefix() . '][' . $this->getId(
                    ) . '][aggregator]',
                'values' => $this->getAggregatorSelectOptions(),
                'value' => $this->getAggregator(),
                'value_name' => $this->getAggregatorName(),
            )
        )->setRenderer(Mage::getBlockSingleton('rule/editable'));
    }

    public function asHtmlRecursive()
    {
        $html = $this->asHtml() . '<ul id="' . $this->getPrefix() . '__' . $this->getId(
            ) . '_' . $this->getPaymentMethodCode() . '__children" class="rule-param-children">';
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
