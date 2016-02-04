<?php
class Allopass_Hipay_Model_Method_Sdd extends Allopass_Hipay_Model_Method_Cc
{
	protected $_code = 'hipay_sdd';

	protected $_canRefund               = false;
	protected $_canRefundInvoicePartial = false;


	/**
	 * Assign data to info model instance
	 *
	 * @param   mixed $data
	 * @return  Mage_Payment_Model_Info
	 */
	public function assignData($data)
	{
		if (!($data instanceof Varien_Object)) {
			$data = new Varien_Object($data);
		}
		$info = $this->getInfoInstance();
		$info->setCcType($this->getConfigData('cctypes'));
		
		$this->assignInfoData($info, $data);
		
		return $this;
	}
}