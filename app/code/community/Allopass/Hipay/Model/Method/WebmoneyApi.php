<?php
class Allopass_Hipay_Model_Method_WebmoneyApi extends Allopass_Hipay_Model_Method_Cc
{	
	protected $_code  = 'hipay_webmoneyapi';
	
	protected $_formBlockType = 'hipay/form_hosted';
	protected $_infoBlockType = 'hipay/info_hosted';
	
	protected $_canRefund               = false;
	protected $_canRefundInvoicePartial = false;
	
	
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
		$info->setCcType($this->getConfigData('cctypes'));
		
		$this->assignInfoData($info, $data);
		
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
			$cardId = $payment->getAdditionalInformation('selected_oneclick_card');
			$card = Mage::getModel('hipay/card')->load($cardId);
			if($card->getId())
				$token = $card->getCcToken();//$customer->getHipayAliasOneclick();
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