<?php
class Allopass_Hipay_Model_Method_Przelewy24Api extends Allopass_Hipay_Model_Method_Cc
{	
	protected $_code  = 'hipay_przelewy24api';
	
	protected $_formBlockType = 'hipay/form_hosted';
	protected $_infoBlockType = 'hipay/info_hosted';
	
	
	public function getOrderPlaceRedirectUrl()
	{
		return Mage::getUrl(str_replace("_", "/", $this->getCode()).'/sendRequest',array('_secure' => true));
	}	
	
	
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
		$info->setCcType($this->getConfigData('cctypes'))
		->setAdditionalInformation('create_oneclick',$data->getOneclick() == "create_oneclick" ? 1 : 0)
		->setAdditionalInformation('use_oneclick',$data->getOneclick() == "use_oneclick" ? 1 : 0)
		;
		
		return $this;
	}
	
	public function initialize($paymentAction, $stateObject)
	{
		/* @var $payment Mage_Sales_Model_Order_Payment */
		$payment = $this->getInfoInstance();
		$order = $payment->getOrder();
		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
		
		
		if($payment->getAdditionalInformation('use_oneclick') && $customer->getId())
		{
			$token = $customer->getHipayAliasOneclick();
			$payment->setAdditionalInformation('token',$token);
		}
		
		return $this;
		
	}
	
	
	protected function getCcTypeHipay($ccTypeMagento)
	{
		return $ccTypeMagento;
	}
	
	/**
	 * Validate payment method information object
	 *
	 * @param   Mage_Payment_Model_Info $info
	 * @return  Mage_Payment_Model_Abstract
	 */
	public function validate()
	{
		/**
          * to validate payment method is allowed for billing country or not
          */
         $paymentInfo = $this->getInfoInstance();
         if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
             $billingCountry = $paymentInfo->getOrder()->getBillingAddress()->getCountryId();
         } else {
             $billingCountry = $paymentInfo->getQuote()->getBillingAddress()->getCountryId();
         }
         if (!$this->canUseForCountry($billingCountry)) {
             Mage::throwException(Mage::helper('payment')->__('Selected payment type is not allowed for billing country.'));
         }
         return $this;
	}
	
}