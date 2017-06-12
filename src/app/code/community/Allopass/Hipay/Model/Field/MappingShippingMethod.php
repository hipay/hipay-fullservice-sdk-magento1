<?php

class Allopass_Hipay_Model_Field_MappingShippingMethod extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{
    /**
     *
     * @return Mage_Core_Model_Abstract|void
     */
    protected function _beforeSave()
    {
        $values = $this->getValue();

        foreach ($values as $key => $value) {
            if (empty($value)) {
                continue;
            }

            if (!empty($value['delay_preparation']) && !is_numeric($value['delay_preparation'])) {
                Mage::throwException(Mage::helper('hipay')->__('Delay delivery is not a correct value for %s', $key));
            } else if (!empty($value['delay_delivery']) && !is_numeric($value['delay_preparation'])) {
                Mage::throwException(Mage::helper('hipay')->__('Delay delivery is not a correct value for %s', $key));
            }
        }
        $this->setValue($values);
        return parent::_beforeSave();
    }
}
