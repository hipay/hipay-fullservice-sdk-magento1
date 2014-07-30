<?php
abstract class Allopass_Hipay_Model_Method_Abstract extends Mage_Payment_Model_Method_Abstract
{
	const OPERATION_SALE = "Sale";	
	const OPERATION_AUTHORIZATION = "Authorization";
	const OPERATION_MAINTENANCE_CAPTURE = "Capture";
	const OPERATION_MAINTENANCE_REFUND = "Refund";
	
	
	const STATE_COMPLETED = "completed";
	const STATE_FORWARDING = "forwarding";
	const STATE_PENDING = "pending";
	const STATE_DECLINED = "declined";
	const STATE_ERROR = "error";
	
	//const STATUS_PENDING_CAPTURE = 'pending_capture';
	
	/**
	 * Availability options
	 */
	protected $_isGateway               = true;
	protected $_canAuthorize            = true;
	protected $_canCapture              = true;
	protected $_canCapturePartial       = true;
	protected $_canRefund               = true;
	protected $_canRefundInvoicePartial = true;
	protected $_canVoid                 = true;
	protected $_canUseInternal          = false;
	protected $_canUseCheckout          = true;
	protected $_canUseForMultishipping  = false;
	protected $_canSaveCc 				= false;
	protected $_canReviewPayment		= false;
	
	//protected $_allowCurrencyCode = array('EUR');
	
