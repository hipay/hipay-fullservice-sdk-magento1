<?php

/**
 *
 * Allopass Hipay Attributes EAN Dropdown
 *
 */
class Allopass_Hipay_Model_Source_Attributes
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();

        $options = array();

        $options[] = array(
            'value' => '',
            'label' => Mage::helper('adminhtml')->__('-- Please Select --')
        );

        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributecode();
            $label = $attribute->getFrontendLabel();
            if (!empty($code) && !empty($label)) {
                $options[] = array(
                    'value' => $attribute->getAttributecode(),
                    'label' => $attribute->getFrontendLabel(),
                );
            }
        }

        return $options;

    }
}
