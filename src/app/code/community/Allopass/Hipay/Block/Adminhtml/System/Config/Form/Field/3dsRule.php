<?php

class Allopass_Hipay_Block_Adminhtml_System_Config_Form_Field_3dsRule extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Check if columns are defined, set template
     *
     */
    public function __construct()
    {
        if (!$this->_addButtonLabel) {
            $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add');
        }
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('hipay/system/config/form/field/rules.phtml');
        }
    }

    public function getNewChildUrl()
    {
        return Mage::helper("adminhtml")->getUrl(
            '*/rule/newConditionHtml',
            array('form' => 'rule_conditions_fieldset')
        );
    }

    /**
     * Enter description here...
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {

        $partsId = explode("_", $element->getId());
        $method_code = $partsId[1] . "_" . $partsId[2];
        $rule = Mage::getModel('hipay/rule');
        $rule->setMethodCode($method_code);

        if ($element->getValue())
            $rule->load($element->getValue());

        if ($rule->getConfigPath() == "")
            $rule->setConfigPath($element->getId());

        $element->setRule($rule);

        $this->setElement($element);
        return $this->_toHtml();
    }
}
