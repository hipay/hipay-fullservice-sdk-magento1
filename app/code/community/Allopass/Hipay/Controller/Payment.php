<?php
class Allopass_Hipay_Controller_Payment extends Mage_Core_Controller_Front_Action
{
	/**
	 * 
	 * @var Mage_Sales_Model_Order $order
	 */	
	protected $_order = null;
	
	
	/**
	 * @return Mage_Core_Controller_Front_Action
	 */
	public function preDispatch() {
		parent::preDispatch();
		
		if (!$this->_validateSignature()) {
			$this->getResponse()->setBody("NOK. Wrong Signature!");
			$this->setFlag('', 'no-dispatch', true);
		}
	}
	
	
	protected function _validateSignature()
	{
		return true;
		/* @var $_helper Allopass_Hipay_Helper_Data */
		$_helper = Mage::helper('hipay');
		$signature = $this->getRequest()->getParam('hash');
		return $_helper->checkSignature($signature);
	}
	
	/**
	 * 
	 * @return Allopass_Hipay_Model_Method_Abstract $methodInstance
	 */
	protected function _getMethodInstance()
	{
		Mage::throwException("Method: '" . __METHOD__ . "' must be implemented!");
	}

	public function sendRequestAction()
	{
		$order = $this->getOrder();
		$payment = $order->getPayment();

		$methodInstance = $this->_getMethodInstance();
		
		try
		{
			$redirectUrl = $methodInstance->place($payment,$order->getBaseTotalDue());
		}
		catch (Exception $e)
		{
			Mage::logException($e);
			$this->getCheckout()->addError($e->getMessage());
			$this->_redirect('checkout/cart');
			return $this;
		}
		
		
		$this->_redirectUrl($redirectUrl);
		
		return $this;
		
	}
	
	
	public function acceptAction()
	{
		if(($profileIds = Mage::getSingleton('checkout/session')->getLastRecurringProfileIds()))
		{
			if(is_array($profileIds))
			{
				/* @var $gatewayResponse Allopass_Hipay_Model_Api_Response_Gateway */
				$gatewayResponse  = Mage::getSingleton('hipay/api_response_gateway',$this->getRequest()->getParams());
				$collection = Mage::getModel('sales/recurring_profile')->getCollection()
				->addFieldToFilter('profile_id', array('in' => $profileIds))
				;
				$profiles = array();
				foreach ($collection as $profile) {
					//$referenceId = $gatewayResponse->getToken()."-".$profile->getId();
					$additionalInfo = array();
					$additionalInfo['ccType'] = $gatewayResponse->getBrand();
					$additionalInfo['ccExpMonth'] = $gatewayResponse->getCardExpiryMonth() ;
					$additionalInfo['ccExpYear'] = $gatewayResponse->getCardExpiryYear();
					$additionalInfo['token'] = $gatewayResponse->getToken();
					$additionalInfo['transaction_id'] = $gatewayResponse->getTransactionReference();
					$profile->setAdditionalInfo($additionalInfo);
					//$profile->setReferenceId($referenceId);
					$profile->setState(Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE);
					 
					$profile->save();
				}
			}
		}
		/*else 
		{		
			$this->processResponse();
		}*/
		$this->processResponse();
		$this->_redirect('checkout/onepage/success');
		
		return $this;
	}
	
	public function pendingAction()
	{
		$this->processResponse();
		$this->_redirect($this->_getMethodInstance()->getConfigData('pending_redirect_page'));
		
		return $this;
	}
	
	public function declineAction()
	{
		$this->processResponse();
		$this->_redirect('checkout/onepage/failure');
		return $this;
	}

	
	public function exceptionAction()
	{
		$this->_redirect('checkout/onepage/failure');
		return $this;
	}
	
	
	public function cancelAction()
	{
		$this->processResponse();
		$this->_redirect('checkout/cart');
		return $this;
	}
	
