<?php

class Allopass_Hipay_Block_Adminhtml_Field_MappingCategory extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
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
        $categories = Mage::helper('hipay')->getMagentoCategories();
        $mappingSaved = $this->getElement()->getValue();

        // Add All Magento categories in Array Rows
        if (!$mappingSaved || !array_key_exists('magento_code', $mappingSaved[key($mappingSaved)])) {
            if (!empty($categories) && is_array($categories)) {
                foreach ($categories as $code => $label) {
                    $mapping = $this->_getHipayCategoryMapping($code);
                    $options[$code] = array(
                        'magento_code' => $code,
                        'magento_label' => $label,
                        'hipay_category' => $this->_getHipayCategoryMapping($code),
                        'class' => empty($mapping) ? 'warning-mapping' : ''
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
     *  Prepare to render for Mapping category
     */
    public function _prepareToRender()
    {
        $this->_addAfter = false;
        $this->setTemplate('hipay/mapping.phtml');
        $this->addColumn(
            'magento_category',
            array(
                'label' => Mage::helper('hipay')->__('Magento category'),
                'renderer' => $this->_getLabelRenderer()
            )
        );

        $this->addColumn(
            'hipay_category',
            array(
                'label' => Mage::helper('hipay')->__('HiPay category'),
                'renderer' => $this->_getListOptionsRenderer()
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
        $options = Mage::helper('hipay/collection')->getItemsCategory();
        if (!$this->_listRenderer) {
            $this->_listRenderer = $this->getLayout()->createBlock('hipay/adminhtml_field_renderer_list',
                '',
                array('is_render_to_js_template' => true));
            $this->_listRenderer->setListOptions($options);
        }
        return $this->_listRenderer;
    }

    /**
     *  Extract hipay category from Mapping
     *
     * @param $codeMagentoCategory
     * @return null|int
     */
    protected function _getHipayCategoryMapping($codeMagentoCategory)
    {
        $mappingSaved = $this->getElement()->getValue();
        $id_hipay_category = null;
        if (is_array($mappingSaved)) {
            foreach ($mappingSaved as $mapping) {
                if (is_array($mapping)
                    && array_key_exists('magento_category', $mapping)
                    && $mapping['magento_category'] == $codeMagentoCategory
                ) {
                    $id_hipay_category = $mapping['hipay_category'];
                    break;
                }
            }
        }
        return $id_hipay_category;
    }


    /**
     * @inheritdoc
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getListOptionsRenderer()
                ->calcOptionHash($row->getData('hipay_category')),
            'selected="selected"'
        );
    }
}
