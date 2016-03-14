<?php
class Allopass_Hipay_NotifyController extends Mage_Core_Controller_Front_Action
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
		
		//Mage::log($this->getRequest()->getParams(),null,$this->getRequest()->getActionName() . ".log");
		if (!$this->_validateSignature()) {
			$this->getResponse()->setBody("NOK. Wrong Signature!");
			$this->setFlag('', 'no-dispatch', true);
		}		
	}
	
	protected function _validateSignature()
	{
		/* @var $_helper Allopass_Hipay_Helper_Data */
		$_helper = Mage::helper('hipay');
		
		/* @var $response Allopass_Hipay_Model_Api_Response_Notification */
		$response  = Mage::getSingleton('hipay/api_response_notification',$this->getRequest()->getParams());
		
		$signature = $this->getRequest()->getServer('HTTP_X_ALLOPASS_SIGNATURE');
		return $_helper->checkSignature($signature,true,$response);
	}
	

	
	public function indexAction()
	{
		/* @var $response Allopass_Hipay_Model_Api_Response_Notification */
		$response  = Mage::getSingleton('hipay/api_response_notification',$this->getRequest()->getParams());
		$orderArr = $response->getOrder();
		
		/* @var $order Mage_Sales_Model_Order */
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderArr['id']);
		
		if(!$order->getId() && (strpos($orderArr['id'], 'recurring') === false && strpos($orderArr['id'], 'split') === false))
			die("Order not found in notification");
		
		if(strpos($orderArr['id'], 'recurring') !== false)
		{
			//return $this;
				
			list($action,$type,$profileId) = explode("-", $orderArr['id']);
				
			if($profileId)
			{
				/* @var $profile Mage_Sales_Model_Recurring_Profile */
				$profile = Mage::getModel('sales/recurring_profile')->load($profileId);
				if($profile->getId())
				{
					

					if($action == 'create' || $action == "payment")
					{
						//$order = $this->createProfileOrder($profile, $response);
					}
						
					//return $this;	
					
				}
				else
					die(Mage::helper('hipay')->__("Profile for ID: %d doesn't exists (Recurring).",$profileId));
			}
			else 
				die(Mage::helper('hipay')->__("Order Id not present (Recurring)."));
				
		}
		elseif (strpos($orderArr['id'], 'split') !== false)
		{
			list($id,$type,$splitPaymentId) = explode("-", $orderArr['id']);
			/* @var $order Mage_Sales_Model_Order */
			$order = Mage::getModel('sales/order')->loadByIncrementId($id);
		}
		
		$payment = $order->getPayment();
		/* @var $methodInstance Allopass_Hipay_Model_Method_Abstract */
		$methodInstance = $payment->getMethodInstance();
		$methodInstance->debugData($response->debug());
		$amount = 0;
		if((int)$response->getRefundedAmount() == 0 && (int)$response->getCapturedAmount() == 0)
			$amount = $response->getAuthorizedAmount();
		elseif((int)$response->getRefundedAmount() == 0 && (int)$response->getCapturedAmount() > 0 )
			$amount = $response->getCapturedAmount();
		else 
			$amount = $response->getRefundedAmount();
		
		$transactionId = $response->getTransactionReference();


		// Move Notification before processing
		$message = Mage::helper('hipay')->__("Notification from Hipay:") . " " . Mage::helper('hipay')->__("status") . ": ". $response->getStatus(). " Message: " .$response->getMessage()." ".Mage::helper('hipay')->__('amount: %s',(string)$amount);
		$order->addStatusToHistory($order->getStatus(), $message);
		$order->save();

		// THEN processResponse
		$methodInstance->processResponse($response, $payment, $amount);

		return $this;
		
		
	}
	
	/**
	 *
	 * @param Mage_Sales_Model_Recurring_Profile $profile
	 * @param Allopass_Hipay_Model_Api_Response_Notification $response
	 * @return Mage_Sales_Model_Order
	 */
	protected function createProfileOrder(Mage_Sales_Model_Recurring_Profile $profile,Allopass_Hipay_Model_Api_Response_Notification $response)
	{
	
		$amount = $this->getAmountFromProfile($profile);
	
		$productItemInfo = new Varien_Object;
		$type = "Regular";
		if ($type == 'Trial') {
			$productItemInfo->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_TRIAL);
		} elseif ($type == 'Regular') {
			$productItemInfo->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_REGULAR);
		}
	
		if($this->isInitialProfileOrder($profile))// because is not additonned in prodile obj
			$productItemInfo->setPrice($profile->getBillingAmount() + $profile->getInitAmount());
	
		/* @var $order Mage_Sales_Model_Order */
		$order = $profile->createOrder($productItemInfo);
	
		//$this->responseToPayment($order->getPayment(),$response);
		$additionalInfo = $profile->getAdditionalInfo();
		
		$order->getPayment()->setCcType($additionalInfo['ccType']);
		$order->getPayment()->setCcExpMonth($additionalInfo['ccExpMonth']);
		$order->getPayment()->setCcExpYear($additionalInfo['ccExpYear']);
		$order->getPayment()->setAdditionalInformation('token',$additionalInfo['token']);
		$order->getPayment()->setAdditionalInformation('create_oneclick',$additionalInfo['create_oneclick']);
		$order->getPayment()->setAdditionalInformation('use_oneclick',$additionalInfo['use_oneclick']);
		//$order->getPayment()->setAdditionalInformation('selected_oneclick_card', $additionalInfo['selected_oneclick_card']);
		
		$order->setState(Mage_Sales_Model_Order::STATE_NEW,'pending',Mage::helper('hipay')->__("New Order Recurring!"));
		
		$order->save();
	
		$profile->addOrderRelation($order->getId());
		$profile->save();
		
		return $order;
		
		
		$order->getPayment()->registerCaptureNotification($amount);
		$order->save();
	
		// notify customer
		if ($invoice = $order->getPayment()->getCreatedInvoice()) {
			$message = Mage::helper('hipay')->__('Notified customer about invoice #%s.', $invoice->getIncrementId());
			$comment = $order->sendNewOrderEmail()->addStatusHistoryComment($message)
			->setIsCustomerNotified(true)
			->save();
	
			/* Add this to send invoice to customer */
			$invoice->setEmailSent(true);
			$invoice->save();
			$invoice->sendEmail();
		}
	
		return $order;
			
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
		
}
