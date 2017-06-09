<?php

/**
 * Class Allopass_Hipay_Block_Adminhtml_System_Config_Form_Field_Notice
 */
class Allopass_Hipay_Block_Adminhtml_System_Config_Form_Field_Information extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	
	/**
	 * Check if columns are defined, set template
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		if (!$this->getTemplate()) {
			$this->setTemplate('hipay/system/config/form/field/information.phtml');
		}
	}

	/**
	 * Custom field
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
        $element->setInformationsHipay($element->getData('tooltip'));
		$this->setElement($element);
		return $this->_toHtml();
	}
}