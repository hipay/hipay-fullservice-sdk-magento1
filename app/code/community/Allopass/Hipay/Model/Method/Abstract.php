<?php
abstract class Allopass_Hipay_Model_Method_Abstract extends Mage_Payment_Model_Method_Abstract
{
	const OPERATION_SALE = "Sale";	
	const OPERATION_AUTHORIZATION = "Authorization";
	const OPERATION_MAINTENANCE_CAPTURE = "Capture";
	const OPERATION_MAINTENANCE_REFUND = "Refund";
	const OPERATION_MAINTENANCE_ACCEPT_CHALLENGE = 'acceptChallenge';
	const OPERATION_MAINTENANCE_DENY_CHALLENGE = 'denyChallenge';
	
	
	const STATE_COMPLETED = "completed";
	const STATE_FORWARDING = "forwarding";
	const STATE_PENDING = "pending";
	const STATE_DECLINED = "declined";
	const STATE_ERROR = "error";
	
	const STATUS_AUTHORIZATION_REQUESTED = 'authorization_requested';
	const STATUS_EXPIRED = 'expired';
	const STATUS_PARTIAL_REFUND = 'partial_refund';
	const STATUS_PARTIAL_CAPTURE = 'partial_capture';
	const STATUS_CAPTURE_REQUESTED = 'capture_requested';
	const STATUS_PENDING_CAPTURE = 'pending_capture';
	
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
	protected $_canUseInternal          = true;
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
	
	public function assignInfoData($info,$data)
	{
		$info->setAdditionalInformation('create_oneclick',$data->getOneclick() == "create_oneclick" ? 1 : 0)
		->setAdditionalInformation('use_oneclick',$data->getOneclick() == "use_oneclick" ? 1 : 0)
		->setAdditionalInformation('selected_oneclick_card',$data->getOneclickCard() == "" ? 0 : $data->getOneclickCard())
		->setAdditionalInformation('split_payment_id',$data->getSplitPaymentId() != "" ? $data->getSplitPaymentId() : 0);
		
		
	}
	
	
	public function acceptPayment(Mage_Payment_Model_Info $payment)
	{
		
		$gatewayParams = array('operation'=>self::OPERATION_MAINTENANCE_ACCEPT_CHALLENGE,'amount'=>$amount);
		$this->_debug($gatewayParams);
		/* @var $request Allopass_Hipay_Model_Api_Request */
		$request = Mage::getModel('hipay/api_request',array($this));
		$uri = Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_MAINTENANCE . $transactionId;
		
		$gatewayResponse = $request->gatewayRequest($uri,$gatewayParams,$payment->getOrder()->getStoreId());
		
		$this->_debug($gatewayResponse->debug());
		
		return $this;
	}
	
