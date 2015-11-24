<?php
class Allopass_Hipay_Block_Adminhtml_PaymentProfile_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	protected function _prepareCollection()
	{
		
		$collection = Mage::getModel('hipay/paymentProfile')->getCollection();	
		$this->setCollection($collection);	
		parent::_prepareCollection();
		return $this;
	}
	
	
	protected function _prepareColumns()
	{
		/* @var $profile Allopass_Hipay_Model_PaymentProfile */
		$profile = Mage::getModel('hipay/paymentProfile');
		
		$this->addColumn('profile_id',
				array(
						'header'=> Mage::helper('hipay')->__('ID'),
						'width' => '50px',
						'type'  => 'number',
						'index' => 'profile_id',
				));
		$this->addColumn('name',
				array(
						'header'=> Mage::helper('hipay')->__('Name'),
						'index' => 'name',
				));

	
		$this->addColumn('period_unit',
				array(
						'header'=> $profile->getFieldLabel('period_unit'),
						'width' => '60px',
						'index' => 'period_unit',
						'type'  => 'options',
						'options' => Mage::getSingleton('hipay/paymentProfile')->getAllPeriodUnits(),
				));
		
		
		$this->addColumn('period_frequency',
				array(
						'header'=> $profile->getFieldLabel('period_frequency'),
						'width' => '10px',
						'type'  => 'number',
						'index' => 'period_frequency',
				));
		
		$this->addColumn('period_max_cycles',
				array(
						'header'=> $profile->getFieldLabel('period_max_cycles'),
						'width' => '10px',
						'type'  => 'number',
						'index' => 'period_max_cycles',
				));
		
		$this->addColumn('payment_type',
				array(
						'header'=> Mage::helper('hipay')->__('Payment type'),
						'width' => '60px',
						'index' => 'payment_type',
						'type'  => 'options',
						'options' => Mage::getSingleton('hipay/paymentProfile')->getAllPaymentTypes(),
				));
	
		
	
		return parent::_prepareColumns();
	}
	
	/**
	 * Row click url
	 *
	 * @return string
	 */
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('profile_id' => $row->getId()));
	}
}