	/**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = array('token','cardtoken','card_number','cvc'); 
	
	
	public function isInitializeNeeded()
	{
		return true;
	}
	
	
	protected function getOperation()
	{
		switch ($this->getConfigPaymentAction())
		{
			case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE:
				return self::OPERATION_AUTHORIZATION;
			default:
				return self::OPERATION_SALE;
		}
		
		return '';
	}
	
	
	public function authorize(Varien_Object $payment, $amount)
	{
		parent::authorize($payment, $amount);
		
		$payment->setSkipTransactionCreation(true);
		return $this;
	}
	
	
	
	
	/**
	 * 
	 * @param Allopass_Hipay_Model_Api_Response_Gateway $gatewayResponse
	 * @param Mage_Sales_Model_Order_Payment $payment
	 * @param float $amount
	 */
	public function processResponse($gatewayResponse,$payment,$amount)
	{
		
		$order = $payment->getOrder();
		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
		
		//$defaultExceptionMessage = Mage::helper('hipay')->__('Error in process response!');
		
		switch ($this->getConfigPaymentAction()) {
			case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE:
				$requestType = self::OPERATION_AUTHORIZATION;
				$newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH;
				$defaultExceptionMessage = Mage::helper('hipay')->__('Payment authorization error.');
				break;
			case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE:
				$requestType = self::OPERATION_SALE;
				$newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE;
				$defaultExceptionMessage = Mage::helper('hipay')->__('Payment capturing error.');
				break;
		}

		switch ($gatewayResponse->getState())
		{
			case self::STATE_COMPLETED:
			case self::STATE_PENDING:		
				switch ((int)$gatewayResponse->getStatus())
				{
					case 111: //denied
						
						$this->addTransaction(
								$payment,
								$gatewayResponse->getTransactionReference(),
								$newTransactionType,
								array('is_transaction_closed' => 0),
								array(),
								Mage::helper('hipay')->getTransactionMessage(
										$payment, $requestType, /*$gatewayResponse->getTransactionReference()*/null, $amount
								)
						);

						
						if ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
							$order->unhold();
						}
						
						if (!$status = $this->getConfigData('order_status_payment_refused')) {
							$status = $order->getStatus();
						}
						
						
						if ($status == Mage_Sales_Model_Order::STATE_HOLDED && $order->canHold()) {
							$order->hold();
						} elseif ($status == Mage_Sales_Model_Order::STATE_CANCELED && $order->canCancel()) {
							$order->cancel();
						}
									
						$order->addStatusToHistory($status, Mage::helper('hipay')->getTransactionMessage(
								$payment, self::OPERATION_AUTHORIZATION, null, $amount,true,$gatewayResponse->getMessage()
						));
						
						$order->save();
						
						
						break;
					case 112: //Authorized and pending
						
						
						$this->addTransaction(
								$payment,
								$gatewayResponse->getTransactionReference(),
								Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
								array('is_transaction_closed' => 0),
								array(
										$this->_realTransactionIdKey => $gatewayResponse->getTransactionReference(),
								),
								Mage::helper('hipay')->getTransactionMessage(
										$payment, self::OPERATION_AUTHORIZATION, $gatewayResponse->getTransactionReference(), $amount,true
								)
						);
						$state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
						if(defined('Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW'))
							$state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
						$status = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
						
						if($fraudScreening = $gatewayResponse->getFraudScreening())
						{
						
							if(isset($fraudScreening['result'])
							&& ($fraudScreening['result'] == 'pending' || $fraudScreening['result'] == 'challenged') )
							{
								if(defined('Mage_Sales_Model_Order::STATUS_FRAUD'))
									$status = Mage_Sales_Model_Order::STATUS_FRAUD;
						
							}
						
						}
						
						$order->setState($state,$status,$gatewayResponse->getMessage());
						
						$order->save();
						break;
					
					case 116: //Authorized
						
						if($order->getStatus() == 'capture_requested' || $order->getStatus() == 'processing' )// for logic process
							break;
						if(!$this->isPreauthorizeCapture($payment))
							$this->addTransaction(
									$payment,
									$gatewayResponse->getTransactionReference(),
									Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
									array('is_transaction_closed' => 0),
									array(),
									Mage::helper('hipay')->getTransactionMessage(
											$payment, self::OPERATION_AUTHORIZATION, /*$gatewayResponse->getTransactionReference()*/null, $amount
									)
							);
						
						$order->setState(
								Mage_Sales_Model_Order::STATE_PROCESSING,
								'pending_capture',
								Mage::helper('hipay')
								->__("Waiting for capture transaction ID '%s' of amount %s",
										$gatewayResponse->getTransactionReference(),
										$order->getBaseCurrency()->formatTxt($order->getBaseTotalDue())),
								$notified = true);
						
						$order->save();
						if (!$order->getEmailSent()) {
							$order->sendNewOrderEmail();
						}
						
						
						
						break;
					case 117: //Capture Requested
						
						$this->addTransaction(
								$payment,
								$gatewayResponse->getTransactionReference(),
								Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
								array('is_transaction_closed' => 0),
								array(),
								Mage::helper('hipay')->getTransactionMessage(
										$payment, self::OPERATION_SALE, /*$gatewayResponse->getTransactionReference()*/null, $amount
								)
						);
						
						$message = Mage::helper("hipay")->__('Capture Requested by Hipay.');

						$order->setState(
								Mage_Sales_Model_Order::STATE_PROCESSING, 'capture_requested', $message, null, false
						);

						if(((int)$this->getConfigData('hipay_status_validate_order') == 117) === false )
							break;
						else {
							$order->save();
						}
						
					case 118: //Capture
						
						if ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
							$order->unhold();
						}
						
						
						if (!$status = $this->getConfigData('order_status_payment_accepted')) {
							$status = $order->getStatus();
						}
						
						$message = Mage::helper("hipay")->__('Payment accepted by Hipay.');
						
						if ($status == Mage_Sales_Model_Order::STATE_PROCESSING) {
							$order->setState(
									Mage_Sales_Model_Order::STATE_PROCESSING, $status, $message
							);
						} else if ($status == Mage_Sales_Model_Order::STATE_COMPLETE) {
							$order->setState(
									Mage_Sales_Model_Order::STATE_COMPLETE, $status, $message, null, false
							);
						} else {
							$order->addStatusToHistory($status, $message, true);
						}
						
						
						
						// Create invoice
						if ($this->getConfigData('invoice_create',$order->getStoreId()) && !$order->hasInvoices()) {
							
							$invoice = $order->prepareInvoice();
							$invoice->setTransactionId($gatewayResponse->getTransactionReference());						
							$invoice->register()->capture();
							$invoice->setIsPaid(1);
							Mage::getModel('core/resource_transaction')
							->addObject($invoice)->addObject($invoice->getOrder())
							->save();
						
						}
						elseif($order->hasInvoices())
						{
							foreach ($order->getInvoiceCollection() as $invoice)
							{
								if($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_OPEN && $invoice->getBaseGrandTotal() == $gatewayResponse->getCapturedAmount())
								{
									$invoice->pay();
									Mage::getModel('core/resource_transaction')
									->addObject($invoice)->addObject($invoice->getOrder())
									->save();
									
								}
							}
						}
						
						if (!$order->getEmailSent()) {
							$order->sendNewOrderEmail();
						}
						
						break;
						
					case 124: //Refund Requested
						
						$message = Mage::helper("hipay")->__('Refund Requested by Hipay.');
						
						$order->setState(
								Mage_Sales_Model_Order::STATE_PROCESSING, 'refund_requested', $message, null, false
						);
						
						break;
					case 125: //Refund
						
						if($order->hasCreditmemos())
						{
							/* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
							foreach ($order->getCreditmemosCollection() as $creditmemo)
							{
								if($creditmemo->getState() == Mage_Sales_Model_Order_Creditmemo::STATE_OPEN 
										&& $creditmemo->getGrandTotal() == $gatewayResponse->getRefundedAmount())
								{
									$creditmemo->setState(Mage_Sales_Model_Order_Creditmemo::STATE_REFUNDED);
									
									$message = Mage::helper("hipay")->__('Refund accepted by Hipay.');
									
									$order->addStatusToHistory($order->getStatus(), $message);
									
									Mage::getModel('core/resource_transaction')
									->addObject($creditmemo)->addObject($creditmemo->getOrder())
									->save();
									
								}
							}
						}
						elseif($order->canCreditmemo())
						{
							$service = Mage::getModel('sales/service_order', $order);
							$creditmemo = $service->prepareInvoiceCreditmemo($order->getInvoiceCollection()->getFirstItem());
							foreach ($creditmemo->getAllItems() as $creditmemoItem) {
								$creditmemoItem->setBackToStock(Mage::helper('cataloginventory')->isAutoReturnEnabled());
							}
							$creditmemo->setOfflineRequested(true);
							$transactionSave = Mage::getModel('core/resource_transaction')
							->addObject($creditmemo)
							->addObject($creditmemo->getOrder());
							if ($creditmemo->getInvoice()) {
								$transactionSave->addObject($creditmemo->getInvoice());
							}
							$transactionSave->save();
						}
						
						break;
				}
				
		
				if(in_array($gatewayResponse->getPaymentProduct(), array('visa','american-express','mastercard','cb')) 
					&& ((int)$gatewayResponse->getEci() == 9 || $payment->getAdditionalInformation('create_oneclick')) 
					&& !$order->isNominal()) //Recurring E-commerce
				{
						
					if($customer->getId())
					{
						$this->responseToCustomer($customer,$gatewayResponse);
							
					}
				}
				$order->save();
				break;
		
			case self::STATE_FORWARDING:
				$this->addTransaction(
						$payment,
						$gatewayResponse->getTransactionReference(),
						$newTransactionType,
						array('is_transaction_closed' => 0),
						array(),
						Mage::helper('hipay')->getTransactionMessage(
								$payment, $requestType, $gatewayResponse->getTransactionReference(), $amount
						)
				);
				
				$payment->setIsTransactionPending(1);
				$order->save();
				break;
				
			case self::STATE_DECLINED:
		
				$reason = $gatewayResponse->getReason();
				$this->addTransaction(
						$payment,
						$gatewayResponse->getTransactionReference(),
						$newTransactionType,
						array('is_transaction_closed' => 0),
						array(
								$this->_realTransactionIdKey => $gatewayResponse->getTransactionReference(),
								$this->_isTransactionFraud => true
						),
						Mage::helper('hipay')->getTransactionMessage(
								$payment, $requestType, null, $amount,true,"Code: ".$reason['code']." " . Mage::helper('hipay')->__("Reason") . " : ".$reason['message']
						)
				);

				
				if ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
					$order->unhold();
				}
				
				if (!$status = $this->getConfigData('order_status_payment_refused')) {
					$status = $order->getStatus();
				}
				
				if($fraudScreening = $gatewayResponse->getFraudScreening())
				{

					if(isset($fraudScreening['result']) && $fraudScreening['result'] == 'blocked' )
					{
						$payment->setIsFraudDetected(true);
						
						if(defined('Mage_Sales_Model_Order::STATUS_FRAUD'))
							$status = Mage_Sales_Model_Order::STATUS_FRAUD;

						$order->addStatusToHistory($status, Mage::helper('hipay')->getTransactionMessage(
								$payment, $this->getOperation(), null, $amount,true,$gatewayResponse->getMessage()
						));
					}
	
				}
				
				
	
				if ($status == Mage_Sales_Model_Order::STATE_HOLDED && $order->canHold()) {
					$order->hold();
				} elseif ($status == Mage_Sales_Model_Order::STATE_CANCELED && $order->canCancel()) {
					$order->cancel();
				}
				
		
				$order->addStatusToHistory($status, Mage::helper('hipay')->getTransactionMessage(
						$payment, $this->getOperation(), null, $amount,true,$gatewayResponse->getMessage()
				));
		
				$order->save();
		
				break;
				
			case self::STATE_ERROR:
			default:
				Mage::throwException($defaultExceptionMessage);
				break;
				
		}	
	}
	
	/**
	 *
	 * @param Allopass_Hipay_Model_Api_Response_Gateway $gatewayResponse
	 * @param Mage_Sales_Model_Order_Payment $payment
	 * @param float $amount
	 */
	public function processResponseToRedirect($gatewayResponse,$payment,$amount)
	{
	
		$order = $payment->getOrder();
		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
	
		switch ($this->getConfigPaymentAction()) {
			case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE:
				$requestType = self::OPERATION_AUTHORIZATION;
				$newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH;
				$defaultExceptionMessage = Mage::helper('hipay')->__('Payment authorization error.');
				break;
			case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE:
				$requestType = self::OPERATION_SALE;
				$newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE;
				$defaultExceptionMessage = Mage::helper('hipay')->__('Payment capturing error.');
				break;
		}
	
		switch ($gatewayResponse->getState())
		{
			case self::STATE_COMPLETED:
				return Mage::getUrl('checkout/onepage/success');
	
			case self::STATE_FORWARDING:
				$payment->setIsTransactionPending(1);
				$order->save();
				return  $gatewayResponse->getForwardUrl();
	
			case self::STATE_PENDING:
				if($this->getConfigData('re_add_to_cart'))
					$this->getHelper()->reAddToCart($order->getIncrementId());
	
				return Mage::getUrl($this->getConfigData('pending_redirect_page'));
	
			case self::STATE_DECLINED:
			
				if($this->getConfigData('re_add_to_cart'))
					$this->getHelper()->reAddToCart($order->getIncrementId());

				return Mage::getUrl('checkout/onepage/failure');
	
			case self::STATE_ERROR:
			default:
	
				if($this->getConfigData('re_add_to_cart'))
					$this->getHelper()->reAddToCart($order->getIncrementId());
	
				$this->_getCheckout()->setErrorMessage($defaultExceptionMessage);
				return Mage::getUrl('checkout/onepage/failure');
	
		}
	}
	
	/**
	 *
	 * @return Allopass_Hipay_Helper_Data $helper
	 */
	protected function getHelper()
	{
		return Mage::helper('hipay');
	}
	
	
	/**
	 * 
	 * @param Mage_Customer_Model_Customer $customer
	 * @param Allopass_Hipay_Model_Api_Response_Gateway $response
	 */
	protected function responseToCustomer($customer,$response)
	{
		$this->getHelper()->responseToCustomer($customer,$response);
		return $this;
	}
	
	/**
	 *
	 * @param Mage_Sales_Model_Order_Payment $payment
	 * @param float $amount
	 * @return @return Mage_Payment_Model_Abstract
	 */
	public function refund(Varien_Object $payment, $amount)
	{
		parent::refund($payment, $amount);
		
		$transactionId = $payment->getLastTransId();
		
		$gatewayParams = array('operation'=>'refund','amount'=>$amount);
		/* @var $request Allopass_Hipay_Model_Api_Request */
		$request = Mage::getModel('hipay/api_request',array($this));
		$action = Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_MAINTENANCE . $transactionId;
		
		
		$this->_debug($gatewayParams);
		
		$gatewayResponse = $request->gatewayRequest($action,$gatewayParams);
		
		$this->_debug($gatewayResponse->debug());
		
		
		switch ($gatewayResponse->getStatus())
		{
			case "124":
			case "125":
				
				/* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
				$creditmemo = $payment->getCreditmemo();		
				$creditmemo->setState(Mage_Sales_Model_Order_Creditmemo::STATE_OPEN);
				
				break;
			default:
				Mage::throwException( $gatewayResponse->getStatus() . " ==> " .$gatewayResponse->getMessage());
				break;
		}
		
		return $this;
	}
	
	/**
	 *
	 * @param Mage_Sales_Model_Order_Payment $payment
	 * @param float $amount
	 * @param string|null $token
	 * @return multitype:
	 */
	public function getGatewayParams($payment,$amount,$token=null)
	{
	
		$params = array();
	
		$params['orderid'] = $payment->getOrder()->getIncrementId();
	
		$paymentProduct = null;
	
		$params['description'] = Mage::helper('hipay')->__("Order %s by %s",$payment->getOrder()->getIncrementId(),$payment->getOrder()->getCustomerEmail());//MANDATORY
		$params['long_description'] = "";// optional
		$params['currency'] = $payment->getOrder()->getOrderCurrencyCode();
		$params['amount'] = $amount;
		$params['shipping'] = $payment->getOrder()->getShippingAmount();
		$params['tax'] = $payment->getOrder()->getTaxAmount();
		$params['cid'] = $payment->getOrder()->getCustomerId();//CUSTOMER ID
		$params['ipaddr'] = !is_null($payment->getOrder()->getXForwardedFor()) ? $payment->getOrder()->getXForwardedFor() : $payment->getOrder()->getRemoteIp();
	
		$params['http_accept'] = "*/*";
		$params['http_user_agent'] = Mage::helper('core/http')->getHttpUserAgent();
		$params['language'] = Mage::app()->getLocale()->getLocaleCode();//strpos(Mage::app()->getLocale()->getLocaleCode(), "fr") !== false ? "fr_FR" : 'en';
	
		/**
		 * Parameters specific to the payment product
		 */
		if(!is_null($token))
			$params['cardtoken'] = $token;
		
		$params['authentication_indicator'] = 0;
		
		switch ((int)$this->getConfigData('use_3d_secure')) {
			case 1:
				$params['authentication_indicator'] = 1;
				break;
			case 2:
				/* @var $rule Allopass_Hipay_Model_Rule */
				$rule = Mage::getModel('hipay/rule')->load($this->getConfigData('config_3ds_rules'));
				if($rule->getId())
					$params['authentication_indicator'] = (int)$rule->validate($payment->getOrder());
				break;
		}

	
		/**
		 * Electronic Commerce Indicator
		*/
		if($payment->getAdditionalInformation('use_oneclick'))
			$params['eci'] = 9; //Recurring E-commerce
	
		/**
		 * Redirect urls
		 */
		$params['accept_url'] = Mage::getUrl($this->getConfigData('accept_url'));
		$params['decline_url'] = Mage::getUrl($this->getConfigData('decline_url'));
		$params['pending_url'] = Mage::getUrl($this->getConfigData('pending_url'));
		$params['exception_url'] = Mage::getUrl($this->getConfigData('exception_url'));
		$params['cancel_url'] = Mage::getUrl($this->getConfigData('cancel_url'));
	
		$params = $this->getCustomerParams($payment,$params);
		$params = $this->getShippingParams($payment,$params);
	
	
		return $params;
	}
	
	/**
	 *
	 * @param Mage_Sales_Model_Order_Payment $payment
	 * @param array $params
	 * @return array $params
	 */
	protected function getCustomerParams($payment,$params=array())
	{
		$order = $payment->getOrder();
		$params['email'] = $order->getCustomerEmail();
		$params['phone'] = $order->getBillingAddress()->getTelephone();
		if(($dob = $order->getCustomerDob()) != "")
		{
			$dob = new Zend_Date($dob);
			$params['birthdate'] = $dob->toString('YYYYMMdd');
		}
	
		$gender = $order->getCustomerGender();
	
		$customer = Mage::getModel('customer/customer');
		$customer->setData('gender',$gender);
		$attribute = $customer->getResource()->getAttribute('gender');
		if($attribute)
		{
			$gender = $attribute->getFrontend()->getValue($customer);
			$gender = strtoupper(substr($gender, 0,1));
		}
		
		if($gender != "M" && $gender != "F")
			$gender = "U";
		
	
		$params['gender'] =$gender ;
		$params['firstname'] = $order->getCustomerFirstname();
		$params['lastname'] = $order->getCustomerLastname();
		$params['recipientinfo'] = $order->getBillingAddress()->getCompany();
		$params['streetaddress'] = $order->getBillingAddress()->getStreet1();
		$params['streetaddress2'] = $order->getBillingAddress()->getStreet2();
		$params['city'] = $order->getBillingAddress()->getCity();
		//$params['state'] = $order->getBillingAddress(); //TODO checck if country is US or Canada
		$params['zipcode'] = $order->getBillingAddress()->getPostcode();
		$params['country'] = $order->getBillingAddress()->getCountry();
	
		return $params;
	}
	
	/**
	 *
	 * @param Mage_Sales_Model_Order_Payment $payment
	 * @param array $params
	 * @return array $params
	 */
	protected function getShippingParams($payment,$params =array())
	{
		if($payment->getOrder()->getIsVirtual())
			return $params;
			
		$shippingAddress = $payment->getOrder()->getShippingAddress();
		$params['shipto_firstname'] = $shippingAddress->getFirstname();
		$params['shipto_lastname'] = $shippingAddress->getLastname();
		$params['shipto_recipientinfo'] = $shippingAddress->getCompany();
		$params['shipto_streetaddress'] = $shippingAddress->getStreet1();
		$params['shipto_streetaddress2'] = $shippingAddress->getStreet2();
		$params['shipto_city'] = $shippingAddress->getCity();
		//$params['shipto_state'] = $shippingAddress; //TODO check if country is US or Canada
		$params['shipto_zipcode'] = $shippingAddress->getPostcode();
		$params['shipto_country'] = $shippingAddress->getCountry();
	
		return $params;
	}
	
	/**
	 * Return true if there are authorized transactions
	 *
	 * @param Mage_Sales_Model_Order_Payment $payment
	 * @return bool
	 */
	protected function isPreauthorizeCapture($payment)
	{
		$lastTransaction = $payment->getTransaction($payment->getLastTransId());
		if (!$lastTransaction
		|| (($this->getOperation() == self::OPERATION_SALE) && ($lastTransaction->getTxnType() == Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH ) )
		|| $lastTransaction->getTxnType() != Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH 
		) {
			return false;
		}
	
		return true;
	}
	
	/**
	 *
	 * @param Mage_Sales_Model_Order_Payment $payment
	 * @param float $amount
	 */
	protected function _preauthorizeCapture($payment,$amount)
	{
		$transactionId = $payment->getLastTransId();
	
		$gatewayParams = array('operation'=>'capture','amount'=>$amount);
		$this->_debug($gatewayParams);
		/* @var $request Allopass_Hipay_Model_Api_Request */
		$request = Mage::getModel('hipay/api_request',array($this));
		$uri = Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_MAINTENANCE . $transactionId;
	
		$gatewayResponse = $request->gatewayRequest($uri,$gatewayParams);
	
		$this->_debug($gatewayResponse->debug());
	
		switch ($gatewayResponse->getStatus())
		{
			case "117": //Capture requested
			case "118": //Capture
			case "119": //Partially Capture
				$this->addTransaction(
				$payment,
				$gatewayResponse->getTransactionReference(),
				Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
				array('is_transaction_closed' => 0),
				array(),
				Mage::helper('hipay')->getTransactionMessage(
				$payment, self::OPERATION_MAINTENANCE_CAPTURE, $gatewayResponse->getTransactionReference(), $amount
				)
				);
	
				$payment->setIsTransactionPending(true);
				break;
			default:
				Mage::throwException( $gatewayResponse->getStatus() . " ==> " .$gatewayResponse->getMessage());
				break;
		}
	
		return $this;
	}
	
	
	/**
	 * Add payment transaction
	 *
	 * @param Mage_Sales_Model_Order_Payment $payment
	 * @param string $transactionId
	 * @param string $transactionType
	 * @param array $transactionDetails
	 * @param array $transactionAdditionalInfo
	 * @return null|Mage_Sales_Model_Order_Payment_Transaction
	 */
	public function addTransaction(Mage_Sales_Model_Order_Payment $payment, $transactionId, $transactionType,
			array $transactionDetails = array(), array $transactionAdditionalInfo = array(), $message = false
	) {
		$payment->setTransactionId($transactionId);
		if(method_exists($payment, "resetTransactionAdditionalInfo"))
			$payment->resetTransactionAdditionalInfo();
		foreach ($transactionDetails as $key => $value) {
			$payment->setData($key, $value);
		}
		foreach ($transactionAdditionalInfo as $key => $value) {
			$payment->setTransactionAdditionalInfo($key, $value);
		}
		
		if(!class_exists("Mage_Sales_Model_Order_Payment_Transaction"))
			return null;
		
		if(method_exists($payment, "addTransaction"))
			$transaction = $payment->addTransaction($transactionType, null, false , $message);
		else 
			$transaction = $this->_addTransaction($payment, $transactionType,null,false);
	
		/**
		 * It for self using
		*/
		$transaction->setMessage($message);
	
		return $transaction;
	}
	
	/**
	 * Create transaction, prepare its insertion into hierarchy and add its information to payment and comments
	 *
	 * To add transactions and related information, the following information should be set to payment before processing:
	 * - transaction_id
	 * - is_transaction_closed (optional) - whether transaction should be closed or open (closed by default)
	 * - parent_transaction_id (optional)
	 * - should_close_parent_transaction (optional) - whether to close parent transaction (closed by default)
	 *
	 * If the sales document is specified, it will be linked to the transaction as related for future usage.
	 * Currently transaction ID is set into the sales object
	 * This method writes the added transaction ID into last_trans_id field of the payment object
	 *
	 * To make sure transaction object won't cause trouble before saving, use $failsafe = true
	 *
	 * @param Mage_Sales_Model_Order_Payment
	 * @param string $type
	 * @param Mage_Sales_Model_Abstract $salesDocument
	 * @param bool $failsafe
	 * @return null|Mage_Sales_Model_Order_Payment_Transaction
	 */
	protected function _addTransaction($payment,$type, $salesDocument = null, $failsafe = false)
	{
		// look for set transaction ids
		$transactionId = $payment->getTransactionId();
		if (null !== $transactionId) {
			// set transaction parameters
			/*$transaction = Mage::getModel('sales/order_payment_transaction')
			->setOrderPaymentObject($payment)
			->setTxnType($type)
			->setTxnId($transactionId)
			->isFailsafe($failsafe)
			;*/
			
			// set transaction parameters
			//$transaction = false;
			$transaction = $this->_lookupTransaction($payment,$transactionId);
			
			if (!$transaction) {
				$transaction = Mage::getModel('sales/order_payment_transaction')->setTxnId($transactionId);
			}
			
			$transaction
			->setOrderPaymentObject($payment)
			->setTxnType($type)
			->isFailsafe($failsafe);
			
			if ($payment->hasIsTransactionClosed()) {
				$transaction->setIsClosed((int)$payment->getIsTransactionClosed());
			}
	
			//set transaction addition information
			/*if ($payment->_transactionAdditionalInfo) {
				foreach ($payment->_transactionAdditionalInfo as $key => $value) {
					$transaction->setAdditionalInformation($key, $value);
				}
			}*/
	
			// link with sales entities
			$payment->setLastTransId($transactionId);
			$payment->setCreatedTransaction($transaction);
			$payment->getOrder()->addRelatedObject($transaction);
			if ($salesDocument && $salesDocument instanceof Mage_Sales_Model_Abstract) {
				$salesDocument->setTransactionId($transactionId);
				// TODO: linking transaction with the sales document
			}
	
			// link with parent transaction Not used because transaction Id is the same
			$parentTransactionId = $payment->getParentTransactionId();
	
			if ($parentTransactionId) {
				$transaction->setParentTxnId($parentTransactionId);
				if ($payment->getShouldCloseParentTransaction()) {
					$parentTransaction = $this->_lookupTransaction($payment,$parentTransactionId);//
					if ($parentTransaction) {
						$parentTransaction->isFailsafe($failsafe)->close(false);
						$payment->getOrder()->addRelatedObject($parentTransaction);
					}
				}
			}
			return $transaction;
		}
	}
	
	/**
	 * Find one transaction by ID or type
	 * @param Mage_Sales_Model_Order_Payment
	 * @param string $txnId
	 * @param string $txnType
	 * @return Mage_Sales_Model_Order_Payment_Transaction|false
	 */
	protected function _lookupTransaction($payment,$txnId, $txnType = false)
	{
		$_transactionsLookup = array();
		if (!$txnId) {
			if ($txnType && $payment->getId()) {
				$collection = Mage::getModel('sales/order_payment_transaction')->getCollection()
				->addPaymentIdFilter($payment->getId())
				->addTxnTypeFilter($txnType);
				foreach ($collection as $txn) {
					$txn->setOrderPaymentObject($payment);
					$_transactionsLookup[$txn->getTxnId()] = $txn;
					return $txn;
				}
			}
			return false;
		}
		if (isset($_transactionsLookup[$txnId])) {
			return $_transactionsLookup[$txnId];
		}
		$txn = Mage::getModel('sales/order_payment_transaction')
		->setOrderPaymentObject($payment)
		->loadByTxnId($txnId);
		if ($txn->getId()) {
			$_transactionsLookup[$txnId] = $txn;
		} else {
			$_transactionsLookup[$txnId] = false;
		}
		return $_transactionsLookup[$txnId];
	}
	
 	/**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return boolean
     */
    public function canUseForCurrency($currencyCode)
    {
       /* if (!in_array($currencyCode, $this->_allowCurrencyCode)) {
            return false;
        }*/
        return true;
    }
	
	
	/**
	 *
	 * @return Mage_Checkout_Model_Session $checkout
	 */
	protected function _getCheckout()
	{
		return Mage::getSingleton('checkout/session');
	}
	
	/**
	 * Log debug data to file
	 *
	 * @param mixed $debugData
	 */
	protected function _debug($debugData)
	{
		if ($this->getDebugFlag()) {
			Mage::getModel('hipay/log_adapter', 'payment_' . $this->getCode() . '.log')
			->setFilterDataKeys($this->_debugReplacePrivateDataKeys)
			->log($debugData);
		}
	}
	
	/**
	 * Define if debugging is enabled
	 *
	 * @return bool
	 */
	public function getDebugFlag()
	{
		return $this->getConfigData('debug');
	}
	
	/**
	 * Used to call debug method from not Payment Method context
	 *
	 * @param mixed $debugData
	 */
	public function debugData($debugData)
	{
		$this->_debug($debugData);
	}
	
	

}