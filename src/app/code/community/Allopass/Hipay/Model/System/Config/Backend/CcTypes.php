<?php 
class Allopass_Hipay_Model_System_Config_Backend_CcTypes extends Mage_Core_Model_Config_Data
{
    protected function _afterload()
    {
        if (!is_array($this->getValue())) {
            $this->setValue(explode(",", $this->getValue()));
        }
        return parent::_afterload();
    }
}
