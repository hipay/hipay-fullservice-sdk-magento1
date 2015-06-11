<?php
class Allopass_Hipay_Model_SplitPayment extends Mage_Core_Model_Abstract
{
	
	const SPLIT_PAYMENT_STATUS_PENDING = 'pending';
	const SPLIT_PAYMENT_STATUS_FAILED = 'failed';
	const SPLIT_PAYMENT_STATUS_COMPLETE = 'complete';
	
	protected function _construct()
	{
		parent::_construct();
		$this->_init('hipay/splitPayment');
		$this->setIdFieldName('split_payment_id');
	}
	
	
	static function getStatues()
	{
		$statues = array(self::SPLIT_PAYMENT_STATUS_PENDING=>Mage::helper('sales')->__('Pending'),
				self::SPLIT_PAYMENT_STATUS_FAILED=>Mage::helper('sales')->__('Failed'),
				self::SPLIT_PAYMENT_STATUS_COMPLETE=>Mage::helper('sales')->__('Complete')
		);
		
		return $statues;
	}
	
	/**
	 * 
	 * @return boolean|string
	 */
	public function pay()
	{
		
		if(!$this->canPay())
			Mage::throwException("This split payment is already paid!");
		
		if(!$this->getId())
		{
			Mage::throwException("Split Payment not found!");
		}
		
		$state = $this->getMethodInstance()->paySplitPayment($this);
		
		switch ($state)
		{
			case Allopass_Hipay_Model_Method_Abstract::STATE_COMPLETED:
			case Allopass_Hipay_Model_Method_Abstract::STATE_FORWARDING:
			case Allopass_Hipay_Model_Method_Abstract::STATE_PENDING:		
				$this->setStatus(self::SPLIT_PAYMENT_STATUS_COMPLETE);
				break;
			case Allopass_Hipay_Model_Method_Abstract::STATE_DECLINED:
			case Allopass_Hipay_Model_Method_Abstract::STATE_ERROR:
			default:
				$this->setStatus(self::SPLIT_PAYMENT_STATUS_FAILED);
				$this->sendErrorEmail();
				break;
					
		}
		
		$this->setAttempts($this->getAttempts() + 1);
		$this->save();
		return $this;
		
	}
	
	public function sendErrorEmail()
	{
		/* @var $helperCheckout Mage_Checkout_Helper_Data */
		$helperCheckout = Mage::helper('checkout');
		$order = Mage::getModel('sales/order')->load($this->getOrderId());
		$message = Mage::helper('hipay')->__("Error on request split Payment HIPAY. Split Payment Id: ".$this->getSplitPaymentId());
		$helperCheckout->sendPaymentFailedEmail($order, $message,'Split Payment Hipay');
	}
	
	/**
	 * @return Allopass_Hipay_Model_Method_Abstract
	 */
	public function getMethodInstance()
	{
		list($moduleName,$methodClass) = explode("_",$this->getMethodCode());
		return MAge::getSingleton($moduleName . "/method_" . $methodClass );
	}
	
	public function canPay()
	{
		return $this->getStatus() == self::SPLIT_PAYMENT_STATUS_FAILED || $this->getStatus() == self::SPLIT_PAYMENT_STATUS_PENDING;
	}
	
	
	
}