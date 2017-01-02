<?php

/**
 * 3ds Rule Product Condition data model
 */
class Allopass_Hipay_Model_Rule_Condition_Product extends Mage_CatalogRule_Model_Rule_Condition_Product
{
    
    
    /**
     * Add special attributes
     *
     * @param array $attributes
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['order_item_qty'] = Mage::helper('salesrule')->__('Quantity in order');
        $attributes['order_item_price'] = Mage::helper('salesrule')->__('Price in order');
        $attributes['order_item_row_total'] = Mage::helper('salesrule')->__('Row total in order');
    }

    /**
     * Validate Product Rule Condition
     *
     * @param Varien_Object $object
     *
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $product = false;
        if ($object->getProduct() instanceof Mage_Catalog_Model_Product) {
            $product = $object->getProduct();
        } else {
            $product = Mage::getModel('catalog/product')
                ->load($object->getProductId());
        }

        $product
            ->setOrderItemQty($object->getQtyOrdered())
            ->setOrderItemPrice($object->getPrice()) // possible bug: need to use $object->getBasePrice()
            ->setOrderItemRowTotal($object->getBaseRowTotal());

        return parent::validate($product);
    }
    
    public function getTypeElement()
    {
        return $this->getForm()->addField($this->getPrefix() . '__' . $this->getId() .'_'. $this->getPaymentMethodCode() . '__type', 'hidden', array(
            //'name'    => 'rule_' . $this->getPaymentMethodCode() . '[' . $this->getPrefix() . '][' . $this->getId().'_'. $this->getPaymentMethodCode() . '][type]',
            'name'    => 'rule_' . $this->getPaymentMethodCode() . '[' . $this->getPrefix() . '][' . $this->getId() . '][type]',
            'value'   => $this->getType(),
            'no_span' => true,
            'class'   => 'hidden',
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
        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'_'. $this->getPaymentMethodCode().'__attribute', 'select', array(
            //'name'=>'rule_' . $this->getPaymentMethodCode() . '['.$this->getPrefix().']['.$this->getId().'_'. $this->getPaymentMethodCode().'][attribute]',
             'name'=>'rule_' . $this->getPaymentMethodCode() . '['.$this->getPrefix().']['.$this->getId().'][attribute]',
            'values'=>$this->getAttributeSelectOptions(),
            'value'=>$this->getAttribute(),
            'value_name'=>$this->getAttributeName(),
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

        $elementId   = sprintf('%s__%s__operator', $this->getPrefix(), $this->getId().'_'. $this->getPaymentMethodCode());
        //$elementName = sprintf('rule_'.$this->getPaymentMethodCode().'[%s][%s][operator]', $this->getPrefix(), $this->getId().'_'. $this->getPaymentMethodCode());
        $elementName = sprintf('rule_'.$this->getPaymentMethodCode().'[%s][%s][operator]', $this->getPrefix(), $this->getId());
        $element     = $this->getForm()->addField($elementId, 'select', array(
            'name'          => $elementName,
            'values'        => $options,
            'value'         => $this->getOperator(),
            'value_name'    => $this->getOperatorName(),
        ));
        $element->setRenderer(Mage::getBlockSingleton('rule/editable'));

        return $element;
    }
    
    public function getValueElement()
    {
        $elementParams = array(
            //'name'               => 'rule_'.$this->getPaymentMethodCode().'['.$this->getPrefix().']['.$this->getId().'_'. $this->getPaymentMethodCode().'][value]',
            'name'               => 'rule_'.$this->getPaymentMethodCode().'['.$this->getPrefix().']['.$this->getId().'][value]',
            'value'              => $this->getValue(),
            'values'             => $this->getValueSelectOptions(),
            'value_name'         => $this->getValueName(),
            'after_element_html' => $this->getValueAfterElementHtml(),
            'explicit_apply'     => $this->getExplicitApply(),
        );
        if ($this->getInputType()=='date') {
            // date format intentionally hard-coded
            $elementParams['input_format'] = Varien_Date::DATE_INTERNAL_FORMAT;
            $elementParams['format']       = Varien_Date::DATE_INTERNAL_FORMAT;
        }
        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'_'. $this->getPaymentMethodCode().'__value',
            $this->getValueElementType(),
            $elementParams
        )->setRenderer($this->getValueElementRenderer());
    }
}
