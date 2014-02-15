<?php
/**
 *
 * @method string getState() transaction state. completed,forwarding, pending, declined, error
 * @method array getReason() optional element. Reason why transaction was declined.
 * @method string getForwardUrl() optional element. Merchant must redirect the customer's browser to this URL.
 * @method bool getTest() true if the transaction is a testing transaction, otherwise false
 * @method int getMid() your merchant account number (issued to you by Allopass).
 * @method int getAttemptId() attempt id of the payment.
 * @method string getAuthorizationCode() an authorization code (up to 35 characters) generated for each approved or pending transaction by the acquiring provider.
 * @method string getTransactionReference() the unique identifier of the transaction.
 * @method DateTime getDateCreated() time when transaction was created.
 * @method DateTime getDateUpdated() time when transaction was last updated.
 * @method DateTime getDateAuthorized() time when transaction was authorized.
 * @method string getStatus() transaction status.
 * @method string getMessage() transaction message.
 * @method string getAuthorizedAmount() the transaction amount.
 * @method string getCapturedAmount() captured amount.
 * @method string getRefundedAmount() refunded amount.
 * @method string getDecimals() decimal precision of transaction amount..
 * @method string getCurrency() base currency for this transaction.
 * @method string getIpAddress() the IP address of the customer making the purchase.
 * @method string getIpCountry() country code associated to the customer's IP address.
 * @method string getEci() Electronic Commerce Indicator (ECI).
 * @method string getPaymentProduct() payment product used to complete the transaction.
 * @method string getPaymentMethod() base currency for this transaction.
 * @method array getFraudScreening() Result of the fraud screening.
 *
 */
class Allopass_Hipay_Model_Api_Response_Gateway extends Allopass_Hipay_Model_Api_Response_Abstract
{
	public function getForwardUrl()
	{
		return $this->getData('forwardUrl');
	}
	
	public function getAttemptId()
	{
		return $this->getData('attemptId');
	}
	
	public function getAuthorizationCode()
	{
		return $this->getData('authorizationCode');
	}
	
	
	public function getTransactionReference()
	{
		if($this->getData('transactionReference') == '')
			return $this->getData('reference');
		
		return $this->getData('transactionReference');
	}
	
	
	public function getDateCreated()
	{
		return $this->getData('dateCreated');
	}
	
	
	public function getDateUpdated()
	{
		return $this->getData('dateUpdated');
	}
	
	
	public function getDateAuthorized()
	{
		return $this->getData('dateAuthorized');
	}
	
	public function getAuthorizedAmount()
	{
		return $this->getData('authorizedAmount');
	}
	
	public function getCapturedAmount()
	{
		return $this->getData('capturedAmount');
	}
	
	public function getRefundedAmount()
	{
		return $this->getData('refundedAmount');
	}
	
	public function getIpAddress()
	{
		return $this->getData('ipAddress');
	}
	
	public function getIpCountry()
	{
		return $this->getData('ipCountry');
	}
	
	public function getPaymentProduct()
	{
		return $this->getData('paymentProduct');
	}
	
	public function getPaymentMethod()
	{
		return $this->getData('paymentMethod');
	}
	
	public function getFraudScreening()
	{
		return $this->getData('fraudScreening');
	}
	
}