<?php

class Allopass_Hipay_Block_Adminhtml_Field_MappingShippingMethod extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * Renderer for List Category
     *
     * @var Allopass_Hipay_Block_Adminhtml_Field_Renderer_List
     */
    protected $_listRenderer;

    /**
     * Renderer for Label
     *
     * @var Allopass_Hipay_Block_Adminhtml_Field_Renderer_Label
     */
    protected $_labelRenderer;

    /**
     *
     * @inheritdoc
     */
    public function getArrayRows()
    {
        $options = array();
        $shippingMethod = Mage::helper('hipay')->getMagentoShippingMethods();
        $mappingSaved = $this->getElement()->getValue();

        // Add All Magento categories in Array Rows
        if (!$mappingSaved || !array_key_exists($mappingSaved[key($mappingSaved)], 'magento_code')) {
            if (!empty($shippingMethod) && is_array($shippingMethod)) {
                foreach ($shippingMethod as $code => $label) {
                    $mapping = $this->_getHipayDeliveryMapping($code);
                    $options[$code] =
                        array(
                            'magento_code' => $code,
                            'magento_label' => $label,
                            'hipay_delivery_method' => $mapping['hipay_delivery_method'],
                            'delay_preparation' => $mapping['delay_preparation'],
                            'delay_delivery' => $mapping['delay_delivery'],
                            'class' => empty($mapping) || (empty($mapping['hipay_delivery_method'])
                                    || empty($mapping['delay_preparation']) ||  empty($mapping['delay_delivery']) ) ? 'warning-mapping' : ''
                    );
                }
            }
        } else {
            $options = $mappingSaved;
        }

        $this->getElement()->setValue($options);
        return parent::getArrayRows();
    }


    /**
     *  Prepare to render for Shipping Method Mapping
     */
    public function _prepareToRender()
    {
        $this->_addAfter = false;
        $this->setTemplate('hipay/mapping.phtml');
        $this->addColumn(
            'magento_shipping_method',
            array(
                'label' => Mage::helper('hipay')->__('Magento shipping method'),
                'style' => 'width: 400px;',
                'renderer' => $this->_getLabelRenderer()
            )
        );

        $this->addColumn(
            'hipay_delivery_method',
            array(
                'label' => Mage::helper('hipay')->__('Hipay delivery method'),
                'renderer' => $this->_getListOptionsRenderer()
            )
        );

        $this->addColumn(
            'delay_preparation',
            array(
                'label' => Mage::helper('hipay')->__('Order preparation delay'),
                'style' => 'width: 50px;',
            )
        );
        $this->addColumn(
            'delay_delivery',
            array(
                'label' => Mage::helper('hipay')->__('Delivery delay'),
                'style' => 'width: 50px;',
            )
        );
    }

    /**
     *  Get Label Renderer ( No input select )
     *
     * @return Allopass_Hipay_Block_Adminhtml_Field_Renderer_Label
     */
    public function _getLabelRenderer()
    {
        if (!$this->_labelRenderer) {
            $this->_labelRenderer = $this->getLayout()->createBlock('hipay/adminhtml_field_renderer_label', '');
        }
        return $this->_labelRenderer;
    }

    /**
     *  Get List Renderer
     *
     * @return Allopass_Hipay_Block_Adminhtml_Field_Renderer_Label
     */
    public function _getListOptionsRenderer()
    {
        if (!$this->_listRenderer) {
            $options = Mage::helper('hipay/collection')->getItemsDelivery();
            $this->_listRenderer = $this->getLayout()->createBlock('hipay/adminhtml_field_renderer_list',
                '',
                array('is_render_to_js_template' => true));
            $this->_listRenderer->setListOptions($options);
        }
        return $this->_listRenderer;
    }

    /**
     *  Extract hipay delivery method from Mapping
     *
     * @param $codeMagentoCategory
     * @return null|int
     */
    protected function _getHipayDeliveryMapping($codeMagentoCategory)
    {
        $mappingSaved = $this->getElement()->getValue();
        foreach ($mappingSaved as $mapping) {
            if ($mapping['magento_shipping_method'] == $codeMagentoCategory) {
                return $mapping;
                break;
            }
        }
        return null;
    }


    /**
     * @inheritdoc
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getListOptionsRenderer()
                ->calcOptionHash($row->getData('hipay_delivery_method')),
            'selected="selected"'
        );
    }
}
