<?php

class Allopass_Hipay_Block_Adminhtml_Customer_Edit_Tab_Card extends
		Mage_Adminhtml_Block_Widget_Grid implements
		Mage_Adminhtml_Block_Widget_Tab_Interface {

	/**
	 * Initialize Grid
	 *
	 */
	public function __construct() {
		parent::__construct();
		$this->setId('hipay_card_grid');
		$this->setDefaultSort('card_id', 'desc');
		$this->setUseAjax(true);
	}

	/**
	 * Retrieve current customer object
	 *
	 * @return Mage_Customer_Model_Customer
	 */
	protected function _getCustomer() {
		return Mage::registry('current_customer');
	}

	/**
	 * Create customer cards collection
	 *
	 * @return Allopass_Hipay_Model_Resource_Card_Collection
	 */
	protected function _createCollection() {
		return Mage::getModel('hipay/card')->getCollection();
	}

	/**
	 * Prepare customer card collection
	 *
	 * @return Allopass_Hipay_Block_Adminhtml_Customer_Edit_Tab_Card
	 */
	protected function _prepareCollection() {
		$collection = $this->_createCollection()
				->addFieldToFilter('customer_id',
						$this->_getCustomer()->getId());
		$this->setCollection($collection);

		return parent::_prepareCollection();
	}

	/**
	 * Prepare Grid columns
	 *
	 * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Wishlist
	 */
	protected function _prepareColumns() {
		$this
				->addColumn('name',
						array(
								'header' => Mage::helper('hipay')
										->__('Card Name'),
								'index' => 'name',));

		$this
				->addColumn('cc_type',
						array(
								'header' => Mage::helper('hipay')->__('Type'),
								'index' => 'cc_type',));

		$this
				->addColumn('cc_exp_month',
						array(
								'header' => Mage::helper('hipay')
										->__('Exp. Month'),
								'index' => 'cc_exp_month',
								'type' => 'number', 'width' => '30px'));

		$this
				->addColumn('cc_exp_year',
						array(
								'header' => Mage::helper('hipay')
										->__('Exp. Year'),
								'index' => 'cc_exp_year',
								'type' => 'number', 'width' => '30px'));

		$this
				->addColumn('cc_token',
						array(
								'header' => Mage::helper('hipay')
										->__('Alias oneclick'),
								'index' => 'cc_token',));
		
		$this->addColumn('cc_status',
				array(
						'header'=> Mage::helper('hipay')->__('Status'),
						'index' => 'cc_status',
						'type'  => 'options',
						'options' => 	array(Allopass_Hipay_Model_Card::STATUS_ENABLED=>$this->__('Enabled'),Allopass_Hipay_Model_Card::STATUS_DISABLED=>$this->__('Disabled')),
				));
		
		$this->addColumn('is_default',
				array(
						'header'=> Mage::helper('hipay')->__('Is default'),
						'index' => 'is_default',
						'type'  => 'options',
						'options' => 	array($this->__('No'),$this->__('Yes')),
				));



		return parent::_prepareColumns();
	}


	public function getGridUrl() {
		return Mage::helper('adminhtml')
				->getUrl('*/card/cards',
						array('_current' => true));
	}
	
	/**
	 * Row click url
	 *
	 * @return string
	 */
	public function getRowUrl($row)
	{
		return $this->getUrl('adminhtml/card/edit', array('card_id' => $row->getId()));
	}
	
	public function getTabLabel() {
		return Mage::helper('hipay')->__("My Payment Cards");

	}
	public function getTabTitle() {
		return Mage::helper('hipay')->__("My Payment Cards");

	}
	
	public function canShowTab()
 {
        if (Mage::registry('current_customer')->getId()) {
            return true;
        }
        return false;
    }

    public function isHidden()
    {
        if (Mage::registry('current_customer')->getId()) {
            return false;
        }
        return true;
    }

}
