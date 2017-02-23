<?php

/**
 * Class Allopass_Hipay_Block_Adminhtml_System_Config_Form_Field_Notice
 */
class Allopass_Hipay_Block_Adminhtml_System_Config_Form_Field_Notice extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	
	/**
	 * Check if columns are defined, set template
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		if (!$this->getTemplate()) {
			$this->setTemplate('hipay/system/config/form/field/notice.phtml');
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
	    $notices = array();
        if (!Mage::getStoreConfigFlag('hipay/hipay_basket/activate_basket', Mage::app()->getStore())) {
            $notices[] = Mage::helper('adminhtml')->__('You have to activate and configuring the support of basket before activate the payment method klarna.');
        }

        $element->setNoticesHipay($notices);
		$this->setElement($element);
		return $this->_toHtml();
	}
}