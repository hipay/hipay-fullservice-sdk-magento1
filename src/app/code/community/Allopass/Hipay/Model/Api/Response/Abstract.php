<?php

/**
 *
 * @method string getToken() card token
 * @method string getBrand() card type
 * @method string getPan() card number masked
 * @method string getCardHolder() Cardholder name
 * @method int getCardExpiryMonth() card expiry month (2 digits)
 * @method int getCardExpiryYear() card expiry year (4 digits)
 * @method string getIssuer() card issuing bank name
 * @method string getCountry() bank country code (ISO 3166-1, 2 letters)
 *
 */
abstract class Allopass_Hipay_Model_Api_Response_Abstract extends Varien_Object
{

}