	public function denyPayment(Mage_Payment_Model_Info $payment)
	{
		
		/*@var $payment Mage_Sales_Model_Order_Payment */
		parent::denyPayment($payment);
		$transactionId = $payment->getLastTransId();
		$amount = $payment->getAmountOrdered();
		
		$transactionId = $payment->getLastTransId();
		
		$gatewayParams = array('operation'=>self::OPERATION_MAINTENANCE_DENY_CHALLENGE,'amount'=>$amount);
		$this->_debug($gatewayParams);
		/* @var $request Allopass_Hipay_Model_Api_Request */
		$request = Mage::getModel('hipay/api_request',array($this));
		$uri = Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_MAINTENANCE . $transactionId;
		
		$gatewayResponse = $request->gatewayRequest($uri,$gatewayParams,$payment->getOrder()->getStoreId());
		
		$this->_debug($gatewayResponse->debug());
		
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
						
						$this->_setFraudDetected($gatewayResponse,$customer, $payment);
						
						$order->setState($state,$status,$gatewayResponse->getMessage());
						
						$order->save();
						break;
						
					case 142: //Authorized Requested
						if($order->getStatus() == self::STATUS_CAPTURE_REQUESTED || $order->getStatus() == Mage_Sales_Model_Order::STATE_PROCESSING
								|| $order->getStatus() == Mage_Sales_Model_Order::STATE_COMPLETE || $order->getStatus() == Mage_Sales_Model_Order::STATE_CLOSED
								|| $order->getStatus() == self::STATUS_PENDING_CAPTURE )// for logic process
							break;
						
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
							$status = self::STATUS_AUTHORIZATION_REQUESTED;
						
							$order->setState($state,$status,$gatewayResponse->getMessage());
						
							$order->save();
							break;
							
					case 114: //Expired
						if($order->getStatus() != self::STATUS_PENDING_CAPTURE)// for logic process
							break;
					
							$this->addTransaction(
									$payment,
									$gatewayResponse->getTransactionReference(),
									Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID,
									array('is_transaction_closed' => 1),
									array(
											$this->_realTransactionIdKey => $gatewayResponse->getTransactionReference(),
									),
									Mage::helper('hipay')->getTransactionMessage(
											$payment, self::OPERATION_AUTHORIZATION, $gatewayResponse->getTransactionReference(), $amount,true
									)
							);
							$state = Mage_Sales_Model_Order::STATE_CLOSED;
							$status = self::STATUS_EXPIRED;
					
							$order->setState($state,$status,$gatewayResponse->getMessage());
					
							$order->save();
							break;
					
					case 116: //Authorized
						
						if($order->getStatus() == 'capture_requested' || $order->getStatus() == 'processing' 
								|| $order->getStatus() == 'complete' || $order->getStatus() == 'closed' )// for logic process
							break;
						if(!$this->isPreauthorizeCapture($payment))
							$this->addTransaction(
									$payment,
									$gatewayResponse->getTransactionReference(),
									Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
									array('is_transaction_closed' => 0),
									array(),
									Mage::helper('hipay')->getTransactionMessage(
											$payment, self::OPERATION_AUTHORIZATION, null, $amount
									)
							);
						
						$order->setState(
								Mage_Sales_Model_Order::STATE_PROCESSING,
								self::STATUS_PENDING_CAPTURE,
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
						
						if($order->getStatus() == 'capture' || $order->getStatus() == 'processing' )// for logic process
							break;
						
						$this->addTransaction(
								$payment,
								$gatewayResponse->getTransactionReference(),
								Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
								array('is_transaction_closed' => 0),
								array(),
								Mage::helper('hipay')->getTransactionMessage(
										$payment, self::OPERATION_SALE, null, $amount
								)
						);
						
						$message = Mage::helper("hipay")->__('Capture Requested by Hipay.');

						$order->setState(
								Mage_Sales_Model_Order::STATE_PROCESSING, 'capture_requested', $message, null, false
						);

						if(((int)$this->getConfigData('hipay_status_validate_order') == 117) === false )
							break;
						
					case 118: //Capture
						
						if($order->getStatus() == $this->getConfigData('order_status_payment_accepted') )
						{
						 	break;
						}
						
						if ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
							$order->unhold();
						}
						
						// Create invoice
						if ($this->getConfigData('invoice_create',$order->getStoreId()) && !$order->hasInvoices()) {
							
							$invoice = $this->create_invoice($order, $gatewayResponse->getTransactionReference());
							Mage::getModel('core/resource_transaction')
							->addObject($invoice)->addObject($invoice->getOrder())
							->save();
						
						}
						elseif($order->hasInvoices())
						{
							foreach ($order->getInvoiceCollection() as $invoice)
							{
								if($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_OPEN && round(($invoice->getBaseGrandTotal() + $order->getBaseTotalPaid()),2) == $gatewayResponse->getCapturedAmount())
								{
									$invoice->pay();
									Mage::getModel('core/resource_transaction')
									->addObject($invoice)->addObject($invoice->getOrder())
									->save();
									
								}
							}
						}
						
						if(($profile = (int)$payment->getAdditionalInformation('split_payment_id')) && $customer->getId())
						{
							$token = isset( $gatewayResponse->paymentMethod['token']) ? $gatewayResponse->paymentMethod['token'] : $gatewayResponse->getData('cardtoken');
							$this->getHelper()->insertSplitPayment($order, $profile,$customer->getId(),$token);
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
							$order->setData('state',Mage_Sales_Model_Order::STATE_COMPLETE);
							$order->addStatusToHistory($status, $message, true);
							/*$order->setState(
									Mage_Sales_Model_Order::STATE_COMPLETE, $status, $message, null, false
							);*/
						} else {
							$order->addStatusToHistory($status, $message, true);
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
					default:
						$message = Mage::helper("hipay")->__('Message Hipay: %s. Status: %s',$gatewayResponse->getMessage(),$gatewayResponse->getStatus());						
						$order->addStatusToHistory($order->getStatus(), $message);
						break;
				}
				
		
				if($gatewayResponse->getState() == self::STATE_COMPLETED)
				{				
						if(in_array($gatewayResponse->getPaymentProduct(), array('visa','american-express','mastercard','cb')) 
							&& ((int)$gatewayResponse->getEci() == 9 || $payment->getAdditionalInformation('create_oneclick')) 
							&& !$order->isNominal()) //Recurring E-commerce
						{
								
							if($customer->getId())
							{
								$this->responseToCustomer($customer,$gatewayResponse);
									
							}
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
				
				$this->_setFraudDetected($gatewayResponse,$customer, $payment,true);
				
				
	
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
	 */
	protected function _setFraudDetected($gatewayResponse,$customer,$payment,$addToHistory = false)
	{
		
		
		if($fraudScreening = $gatewayResponse->getFraudScreening())
		{
		
			if(isset($fraudScreening['result']) && isset($fraudScreening['scoring']))
			{
				$order = $payment->getOrder();
				$payment->setIsFraudDetected(true);
		
				if(defined('Mage_Sales_Model_Order::STATUS_FRAUD'))
					$status = Mage_Sales_Model_Order::STATUS_FRAUD;
		
				$payment->setAdditionalInformation('fraud_type',$fraudScreening['result']);
				$payment->setAdditionalInformation('fraud_score',$fraudScreening['scoring']);
				
				if($addToHistory)
					$order->addStatusToHistory($status, Mage::helper('hipay')->getTransactionMessage(
							$payment, $this->getOperation(), null, $amount,true,$gatewayResponse->getMessage()
					));
				
				$message = "";
				
				if($this->getConfigData('send_fraud_payment_email',$order->getStoreId()));
					$this->getHelper()->sendFraudPaymentEmail($customer, $order, $message);
			}
		
		}
	}

	/**
	 * Create object invoice
	 * @param Mage_Sales_Model_Order $order
	 * @param string $transactionReference
	 * @param boolean $capture
	 * @param boolean $paid
	 * @return Mage_Sales_Model_Order_Invoice $invoice 
	 */
	protected function create_invoice($order,$transactionReference,$capture = true,$paid = true)
	{
		$invoice = $order->prepareInvoice();
		$invoice->setTransactionId($transactionReference);	
		
		if($capture)					
			$invoice->register()->capture();
			
		/*if($paid)
			$invoice->setIsPaid(1);*/
		
		return $invoice;
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
		
		$urlAdmin = Mage::getUrl('admin/sales_order/index');
		if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
			$urlAdmin = Mage::getUrl('admin/sales_order/view', array('order_id' => $order->getId()));
		} 
	
		switch ($gatewayResponse->getState())
		{
			case self::STATE_COMPLETED:
				return $this->isAdmin() ? $urlAdmin : Mage::getUrl('checkout/onepage/success');
	
			case self::STATE_FORWARDING:
				$payment->setIsTransactionPending(1);
				$order->save();
				return  $gatewayResponse->getForwardUrl();
	
			case self::STATE_PENDING:
				if($this->getConfigData('re_add_to_cart'))
					$this->getHelper()->reAddToCart($order->getIncrementId());
	
				return $this->isAdmin() ? $urlAdmin : Mage::getUrl($this->getConfigData('pending_redirect_page'));
	
			case self::STATE_DECLINED:
			
				if($this->getConfigData('re_add_to_cart'))
					$this->getHelper()->reAddToCart($order->getIncrementId());

				return $this->isAdmin() ? $urlAdmin : Mage::getUrl('checkout/onepage/failure');
	
			case self::STATE_ERROR:
			default:
	
				if($this->getConfigData('re_add_to_cart'))
					$this->getHelper()->reAddToCart($order->getIncrementId());
	
				$this->_getCheckout()->setErrorMessage($defaultExceptionMessage);
				return $this->isAdmin() ? $urlAdmin : Mage::getUrl('checkout/onepage/failure');
	
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
		$this->getHelper()->createCustomerCardFromResponse($customer->getId(), $response);
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
		
		$gatewayResponse = $request->gatewayRequest($action,$gatewayParams,$payment->getOrder()->getStoreId());
		
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
		
		$longDesc ="";
		
		if(($profile = $payment->getAdditionalInformation('split_payment_id')))
		{
			//Check if this order is already split
			$spCollection = Mage::getModel('hipay/splitPayment')->getCollection()
																->addFieldToFilter('order_id',$payment->getOrder()->getId());
			
			if(!$spCollection->count())
			{
				$longDesc = Mage::helper('hipay')->__('Split payment');
				$paymentsSplit = $this->getHelper()->splitPayment((int)$profile, $amount);
				Mage::log($paymentsSplit,null,'hipay_split_debug.log');
			
				$amount = $paymentsSplit[0]['amountToPay'];
			}
			
		}
		
		$params['description'] = Mage::helper('hipay')->__("Order %s by %s",$payment->getOrder()->getIncrementId(),$payment->getOrder()->getCustomerEmail());//MANDATORY
		$params['long_description'] = $longDesc;// optional
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
		$isAdmin = $this->isAdmin();
		$params['accept_url'] =  $isAdmin ? Mage::getUrl('hipay/adminhtml_payment/accept') : Mage::getUrl($this->getConfigData('accept_url'));
		$params['decline_url'] = $isAdmin ? Mage::getUrl('hipay/adminhtml_payment/decline') : Mage::getUrl($this->getConfigData('decline_url'));
		$params['pending_url'] = $isAdmin ? Mage::getUrl('hipay/adminhtml_payment/pending') : Mage::getUrl($this->getConfigData('pending_url'));
		$params['exception_url'] = $isAdmin ? Mage::getUrl('hipay/adminhtml_payment/exception') : Mage::getUrl($this->getConfigData('exception_url'));
		$params['cancel_url'] = $isAdmin ? Mage::getUrl('hipay/adminhtml_payment/cancel') : Mage::getUrl($this->getConfigData('cancel_url'));
	
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
	 * 
	 * @param Allopass_Hipay_Model_SplitPayment $spiltPayment
	 */
	public function paySplitPayment($splitPayment)
	{
		$request = Mage::getModel('hipay/api_request',array($this));
		
		$order = Mage::getModel('sales/order')->load($splitPayment->getOrderId());
		if($order->getId())
		{
			$gatewayParams =  $this->getGatewayParams($order->getPayment(), $splitPayment->getAmountToPay());
			$gatewayParams['orderid'] .= "-split-".$splitPayment->getId();//added because if the same order_id tpp respond "Max Attempts exceed!"
			$gatewayParams['description'] = Mage::helper('hipay')->__("Order SPLIT %s by %s",$order->getIncrementId(),$order->getCustomerEmail());//MANDATORY;
			$gatewayParams['eci'] = 9;
			$gatewayParams['operation'] =self::OPERATION_SALE;
			$gatewayParams['payment_product'] = $this->getCcTypeHipay($order->getPayment()->getCcType());
			
			/**
			 * Parameters specific to the payment product
			 */
			$gatewayParams['cardtoken'] = $splitPayment->getCardToken();
			
			$gatewayParams['authentication_indicator'] = 0;//$this->getConfigData('use_3d_secure');
			$this->_debug($gatewayParams);
			
			$gatewayResponse = $request->gatewayRequest(Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_ORDER,$gatewayParams);
				
			$this->_debug($gatewayResponse->debug());
			
			
			return $gatewayResponse->getState();
		}
		
		
		
		
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
		
		if(!$lastTransaction)
			return false;
		
		if ($this->getOperation() == self::OPERATION_SALE && $lastTransaction->getTxnType() == Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH  )
			return false;
		
		if($lastTransaction->getTxnType() == Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE && $this->orderDue($payment->getOrder()))
			return true;
		
		if ($lastTransaction->getTxnType() != Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH  ) 
			return false;
	
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
	
		$gatewayResponse = $request->gatewayRequest($uri,$gatewayParams,$payment->getOrder()->getStoreId());
	
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
     * Whether this method can accept or deny payment
     *
     * @param Mage_Payment_Model_Info $payment
     *
     * @return bool
     */
    public function canReviewPayment(Mage_Payment_Model_Info $payment)
    {
    	$fraud_type = $payment->getAdditionalInformation('fraud_type');
    	return $this->_canReviewPayment || $fraud_type == 'challenged';
    }
	
	protected function orderDue($order)
	{
		return $order->hasInvoices() && $order->getBaseTotalDue() > 0;
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
	
	public function isAdmin()
	{
		return Mage::app()->getStore()->isAdmin();
	}
	
	

}