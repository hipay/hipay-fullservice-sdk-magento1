<?php
class Allopass_Hipay_Model_Method_Astropay extends Allopass_Hipay_Model_Method_AbstractOrder
{	
	
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

		 // Validate CPF format 
		 if ($this->_typeIdentification == 'cpf'){
			if (!preg_match("/(\d{2}[.]?\d{3}[.]?\d{3}[\/]?\d{4}[-]?\d{2})|(\d{3}[.]?\d{3}[.]?\d{3}[-]?\d{2})$/",$paymentInfo->getAdditionalInformation('national_identification_number'))){
				Mage::throwException(Mage::helper('payment')->__('CPF is not valid.' . $paymentInfo->getAdditionalInformation('national_identification_number')));
			}
		 }

		 // Validate CPN format
		if ($this->_typeIdentification == 'cpn'){
			if (!preg_match("/^[a-zA-Z]{4}\d{6}[a-zA-Z]{6}\d{2}$/",$paymentInfo->getAdditionalInformation('national_identification_number'))){
				Mage::throwException(Mage::helper('payment')->__('CPN is incorrect.'));
			}
		 }
		
         return $this;
	}

	/**
    *  Return the type for national identification number
	*
    *  @return string
    */
	public function getTypeNationalIdentification(){
		return $this->_typeIdentification;
	}
}