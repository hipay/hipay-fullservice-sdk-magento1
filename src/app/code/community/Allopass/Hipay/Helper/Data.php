<?php

/**
 * HiPay Fullservice SDK Magento 1
 *
 * 2018 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2018 HiPay
 * @license   https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 */
require_once(dirname(__FILE__) . '/Enum/ScopeConfig.php');

/**
 *
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Helper_Data extends Mage_Core_Helper_Abstract
{
    const TYPE_ITEM_BASKET_GOOD = "good";
    const TYPE_ITEM_BASKET_FEE = "fee";
    const TYPE_ITEM_BASKET_DISCOUNT = "discount";


    const FIELD_BASE_INVOICED = 'row_invoiced';
    const FIELD_BASE_DISCOUNT_INVOICED = 'discount_invoiced';
    const FIELD_BASE_TAX_INVOICED = 'tax_invoiced';
    const FIELD_BASE_ROW = 'row_total_incl_tax';
    const FIELD_BASE_DISCOUNT = 'discount_amount';
    const FIELD_BASE_TAX = 'tax_amount';
    const FIELD_BASE_REFUNDED = 'amount_refunded';
    const FIELD_DISCOUNT_REFUNDED = 'discount_refunded';
    const FIELD_TAX_REFUNDED = 'tax_refunded';
    const FIELD_BASE_DISCOUNT_REFUNDED = 'base_discount_refunded';
    const FIELD_BASE_TAX_REFUNDED = '_base_tax_refunded';

    const FIELD_BASE_TAX_HIDDEN_INVOICED = 'hidden_tax_invoiced';
    const FIELD_BASE_TAX_HIDDEN_REFUNDED = 'hidden_tax_amount';
    const FIELD_BASE_TAX_HIDDEN = 'hidden_tax_refunded';

    const STATE_AUTHORIZATION = '0';
    const STATE_REFUND = '1';
    const STATE_CAPTURE = '2';
    const EPSYLON = 0.00001;

    const DEFAULT_CATEGORY_CODE = 1;

    const LOG_INTERNAL_HIPAY = 'hipay_general_debug';

    /**
     *  Return to TPP Tax rate only if all products have the same tax
     *
     * @param Mage_Sales_Model_Order
     * @return json
     */
    public function getTaxeRateInformation($order)
    {
        $products = $order->getAllItems();
        $taxPercentbasket = 0;

        // =============================================================== //
        // For  each product in basket
        // =============================================================== //
        foreach ($products as $key => $product) {
            $item = array();
            // For configurable products
            if ($product->getParentItem()) {
                $productParent = $product->getParentItem();

                // Check if simple product override configurable his parent
                $taxPercent = $product->getData('tax_percent');

                if (!empty($taxPercent) && $product->getData('tax_percent') > 0) {
                    $product->getData('tax_percent');
                } else {
                    $productParent->getData('tax_percent');
                }
            } else {
                $taxPercent = $product->getData('tax_percent');
            }

            // Checking
            if ($product->getProductType() == 'simple') {
                // Check if taxe rate is the same for all products
                if ($taxPercentbasket == 0) {
                    $taxPercentbasket = $taxPercent;
                } else {
                    if ($taxPercentbasket != $taxPercent) {
                        $taxPercentbasket = 0;
                    }
                }
            }
        }

        return $taxPercentbasket;
    }

    /**
     * Calculate unit price for one product and quantity
     *
     * @param $product
     * @param $quantity
     * @return float|int
     * @throws Mage_Core_Model_Store_Exception
     */
    public function returnUnitPrice($product, $quantity)
    {
        $useOrderCurrency = Mage::getStoreConfig('hipay/hipay_api/currency_transaction', Mage::app()->getStore());

        if (!$useOrderCurrency) {
            return $product->getBasePrice() + $product->getBaseTaxAmount() / $quantity;
        } else {
            return $product->getPrice() + $product->getTaxAmount() / $quantity;
        }
    }

    /**
     * @param int $profile The split payment profile ID
     * @param float $amount The order's total amount
     * @param int $taxAmount The order's tax amount
     * @param string|integer|Zend_Date|array $baseDate A suitable argument for the Zend_Date constructor, used as base date for the splits. Default is today.
     * @return array The split payments list, ordered by date to pay
     * @throws Mage_Core_Exception
     */
    public function splitPayment($profile, $amount, $baseDate = null, $taxAmount = 0)
    {
        $paymentsSplit = array();

        if (is_int($profile)) {
            $profile = Mage::getModel('hipay/paymentProfile')->load($profile);
        }

        if ($profile) {
            $maxCycles = (int)$profile->getPeriodMaxCycles();

            $periodFrequency = (int)$profile->getPeriodFrequency();
            $periodUnit = $profile->getPeriodUnit();

            $todayDate = new Zend_Date($baseDate);

            if ($maxCycles < 1) {
                Mage::throwException(
                    "Period max cycles is equals zero or negative for Payment Profile ID: " . $profile->getId()
                );
            }

            $part = (int)($amount / $maxCycles);
            $taxPart = $taxAmount / $maxCycles;

            $fmod = fmod($amount, $maxCycles);

            for ($i = 0; $i < $maxCycles; $i++) {
                $todayClone = clone $todayDate;
                switch ($periodUnit) {
                    case Allopass_Hipay_Model_PaymentProfile::PERIOD_UNIT_MONTH:
                        {
                            $dateToPay = $todayClone->addMonth($periodFrequency * $i)->getDate()->toString(
                                'yyyy-MM-dd'
                            );
                            break;
                        }
                    case Allopass_Hipay_Model_PaymentProfile::PERIOD_UNIT_DAY:
                        {
                            $dateToPay = $todayClone->addDay($periodFrequency * $i)->getDate()->toString('yyyy-MM-dd');

                            break;
                        }
                    case Allopass_Hipay_Model_PaymentProfile::PERIOD_UNIT_SEMI_MONTH:
                        {
                            $dateToPay = $todayClone->addDay(15 * $periodFrequency * $i)->getDate()->toString(
                                'yyyy-MM-dd'
                            );
                            break;
                        }
                    case Allopass_Hipay_Model_PaymentProfile::PERIOD_UNIT_WEEK:
                        {
                            $dateToPay = $todayClone->addWeek($periodFrequency * $i)->getDate()->toString('yyyy-MM-dd');
                            break;
                        }
                    case Allopass_Hipay_Model_PaymentProfile::PERIOD_UNIT_YEAR:
                        {
                            $dateToPay = $todayClone->addYear($periodFrequency * $i)->getDate()->toString('yyyy-MM-dd');
                            break;
                        }
                }

                $amountToPay = $i == 0 ? ($part + $fmod) : $part;
                $paymentsSplit[] = array(
                    'dateToPay' => $dateToPay,
                    'amountToPay' => $amountToPay,
                    'taxAmountToPay' => $taxPart,
                    'totalAmount' => $taxAmount
                );
            }

            return $paymentsSplit;
        }

        Mage::throwException("Payment Profile not found");
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param $profile
     * @param $customerId
     * @param $cardToken
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     * @throws Zend_Date_Exception
     */
    public function insertSplitPayment($order, $profile, $customerId, $cardToken)
    {
        $useOrderCurrency = Mage::getStoreConfig('hipay/hipay_api/currency_transaction', Mage::app()->getStore());

        if ($useOrderCurrency) {
            $total = $order->getGrandTotal();
        } else {
            $total = $order->getBaseGrandTotal();
        }

        if (is_int($profile)) {
            $profile = Mage::getModel('hipay/paymentProfile')->load($profile);
        }

        if (!$this->splitPaymentsExists($order->getId())) {
            $taxAmount = $order->getTaxAmount();
            $paymentsSplit = $this->splitPayment($profile, $total, $order->getCreatedAtDate(), $taxAmount);

            //remove last element because the first split is already paid
            $numberSplit = 1;
            foreach ($paymentsSplit as $split) {
                $splitPayment = Mage::getModel('hipay/splitPayment');
                $data = array(
                    'order_id' => $order->getId(),
                    'real_order_id' => (int)$order->getRealOrderId(),
                    'customer_id' => $customerId,
                    'card_token' => $cardToken,
                    'total_amount' => $total,
                    'amount_to_pay' => $split['amountToPay'],
                    'tax_amount_to_pay' => $split['taxAmountToPay'],
                    'total_tax_amount' => $split['totalAmount'],
                    'date_to_pay' => $split['dateToPay'],
                    'method_code' => $order->getPayment()->getMethod(),
                    'status' => Allopass_Hipay_Model_SplitPayment::SPLIT_PAYMENT_STATUS_PENDING,
                    'split_number' => (string)$numberSplit . '-' . (string)count($paymentsSplit),
                );

                // First split is already paid
                if ($numberSplit == 1) {
                    $data['status'] = Allopass_Hipay_Model_SplitPayment::SPLIT_PAYMENT_STATUS_COMPLETE;
                }

                $splitPayment->setData($data);

                try {
                    $splitPayment->save();
                } catch (Exception $e) {
                    Mage::throwException("Error on save split payments!");
                }

                $numberSplit++;
            }
        }
    }

    /**
     *
     * @param int $orderId
     * @return boolean
     */
    public function splitPaymentsExists($orderId)
    {
        $collection = Mage::getModel('hipay/splitPayment')->getCollection()->addFieldToFilter('order_id', $orderId);
        if ($collection->getSize() > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param $signature
     * @param bool $fromNotification
     * @param null $order
     * @param bool $isMoto
     * @return bool
     */
    public function checkSignature($signature, $fromNotification = false, $order = null, $isMoto = false)
    {
        $storeId = $order->getStore()->getId();
        $secretMoto = $this->getConfig()->getSecretPassphraseMoto($storeId);
        $secretMotoTest = $this->getConfig()->getSecretPassphraseTestMoto($storeId);
        $passphrase = $isMoto && $secretMoto ? $secretMoto : $this->getConfig()->getSecretPassphrase($storeId);
        $environment = $isMoto && $secretMoto ? ScopeConfig::PRODUCTION_MOTO : ScopeConfig::PRODUCTION;
        if ($order !== null) {
            if ($order->getId()) {
                $method = $order->getPayment()->getMethodInstance();
                if ($method->getConfigData('is_test_mode')) {
                    $passphrase = $isMoto && $secretMotoTest ? $secretMotoTest : $this->getConfig(
                    )->getSecretPassphraseTest($storeId);
                    $environment = $isMoto && $secretMotoTest ? ScopeConfig::TEST_MOTO : ScopeConfig::TEST;
                }
            }
        }

        if (empty($passphrase) && empty($signature)) {
            return true;
        }

        $hashAlgorithm = $this->getConfig()->getConfigHashing($environment, $storeId);
        $isValidSignature = HiPay\Fullservice\Helper\Signature::isValidHttpSignature($passphrase, $hashAlgorithm);

        // Fallback hashing configuration
        if (!$isValidSignature
            && !HiPay\Fullservice\Helper\Signature::isSameHashAlgorithm($passphrase, $hashAlgorithm)
        ) {
            Mage::helper('hipay')->debug(
                'Signature is not valid, try to sync new hashing configuration for ' . $environment .
                ' and store ID ' . $storeId
            );
            $request = Mage::getModel('hipay/api_request');
            $scope = "stores";
            try {
                $request->setEnvironment($environment);
                if ($request->existsCredentials($storeId)) {
                    $retry = Mage::helper('hipay')->synchronizeSecuritySettings($request, $storeId, $scope);
                    if ($retry) {
                        $hashAlgorithm = $this->getConfig()->getConfigHashing($environment, $storeId);
                        Mage::helper('hipay')->debug('Configuration is updated, try again valid signature');
                        $isValidSignature = HiPay\Fullservice\Helper\Signature::isValidHttpSignature(
                            $passphrase,
                            $hashAlgorithm
                        );
                    }
                }
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::helper('hipay')->debug('Error with retry hashing configuration . ' . $e->getMessage());
            }
        }

        return $isValidSignature;
    }

    /**
     * @return array
     */
    public function getHipayMethods()
    {
        $methods = array();

        foreach (Mage::getStoreConfig('payment') as $code => $data) {
            if (strpos($code, 'hipay') !== false) {
                if (isset($data['model'])) {
                    $methods[$code] = $data['model'];
                }
            }
        }

        return $methods;
    }

    /**
     * @param $customer
     * @return bool
     */
    public function checkIfCcExpDateIsValid($customer)
    {
        if (is_int($customer)) {
            $customer = Mage::getModel('customer/customer')->load($customer);
        }

        $expDate = $customer->getHipayCcExpDate();
        $alias = $customer->getHipayAliasOneclick();
        if (!empty($expDate) && !empty($alias)) {
            list($expMonth, $expYear) = explode("-", $expDate);

            return $this->checkIfCcIsExpired($expMonth, $expYear);
        }

        return false;
    }

    /**
     * @param $expMonth
     * @param $expYear
     * @return bool
     */
    public function checkIfCcIsExpired($expMonth, $expYear)
    {
        $today = new Zend_Date(Mage::app()->getLocale()->storeTimeStamp());

        $currentYear = (int)$today->getYear()->toString("YY");
        $currentMonth = (int)$today->getMonth()->toString("MM");

        if ($currentYear > (int)$expYear) {
            return false;
        }

        if ($currentYear == (int)$expYear && $currentMonth > (int)$expMonth) {
            return false;
        }

        return true;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param Allopass_Hipay_Model_Api_Response_Gateway $response
     * @param bool $isRecurring
     * @return $this
     */
    public function responseToCustomer($customer, $response, $isRecurring = false)
    {
        $paymentMethod = $response->getPaymentMethod();
        $paymentProduct = $response->getPaymentProduct();
        $token = isset($paymentMethod['token']) ? $paymentMethod['token'] : $response->getData('cardtoken');

        if ($isRecurring) {
            $customer->setHipayAliasRecurring($token);
        } else {
            $customer->setHipayAliasOneclick($token);
        }

        if (isset($paymentMethod['card_expiry_month']) && $paymentMethod['card_expiry_year']) {
            $customer->setHipayCcExpDate(
                $paymentMethod['card_expiry_month'] . "-" . $paymentMethod['card_expiry_year']
            );
        } else {
            $customer->setHipayCcExpDate(
                substr(
                    $response->getData('cardexpiry'),
                    4,
                    2
                ) . "-" . substr($response->getData('cardexpiry'), 0, 4)
            );
        }

        $customer->setHipayCcNumberEnc(
            isset($paymentMethod['pan']) ? $paymentMethod['pan'] : $response->getData('cardpan')
        );
        $customer->setHipayCcType($paymentProduct);

        $customer->getResource()->saveAttribute($customer, 'hipay_alias_oneclick');
        $customer->getResource()->saveAttribute($customer, 'hipay_cc_exp_date');
        $customer->getResource()->saveAttribute($customer, 'hipay_cc_number_enc');
        $customer->getResource()->saveAttribute($customer, 'hipay_cc_type');

        return $this;
    }

    /**
     * @param $ccToken
     * @param int $customer_id
     * @return bool
     */
    protected function _cardTokenExist($ccToken, $customer_id = 0)
    {
        $cards = Mage::getResourceModel('hipay/card_collection')
                     ->addFieldToSelect('card_id')
                     ->addFieldToFilter('cc_token', $ccToken);

        if ($customer_id > 0) {
            $cards->addFieldToFilter('customer_id', $customer_id);
        }

        return $cards->count() > 0;
    }

    /**
     * @param $customerId
     * @param $response
     * @param bool $isRecurring
     * @return false|Mage_Core_Model_Abstract|null
     */
    public function createCustomerCardFromResponse($customerId, $response, $isRecurring = false)
    {
        $paymentMethod = $response->getPaymentMethod();
        $paymentProduct = $response->getPaymentProduct();
        $token = isset($paymentMethod['token']) ? $paymentMethod['token'] : $response->getData('cardtoken');

        if ($this->_cardTokenExist($token, $customerId)) {
            return null;
        }

        $pan = isset($paymentMethod['pan']) ? $paymentMethod['pan'] : $response->getData('cardpan');

        $cardHolder = isset($paymentMethod['card_holder']) ? $paymentMethod['card_holder'] : "";

        $newCard = Mage::getModel('hipay/card');
        $newCard->setCustomerId($customerId);
        $newCard->setCcToken($token);
        $newCard->setCcNumberEnc($pan);
        $newCard->setCcType($paymentProduct);
        $newCard->setCcOwner($cardHolder);
        $newCard->setCcStatus(Allopass_Hipay_Model_Card::STATUS_ENABLED);
        $newCard->setName($this->__('Card %s - %s', $paymentProduct, $pan));
        $newCard->setCreatedAt((new DateTime())->format('Y-m-d'));

        if (isset($paymentMethod['card_expiry_month']) && $paymentMethod['card_expiry_year']) {
            $newCard->setCcExpMonth($paymentMethod['card_expiry_month']);
            $newCard->setCcExpYear($paymentMethod['card_expiry_year']);
        } else {
            $newCard->setCcExpMonth(substr($response->getData('cardexpiry'), 4, 2));
            $newCard->setCcExpYear(substr($response->getData('cardexpiry'), 0, 4));
        }

        try {
            $newCard->save();
            return $newCard;
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * @param $incrementId
     * @throws Exception
     */
    public function reAddToCart($incrementId)
    {
        $cart = Mage::getSingleton('checkout/cart');
        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);

        if ($order->getId()) {
            $items = $order->getItemsCollection();
            foreach ($items as $item) {
                try {
                    $cart->addOrderItem($item);
                } catch (Mage_Core_Exception $e) {
                    if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                        Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                    } else {
                        Mage::getSingleton('checkout/session')->addError($e->getMessage());
                    }
                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addException(
                        $e,
                        Mage::helper('checkout')->__('Cannot add the item to shopping cart.')
                    );
                }
            }
        }

        $cart->save();
    }

    /**
     * Return message for gateway transaction request
     *
     * @param $payment
     * @param $requestType
     * @param $lastTransactionId
     * @param bool $amount
     * @param bool $exception
     * @param bool $additionalMessage
     * @return bool|string
     */
    public function getTransactionMessage(
        $payment,
        $requestType,
        $lastTransactionId,
        $amount = false,
        $exception = false,
        $additionalMessage = false
    ) {
        return $this->getExtendedTransactionMessage(
            $payment,
            $requestType,
            $lastTransactionId,
            $amount,
            $exception,
            $additionalMessage
        );
    }

    /**
     * Return message for gateway transaction request
     *
     * @param $payment
     * @param $requestType
     * @param $lastTransactionId
     * @param bool $amount
     * @param bool $exception
     * @param bool $additionalMessage
     * @return bool|mixed
     */
    public function getExtendedTransactionMessage(
        $payment,
        $requestType,
        $lastTransactionId,
        $amount = false,
        $exception = false,
        $additionalMessage = false
    ) {
        $operation = 'Operation: ' . $requestType;

        if (!$operation) {
            return false;
        }

        if ($amount) {
            $amount = $this->__('amount: %s', $this->_formatPrice($payment, $amount));
        }

        if ($exception) {
            $result = $this->__('failed');
        } else {
            $result = $this->__('successful');
        }

        $card = $this->__('Credit Card: xxxx-%s', $payment->getCcLast4());
        $cardType = $this->__('Card type: %s', ucfirst($this->getCcTypeHipay($payment->getCcType())));

        $pattern = '%s - %s.<br /> %s<br /> %s.<br /> %s';
        $texts = array($operation, $result, $card, $amount, $cardType);

        if ($lastTransactionId !== null) {
            $pattern .= '<br />%s.';
            $texts[] = $this->__('Hipay Transaction ID %s', $lastTransactionId);
        }

        if ($additionalMessage) {
            $pattern .= '<br />%s.';
            $texts[] = $additionalMessage;
        }

        return call_user_func_array(array($this, '__'), array_merge(array($pattern), $texts));
    }

    /**
     * Format price with currency sign
     * @param  Mage_Payment_Model_Info $payment
     * @param float $amount
     * @return string
     */
    protected function _formatPrice($payment, $amount)
    {
        return $payment->getOrder()->getBaseCurrency()->formatTxt($amount);
    }


    /**
     * Send email id payment is in Fraud status
     * @param Mage_Customer_Model_Customer|Mage_Core_Model_Abstract $receiver
     * @param Mage_Sales_Model_Order $order
     * @param string $message
     * @return Mage_Checkout_Helper_Data
     */
    public function sendFraudPaymentEmail($receiver, $order, $message, $email_key = 'fraud_payment')
    {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $mailTemplate = Mage::getModel('core/email_template');
        /* @var $mailTemplate Mage_Core_Model_Email_Template */

        $template = Mage::getStoreConfig('hipay/' . $email_key . '/template', $order->getStoreId());

        $copyTo = $this->_getEmails('hipay/' . $email_key . '/copy_to', $order->getStoreId());
        $copyMethod = Mage::getStoreConfig('hipay/' . $email_key . '/copy_method', $order->getStoreId());
        if ($copyTo && $copyMethod == 'bcc') {
            $mailTemplate->addBcc($copyTo);
        }

        $sendTo = array(
            array(
                'email' => $receiver->getEmail(),
                'name' => $receiver->getName()
            )
        );

        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $sendTo[] = array(
                    'email' => $email,
                    'name' => null
                );
            }
        }

        $shippingMethod = '';
        if ($shippingInfo = $order->getShippingAddress()->getShippingMethod()) {
            $data = explode('_', $shippingInfo);
            $shippingMethod = $data[0];
        }

        $paymentMethod = '';
        if ($paymentInfo = $order->getPayment()) {
            $paymentMethod = $paymentInfo->getMethod();
        }

        $items = '';
        foreach ($order->getAllVisibleItems() as $_item) {
            /* @var $_item Mage_Sales_Model_Quote_Item */
            $items .= $_item->getProduct()->getName() . '  x ' . $_item->getQty() . '  '
                . $order->getStoreCurrencyCode() . ' '
                . $_item->getProduct()->getFinalPrice($_item->getQty()) . "\n";
        }

        $total = $order->getStoreCurrencyCode() . ' ' . $order->getGrandTotal();

        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $order->getStoreId()))
                         ->sendTransactional(
                             $template,
                             Mage::getStoreConfig('hipay/' . $email_key . '/identity', $order->getStoreId()),
                             $recipient['email'],
                             $recipient['name'],
                             array(
                                 'reason' => $message,
                                 'dateAndTime' => Mage::app()->getLocale()->date(),
                                 'customer' => $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
                                 'customerEmail' => $order->getCustomerEmail(),
                                 'billingAddress' => $order->getBillingAddress(),
                                 'shippingAddress' => $order->getShippingAddress(),
                                 'shippingMethod' => Mage::getStoreConfig('carriers/' . $shippingMethod . '/title'),
                                 'paymentMethod' => Mage::getStoreConfig('payment/' . $paymentMethod . '/title'),
                                 'items' => nl2br($items),
                                 'total' => $total
                             )
                         );
        }

        $translate->setTranslateInline(true);

        return $this;
    }

    /**
     * @param $configPath
     * @param $storeId
     * @return array|bool
     */
    protected function _getEmails($configPath, $storeId)
    {
        $data = Mage::getStoreConfig($configPath, $storeId);
        if (!empty($data)) {
            return explode(',', $data);
        }

        return false;
    }

    /**
     *
     * @return Allopass_Hipay_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('hipay/config');
    }

    /**
     * @param $ccTypeMagento
     * @param bool $exceptionIfNotFound
     * @return mixed
     * @throws Mage_Core_Exception
     */
    public function getCcTypeHipay($ccTypeMagento, $exceptionIfNotFound = false)
    {
        $ccTypes = Mage::getSingleton('hipay/config')->getCcTypesHipay();

        if (isset($ccTypes[$ccTypeMagento])) {
            return $ccTypes[$ccTypeMagento];
        }

        if ($exceptionIfNotFound) {
            Mage::throwException(Mage::helper('hipay')->__("Code Credit Card Type Hipay not found!"));
        }

        return $ccTypeMagento;
    }

    /**
     * @param $use3dSecure
     * @param $config3dsRules
     * @param bool $payment
     * @return int
     */
    public function is3dSecure($use3dSecure, $config3dsRules, $payment = false)
    {
        $params = 0;
        if ($use3dSecure > 0 && !$payment) {
            $params = 1;
        } else {
            switch ((int)$use3dSecure) {
                case 1:
                    $params = 1;
                    break;
                case 2:
                case 3:
                    /* @var $rule Allopass_Hipay_Model_Rule */
                    $rule = Mage::getModel('hipay/rule')->load($config3dsRules);
                    if ($rule->getId() && $rule->validate($payment->getOrder())) {
                        $params = 1;

                        if ((int)$use3dSecure == 3) {//case for force 3ds if rules are validated
                            $params = 2;
                        }
                    }
                    break;
                case 4:
                    $params = 2;
                    break;
            }
        }
        return $params;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return string
     */
    public function getCheckoutSuccessPage($payment)
    {
        // if empty success page magento
        $url = Mage::getStoreConfig('payment/' . $payment->getMethod() . '/success_redirect_page');
        return empty($url) ? Mage::getUrl('checkout/onepage/success') : $url;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return string
     */
    public function getCheckoutFailurePage($payment)
    {
        return is_null(Mage::getStoreConfig('payment/' . $payment->getMethod() . '/failure_redirect_page')) ?
            'checkout/onepage/failure' :
            Mage::getStoreConfig('payment/' . $payment->getMethod() . '/failure_redirect_page');
    }

    /**
     *  Return informations for TPP about the request
     *
     * @return string
     */
    public function getRequestSource()
    {
        $request = array();

        $request['source'] = 'CMS';
        $request['brand'] = 'magento';
        $request['brand_version'] = Mage::getVersion();
        $request['integration_version'] = strval(Mage::getConfig()->getNode('modules')->Allopass_Hipay->version);

        return json_encode($request);
    }

    /**
     * Return customs data from Hipay
     *
     * @param $payment
     * @param $amount
     * @param $method
     * @param null $split_number
     * @return array
     */
    public function getCustomData($payment, $amount, $method, $split_number = null)
    {
        $customData = array();

        // Shipping description
        $customData['shipping_description'] = $payment->getOrder()->getShippingDescription();

        // Customer information
        $customer = $payment->getOrder()->getCustomerId();
        $customerData = Mage::getModel('customer/customer')->load($customer);
        $codeCustomer = Mage::getModel('customer/group')->load($customerData->getGroupId())->getCustomerGroupCode();
        $customData['customer_code'] = $codeCustomer;

        // Method payment information
        $customData['payment_code'] = $method->getCode();
        $customData['display_iframe'] = $method->getConfigData('display_iframe');

        //add url to order in BO Magento
        $customData['url'] = Mage::getUrl(
            'adminhtml/sales_order/view',
            array('_secure' => true, 'order_id' => $payment->getOrder()->getId())
        );

        // Payment type
        if ($split_number) {
            $customData['payment_type'] = 'Split ' . $split_number;
        }

        // Use Onclick
        if ($payment->getAdditionalInformation('use_oneclick') == '1') {
            $customData['payment_type'] = 'OneClick';
        }

        return $customData;
    }

    /**
     * Send an email to customer to pay his order
     *
     * @param $receiver
     * @param $order
     * @return $this
     */
    public function sendLinkPaymentEmail($receiver, $order)
    {
        $email_key = 'hipay_api_moto';
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $mailTemplate = Mage::getModel('core/email_template');
        /* @var $mailTemplate Mage_Core_Model_Email_Template */

        $template = Mage::getStoreConfig('hipay/' . $email_key . '/template', $order->getStoreId());

        $sendTo = array(
            array(
                'email' => $receiver->getEmail(),
                'name' => $receiver->getName()
            )
        );

        $shippingMethod = '';
        if ($shippingInfo = $order->getShippingAddress()->getShippingMethod()) {
            $data = explode('_', $shippingInfo);
            $shippingMethod = $data[0];
        }

        $paymentMethod = '';
        if ($paymentInfo = $order->getPayment()) {
            $paymentMethod = $paymentInfo->getMethod();
        }

        $items = '';
        foreach ($order->getAllVisibleItems() as $_item) {
            /* @var $_item Mage_Sales_Model_Quote_Item */
            $items .= $_item->getProduct()->getName() . '  x ' . $_item->getQty() . '  '
                . $order->getStoreCurrencyCode() . ' '
                . $_item->getProduct()->getFinalPrice($_item->getQty()) . "\n";
        }
        $total = $order->getStoreCurrencyCode() . ' ' . $order->getGrandTotal();

        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $order->getStoreId()))
                         ->sendTransactional(
                             $template,
                             Mage::getStoreConfig('hipay/' . $email_key . '/identity', $order->getStoreId()),
                             $recipient['email'],
                             $recipient['name'],
                             array(
                                 'redirectUrl' => $paymentInfo->getAdditionalInformation('redirectUrl'),
                                 'dateAndTime' => Mage::app()->getLocale()->date(),
                                 'customer' => $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
                                 'customerEmail' => $order->getCustomerEmail(),
                                 'billingAddress' => $order->getBillingAddress(),
                                 'shippingAddress' => $order->getShippingAddress(),
                                 'shippingMethod' => Mage::getStoreConfig('carriers/' . $shippingMethod . '/title'),
                                 'paymentMethod' => Mage::getStoreConfig('payment/' . $paymentMethod . '/title'),
                                 'items' => nl2br($items),
                                 'total' => $total
                             )
                         );
        }

        $translate->setTranslateInline(true);

        return $this;
    }

    /**
     * Debug internal process ( For cron for example )
     *
     * @param $debugData
     */
    public function debug($debugData)
    {
        if ($this->getConfig()->isGeneralDebugEnabled()) {
            Mage::getModel('hipay/log_adapter', self::LOG_INTERNAL_HIPAY . '.log')->log($debugData);
        }
    }

    /**
     *  Get All Magento Categories (Level 2)
     *
     *  If Store ID in context, get Category from root ID
     *
     * @return array
     */
    public function getMagentoCategories()
    {
        $options = array();
        $storeId = Mage::getSingleton('adminhtml/config_data')->getStore();
        $categories = Mage::getModel('catalog/category')
                          ->getCollection()
                          ->addAttributeToFilter('level', 2)
                          ->addAttributeToSelect('name')
                          ->addAttributeToSelect('id');

        if ($storeId && !empty($storeId)) {
            $rootCategoryId = Mage::app()->getStore($storeId)->getRootCategoryId();
            $categories = $categories->addAttributeToFilter('path', array('like' => "1/$rootCategoryId/%"));
        }

        foreach ($categories as $category) {
            $options[$category->getId()] = $category->getName();
        }

        return $options;
    }

    /**
     *  Get All Magento Shipping method
     *
     *  According current store
     *
     * @return array
     */
    public function getMagentoShippingMethods()
    {
        $options = array();
        $store = Mage::getSingleton('adminhtml/config_data')->getStore();

        // Get all carriers
        $carriers = Mage::getSingleton('shipping/config')->getAllCarriers($store);
        foreach ($carriers as $carrierCode => $carrierModel) {
            try {
                if (!$carrierModel->isActive()) {
                    continue;
                }

                $carrierMethods = $carrierModel->getAllowedMethods();
                if (!$carrierMethods) {
                    continue;
                }

                $carrierTitle = Mage::getStoreConfig('carriers/' . $carrierCode . '/title');
                foreach ($carrierMethods as $methodCode => $methodTitle) {
                    $options[$carrierCode . '_' . $methodCode] = '[' . $carrierTitle . '] - ' . $methodTitle;
                }
            } catch (Exception $e) {
                // Log error and continue process listing
                continue;
            }
        }
        return $options;
    }

    /**
     *  Return the mapping if exists for one category
     *
     * @param $idCategory
     * @param $storeId int
     * @return string
     */
    public function getMappingCategory($idCategory, $storeId = null)
    {
        $mappingCategories = unserialize($this->getConfig()->getConfigDataBasket('mapping_category', $storeId));
        if (is_array($mappingCategories) && !empty($mappingCategories)) {
            foreach ($mappingCategories as $key => $mapping) {
                if ($mapping['magento_category'] == $idCategory) {
                    return $mapping;
                }
            }
            $category = Mage::getModel('catalog/category')->load($idCategory);
            foreach ($mappingCategories as $key => $mapping) {
                if (in_array($mapping['magento_category'], $category->getParentIds())) {
                    return $mapping;
                }
            }
        }
        return null;
    }

    /**
     * Return the mapping if exist for one category
     *
     * @param $codeShippingMethod
     * @param null $storeId
     * @return mixed|null
     */
    public function getMappingShipping($codeShippingMethod, $storeId = null)
    {
        $mappingDeliveryMethod = unserialize(
            $this->getConfig()->getConfigDataBasket('mapping_shipping_method', $storeId)
        );
        if (is_array($mappingDeliveryMethod) && !empty($mappingDeliveryMethod)) {
            foreach ($mappingDeliveryMethod as $key => $mapping) {
                if ($mapping['magento_shipping_method'] == $codeShippingMethod) {
                    return $mapping;
                }
            }
        }
        return null;
    }

    /**
     * According the mapping, provide a approximated date delivery
     *
     * @param $mapping
     * @return string
     */
    public function calculateEstimatedDate($mapping)
    {
        if (is_array($mapping)) {
            $today = new \Datetime();
            $daysDelay = $mapping['delay_preparation'] + $mapping['delay_delivery'];
            $interval = new \DateInterval("P{$daysDelay}D");
            return $today->add($interval)->format("Y-m-d");
        }

        return '';
    }

    /**
     * Provide a delivery Method compatible with gateway
     *
     * @param $mapping array Result of mapping
     * @return null|string JSON
     */
    public function getMappingShippingMethod($mapping)
    {
        if (is_array($mapping)) {
            $itemsDelivery = Mage::helper('hipay/collection')->getFullItemsDelivery();
            if ($itemsDelivery && is_array($itemsDelivery) && !empty($mapping['hipay_delivery_method'])) {
                return json_encode(
                    array(
                        'mode' => $itemsDelivery[$mapping['hipay_delivery_method']]['mode'],
                        'shipping' => $itemsDelivery[$mapping['hipay_delivery_method']]['shipping']
                    )
                );
            }
        }
        return '';
    }

    /**
     *
     * @param $codeShippingMethod
     * @param $store
     */
    public function processDeliveryInformation($codeShippingMethod, $store, $method, &$params)
    {
        $mapping = $this->getMappingShipping($codeShippingMethod, $store->getId());
        $params['delivery_method'] = $this->calculateDeliveryMethod($mapping);

        if (empty($params['delivery_method'])) {
            Mage::helper('hipay')->debug('### Method processDeliveryInformation');
            Mage::helper('hipay')->debug('### WARNING : Mapping for ' . $codeShippingMethod . ' is missing.');
        }

        $params['delivery_date'] = $this->calculateEstimatedDate($mapping);
    }

    /**
     * Check if send cart items is required or of option is activated
     *
     * @param $productCode
     * @return bool
     * @throws Mage_Core_Model_Store_Exception
     */
    public function isSendCartItemsRequired($productCode)
    {
        return $this->isCartItemsRequired($productCode) ||
            (Mage::getStoreConfigFlag('hipay/hipay_basket/activate_basket', Mage::app()->getStore()));
    }

    /**
     *  Delivery information and Cart Items are mandatory for some payment product
     *
     * @param string $product_code
     * @return boolean
     */
    public function isDeliveryMethodAndCartItemsRequired($product_code)
    {
        return in_array($product_code, array('3xcb', '3xcb-no-fees', '4xcb-no-fees', '4xcb', 'credit-long'));
    }

    /**
     *  Cart Items are mandatory for some payment product
     *
     * @param string $productCode
     * @return boolean
     */
    public function isCartItemsRequired($productCode)
    {
        return in_array(
            $productCode,
            array('klarnainvoice', '3xcb', '3xcb-no-fees', '4xcb-no-fees', '4xcb', 'credit-long')
        );
    }

    /**
     *  Check if all mapping Shipping are done
     *
     * @return int
     */
    public function checkMappingShippingMethod()
    {
        $store = Mage::getSingleton('adminhtml/config_data')->getStore();
        $mappings = unserialize($this->getConfig()->getConfigDataBasket('mapping_shipping_method', $store));
        $magentoShippingMethod = $this->getMagentoShippingMethods();
        $nbMappingMissing = count($magentoShippingMethod);
        if (is_array($magentoShippingMethod) && is_array($mappings)) {
            $nbMapping = 0;
            foreach ($mappings as $mapping) {
                if (!empty($mapping['hipay_delivery_method'])) {
                    $nbMapping++;
                }
            }
            $nbMappingMissing = count($magentoShippingMethod) - $nbMapping;
        }
        return $nbMappingMissing;
    }

    /**
     *  Check if all mapping Category are done
     *
     * @return int
     */
    public function checkMappingCategoryMethod()
    {
        $store = Mage::getSingleton('adminhtml/config_data')->getStore();
        $mappings = unserialize($this->getConfig()->getConfigDataBasket('mapping_category', $store));
        $magentoCategory = $this->getMagentoCategories();
        $nbMappingMissing = count($magentoCategory);

        if (is_array($magentoCategory) && is_array($mappings)) {
            $nbMapping = 0;
            foreach ($mappings as $mapping) {
                if (!empty($mapping['hipay_category'])) {
                    $nbMapping++;
                }
            }
            $nbMappingMissing = count($magentoCategory) - $nbMapping;
        }
        return $nbMappingMissing;
    }

    /**
     * Convert Hours in second
     *
     * @param $time float in Hours
     * @return int in second
     */
    public function convertHoursToSecond($time)
    {
        return intval($time * 3600);
    }

    /**
     * Update hashing configuration with Hipay back office configuration
     *
     * @param $request
     * @param int $storeId
     * @param string $scope
     * @param $session
     *
     * @return bool
     */
    public function synchronizeSecuritySettings($request, $storeId, $scope, $session = null)
    {
        $gatewayResponse = $request->gatewayRequest(
            Allopass_Hipay_Model_Api_Request::GATEWAY_SECURITY_SETTINGS,
            null,
            $storeId
        );

        Mage::helper('hipay')->debug($gatewayResponse->debug());
        return $this->updateHashingConfiguration($gatewayResponse, $storeId, $scope, $request, $session);
    }

    /**
     *  Update hashing configuration from response from gateway
     *
     * @param $gatewayResponse
     * @return boolean
     */
    private function updateHashingConfiguration($gatewayResponse, $storeId, $scope, $request, $session = null)
    {
        $updating = false;
        $environment = $request->getEnvironment();
        $config = Mage::getSingleton('hipay/config');
        if (isset($gatewayResponse["hashing_algorithm"]) && !empty($gatewayResponse["hashing_algorithm"])) {
            $hashingAlgorithm = $gatewayResponse["hashing_algorithm"];
            if ($config->getConfigHashing($environment, $storeId) != $hashingAlgorithm) {
                $config->setConfigDataHashing($environment, $hashingAlgorithm, $storeId, $scope);
                $message = $this->__(
                        'The hash configuration for "' . ScopeConfig::getLabelFromEnvironment(
                            $environment
                        ) . '" has been updated with '
                    ) . $hashingAlgorithm;
                $updating = true;
                Mage::app()->getStore()->resetConfig();
            } else {
                $message = $this->__(
                        'The hash configuration for "' . ScopeConfig::getLabelFromEnvironment(
                            $environment
                        ) . '"" was already updated with '
                    ) . $hashingAlgorithm;
            }
        } else {
            if ($session) {
                $session->addError(
                    $this->__(
                        'The hash configuration has not been updated. Please check the configuration in the hipay back office.'
                    )
                );
            }
        }

        if ($session) {
            $session->addSuccess($message);
        }

        return $updating;
    }

    public function getIpAddress($_payment)
    {
        $remoteIp = $_payment->getOrder()->getRemoteIp();

        //Check if it's forwarded and in this case, explode and retrieve the first part
        if ($_payment->getOrder()->getXForwardedFor() !== null) {
            if (strpos($_payment->getOrder()->getXForwardedFor(), ",") !== false) {
                $xfParts = explode(",", $_payment->getOrder()->getXForwardedFor());
                $remoteIp = current($xfParts);
            } else {
                $remoteIp = $_payment->getOrder()->getXForwardedFor();
            }
        }

        return $remoteIp;
    }

    public function readVersionDataFromConf(){
        $config = Mage::getSingleton('hipay/config');
        $info = $config->getVersionInfo();

        if(!$info){
            $info = new stdClass();
        }

        $info->version = $this->getExtensionVersion();


        return $info;
    }

    /**
     * @return string
     */
    public function getExtensionVersion()
    {
        return (string)Mage::getConfig()->getModuleConfig('Allopass_Hipay')->version;
    }
}