	protected function processResponse()
	{
		$order = $this->getOrder();
		$payment = $order->getPayment();
		
		/* @var $gatewayResponse Allopass_Hipay_Model_Api_Response_Gateway */
		$gatewayResponse  = Mage::getSingleton('hipay/api_response_gateway',$this->getRequest()->getParams());
		
		$this->_getMethodInstance()->processResponseToRedirect($gatewayResponse, $payment, $order->getBaseTotalDue());
	}

	
	
	/**
	 * 
	 * @return Mage_Sales_Model_Order
	 */
	protected function getOrder()
	{
		if(is_null($this->_order))
		{
			
			if(($profileIds = $this->getCheckout()->getLastRecurringProfileIds()))
			{
					
				if (is_array($profileIds)) {
					
					foreach ($profileIds as $profileId)
					{
						/* @var $profile Mage_Sales_Model_Recurring_Profile */
						$profile = Mage::getModel('sales/recurring_profile')->load($profileId);
						/* @var $_helperRecurring Allopass_Hipayrecurring_Helper_Data */
						$_helperRecurring = Mage::helper('hipayrecurring');
						
						if($_helperRecurring->isInitialProfileOrder($profile))
							$this->_order = $_helperRecurring->createOrderFromProfile($profile);
						else 
						{
							$orderId = current($profile->getChildOrderIds());
							$this->_order = Mage::getModel('sales/order')->load($orderId);
							
							$additionalInfo = $profile->getAdditionalInfo();
							
							$this->_order->getPayment()->setCcType(isset($additionalInfo['ccType']) ? $additionalInfo['ccType'] : "");
							$this->_order->getPayment()->setCcExpMonth(isset($additionalInfo['ccExpMonth']) ? $additionalInfo['ccExpMonth'] : "");
							$this->_order->getPayment()->setCcExpYear(isset($additionalInfo['ccExpYear']) ? $additionalInfo['ccExpYear'] : "");
							$this->_order->getPayment()->setAdditionalInformation('token',isset($additionalInfo['token']) ? $additionalInfo['token'] : "");
							$this->_order->getPayment()->setAdditionalInformation('create_oneclick',isset($additionalInfo['create_oneclick']) ? $additionalInfo['create_oneclick'] : 1);
							$this->_order->getPayment()->setAdditionalInformation('use_oneclick',isset($additionalInfo['use_oneclick']) ? $additionalInfo['use_oneclick'] : 0);
							$this->_order->getPayment()->setAdditionalInformation('selected_oneclick_card',isset($additionalInfo['selected_oneclick_card']) ? $additionalInfo['selected_oneclick_card'] : 0);
						}
						
						
						
						return $this->_order; //because only one nominal item in cart is authorized and Hipay not manage many profiles
					}
					
					
				}
					
				Mage::throwException("An error occured. Profile Ids not present!");
					
					
					
			}
			else
			{
				$this->_order = Mage::getModel('sales/order')->load($this->getCheckout()->getLastOrderId());
			}
		}
		
		return $this->_order;
	}
	
	/**
	 * Add method to calculate amount from recurring profile
	 * @param Mage_Sales_Model_Recurring_Profile $profile
	 * @return int $amount
	 **/
	public function getAmountFromProfile(Mage_Sales_Model_Recurring_Profile $profile) {
		$amount = $profile->getBillingAmount() + $profile->getTaxAmount() + $profile->getShippingAmount();
	
		if($this->isInitialProfileOrder($profile))
			$amount += $profile->getInitAmount() ;
	
		return $amount;
	}
	
	protected function isInitialProfileOrder(Mage_Sales_Model_Recurring_Profile $profile)
	{
		if(count($profile->getChildOrderIds()) && current($profile->getChildOrderIds()) == "-1")
			return true;
	
		return false;
	}
	
	
	/**
	 * 
	 * @return Mage_Checkout_Model_Session
	 */
	protected function getCheckout()
	{
		return Mage::getSingleton('checkout/session');
	}
}