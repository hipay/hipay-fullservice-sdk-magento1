<?php

class Allopass_Hipay_Block_Adminhtml_Field_HashingButtonInit extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     *  Label for Hashing button
     *
     * @type string
     */
    protected $labelButton = "Click";

    /**
     *  Unset Scope
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     *  Add template for rendering
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('hipay/field/button.phtml');
        return $this;
    }

    /**
     * Get the button and scripts contents.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $fieldConfig = $element->getFieldConfig();
        $this->labelButton =  $fieldConfig->label_button;
        return $this->_toHtml();
    }

    /**
     *  Display Label for button
     *
     * @return string
     */
    public function getLabel() {
        return $this->labelButton;
    }

    /**
     * Display confirmation message before syncronization
     *
     * @return string
     */
    public function getConfirmationMessage() {
        return Mage::helper('hipay')->__('Are you sure you want to sync the hashing configuration for notifications ?');
    }

    /**
     * Get Path for Synchronize Action
     *
     * @return string
     * @type url
     *
     */
    public function getButtonAction() {
        return $this->getUrl('adminhtml/hashing/synchronize/', array('store' => Mage::getSingleton('adminhtml/config_data')->getStore(),
            'website' => Mage::getSingleton('adminhtml/config_data')->getWebsite()));
    }


}
