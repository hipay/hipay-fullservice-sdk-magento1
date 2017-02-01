<?php

class Allopass_Hipay_Helper_Data extends Mage_Core_Helper_Abstract
{
    const TYPE_ITEM_BASKET_GOOD = "good";
    const TYPE_ITEM_BASKET_FEE = "fee";
    const TYPE_ITEM_BASKET_DISCOUNT = "discount";


    const FIELD_BASE_INVOICED = 'base_row_invoiced';
    const FIELD_BASE__DISCOUNT_INVOICED = 'base_discount_invoiced';
    const FIELD_BASE_TAX_INVOICED = 'base_tax_invoiced';
    const FIELD_BASE = 'base_row_total';
    const FIELD_BASE_DISCOUNT = 'base_discount_amount';
    const FIELD_BASE_TAX = 'tax_amount';
    const FIELD_BASE_REFUNDED = 'base_amount_refunded';
    const FIELD_BASE_DISCOUNT_REFUNDED = 'base_discount_refunded';
    const FIELD_BASE_TAX_REFUNDED = 'base_tax_refunded';


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
                $tax_percent = $product->getData('tax_percent');

                if (!empty($tax_percent) && $product->getData('tax_percent') > 0) {
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
     *  Return to TPP API basket informations
     *
     * @param Mage_Sales_Model_Order
     * @return json
     */
    public function getCartInformation(
        $order
    ) {
        $basket = array();
        $products = $order->getAllItems();

        // =============================================================== //
        // Add coupon in basket
        // =============================================================== //
        $coupon = $order->getCouponCode();
        if (!empty($coupon)) {
            $item = array();
            $item['type'] = Allopass_Hipay_Helper_Data::TYPE_ITEM_BASKET_DISCOUNT;
            $item['product_reference'] = $order->getCouponCode();
            $item['name'] = $order->getDiscountDescription();
            $item['discount'] = Mage::app()->getStore()->roundPrice($order->getDiscountAmount());
            $item['total_amount'] = Mage::app()->getStore()->roundPrice($order->getDiscountAmount());
            $item['quantity'] = '1';
            $item['unit_price'] = '0';
            $basket[] = $item;
        }

        // =============================================================== //
        // Add Shipping in basket
        // =============================================================== //
        if ($order->getBaseShippingAmount() > 0) {
            $item = array();
            $item['type'] = Allopass_Hipay_Helper_Data::TYPE_ITEM_BASKET_FEE;
            $item['product_reference'] = $order->getShippingDescription();
            $item['name'] = $order->getShippingDescription();
            $item['quantity'] = '1';
            $item['unit_price'] = Mage::helper('core')->currency($order->getBaseShippingAmount(), false, false);
            $item['total_amount'] = Mage::helper('core')->currency($order->getBaseShippingAmount(), false, false);
            $basket[] = $item;
        }

        // =============================================================== //
        // Add each product in basket
        // =============================================================== //
        foreach ($products as $key => $product) {
            $item = array();

            $fieldBaseRow = Allopass_Hipay_Helper_Data::FIELD_BASE;
            $fieldBaseDiscount = Allopass_Hipay_Helper_Data::FIELD_BASE_DISCOUNT;
            $fieldBaseTax = Allopass_Hipay_Helper_Data::FIELD_BASE_TAX;

            // For configurable products
            if ($product->getParentItem()) {
                $productParent = $product->getParentItem();

                // If partial capture
                if ($productParent->getData('qty_invoiced') > 0 && $productParent->getData('qty_invoiced') != $productParent->getData('qty_ordered')) {
                    $fieldBaseRow = Allopass_Hipay_Helper_Data::FIELD_BASE_INVOICED;
                    $fieldBaseDiscount = Allopass_Hipay_Helper_Data::FIELD_BASE_DISCOUNT_INVOICED;
                    $fieldBaseTax = Allopass_Hipay_Helper_Data::FIELD_BASE_TAX_INVOICED;
                } elseif ($productParent->getData('qty_refunded') > 0) {
                    $fieldBaseRow = Allopass_Hipay_Helper_Data::FIELD_BASE_REFUNDED;
                    $fieldBaseDiscount = Allopass_Hipay_Helper_Data::FIELD_BASE_DISCOUNT_REFUNDED;
                    $fieldBaseTax = Allopass_Hipay_Helper_Data::FIELD_BASE_TAX_REFUNDED;
                }

                // Quantity Invoice is only on Parent item if exist
                if ($productParent->getData('qty_invoiced') > 0 && $productParent->getData('qty_invoiced') != $productParent->getData('qty_ordered')) {
                    $item['quantity'] = intval($productParent->getData('qty_invoiced'));
                } elseif ($productParent->getData('qty_refunded') > 0) {
                    $item['quantity'] = intval($productParent->getData('qty_refunded'));
                } else {
                    $item['quantity'] = intval($productParent->getData('qty_ordered'));
                }

                // Check if simple product override configurable his parent
                $productTaxPercent = $product->getData('tax_percent');
                $basePrice = $product->getData('base_price');
                $baseRow = $product->getData($fieldBaseRow);
                $productSku = $product->getData('sku');
                $productDiscount = $product->getData($fieldBaseDiscount);

                $taxPercent = !empty($productTaxPercent) && $product->getData('tax_percent') > 0 ? $product->getData('tax_percent') : $productParent->getData('tax_percent');

                // Calculation is done because Magento save with 2 digits per default in base_price_incl_tax
                $unitPrice = !empty($basePrice) && $product->getData('base_price') > 0 ? ($product->getData('base_price')) + ($product->getData('tax_percent') / 100 * ($product->getData('base_price'))) : ($productParent->getData('base_price')) + ($productParent->getData('tax_percent') / 100 * ($productParent->getData('base_price')));

                $total_amount = !empty($baseRow) && $product->getData($fieldBaseRow) > 0 ? $product->getData($fieldBaseRow) + $product->getData($fieldBaseTax) - $product->getData($fieldBaseDiscount) : $productParent->getData($fieldBaseRow) + $productParent->getData($fieldBaseTax) - $productParent->getData($fieldBaseDiscount);

                $sku = !empty($productSku) ? $product->getData('sku') : $productParent->getData('sku');
                $discount = (!empty($productDiscount) && $product->getData($fieldBaseDiscount) > 0) ? $product->getData($fieldBaseDiscount) : $productParent->getData($fieldBaseDiscount);
            } else {
                // If partial capture
                if ($product->getData('qty_invoiced') > 0 && $product->getData('qty_invoiced') != $product->getData('qty_ordered')) {
                    $fieldBaseRow = Allopass_Hipay_Helper_Data::FIELD_BASE_INVOICED;
                    $fieldBaseDiscount = Allopass_Hipay_Helper_Data::FIELD_BASE_DISCOUNT_INVOICED;
                    $fieldBaseTax = Allopass_Hipay_Helper_Data::FIELD_BASE_TAX_INVOICED;
                } elseif ($product->getData('qty_refunded') > 0) {
                    $fieldBaseRow = Allopass_Hipay_Helper_Data::FIELD_BASE_REFUNDED;
                    $fieldBaseDiscount = Allopass_Hipay_Helper_Data::FIELD_BASE_DISCOUNT_REFUNDED;
                    $fieldBaseTax = Allopass_Hipay_Helper_Data::FIELD_BASE_TAX_REFUNDED;
                }

                if ($product->getData('qty_invoiced') > 0 && $product->getData('qty_invoiced') != $product->getData('qty_ordered')) {
                    $item['quantity'] = intval($product->getData('qty_invoiced'));
                } elseif ($product->getData('qty_refunded') > 0) {
                    $item['quantity'] = intval($product->getData('qty_refunded'));
                } else {
                    $item['quantity'] = intval($product->getData('qty_ordered'));
                }

                // Basket from product himself ( Simple product )
                $taxPercent = $product->getData('tax_percent');
                $total_amount = $product->getData($fieldBaseRow) + $product->getData($fieldBaseTax) - $product->getData($fieldBaseDiscount);
                $unitPrice = $product->getData('base_price') + ($product->getData('tax_percent') / 100 * $product->getData('base_price'));
                $sku = $product->getData('sku');
                $discount = $product->getData($fieldBaseDiscount);
            }

            // Add information in basket only if the product is simple
            if ($product->getProductType() == 'simple') {


                // if store support EAN ( Please set the attribute on hipay config )
                if (Mage::getStoreConfig('hipay/hipay_basket/attribute_ean', Mage::app()->getStore())) {
                    $attribute = Mage::getStoreConfig('hipay/hipay_basket/attribute_ean', Mage::app()->getStore());

                    if (Mage::getStoreConfig('hipay/hipay_basket/load_product_ean', Mage::app()->getStore())) {
                        $resource = Mage::getSingleton('catalog/product')->getResource();
                        $ean = $resource->getAttributeRawValue($product->getProductId(), $attribute,
                            Mage::app()->getStore());
                    } else {
                        // The custom attribute have to be present in quote and order
                        $ean = $product->getData($attribute);
                    }
                }

                $item['type'] = Allopass_Hipay_Helper_Data::TYPE_ITEM_BASKET_GOOD;
                $item['tax_rate'] = Mage::helper('core')->currency($taxPercent, false, false);
                $item['unit_price'] = Mage::helper('core')->currency(round($unitPrice, 3), false, false);
                $item['total_amount'] = Mage::helper('core')->currency($total_amount, false, false);

                if (!empty($ean) && $ean != 'null') {
                    $item['european_article_numbering'] = $ean;
                }

                $item['product_reference'] = $sku;

                // Get the config for discount application
                $configDiscount = Mage::getStoreConfig('tax/calculation/apply_after_discount',
                    Mage::app()->getStore());

                // Check if Tax is applied before or after discount
                if ($configDiscount == '1') {
                    $item['discount'] = Mage::helper('core')->currency(-round($discount + (($discount * $taxPercent) / 100),
                        3), false, false);
                } else {
                    $item['discount'] = Mage::helper('core')->currency(-round($discount, 3), false, false);
                }

                $basket[] = $item;
            }
        }

        return json_encode($basket);
    }

    /**
     *
     * @param Allopass_Hipay_Model_PaymentProfile|int $profile
     * @param float $amount
     */
    public function splitPayment(
        $profile,
        $amount,
        $taxAmount = 0
    ) {
        $paymentsSplit = array();

        if (is_int($profile)) {
            $profile = Mage::getModel('hipay/paymentProfile')->load($profile);
        }

        if ($profile) {
            $maxCycles = (int)$profile->getPeriodMaxCycles();

            $periodFrequency = (int)$profile->getPeriodFrequency();
            $periodUnit = $profile->getPeriodUnit();

            $todayDate = new Zend_Date();

            if ($maxCycles < 1) {
                Mage::throwException("Period max cycles is equals zero or negative for Payment Profile ID: " . $profile->getId());
            }

            $part = (int)($amount / $maxCycles);
            $taxPart = $taxAmount / $maxCycles;

            //$reste = $amount%$maxCycles;
            $fmod = fmod($amount, $maxCycles);

            for ($i = 0; $i <= ($maxCycles - 1); $i++) {
                $j = $i - 1;
                $todayClone = clone $todayDate;
                switch ($periodUnit) {
                    case Allopass_Hipay_Model_PaymentProfile::PERIOD_UNIT_MONTH: {
                        $dateToPay = $todayClone->addMonth($periodFrequency + $j)->getDate()->toString('yyyy-MM-dd');
                        break;
                    }
                    case Allopass_Hipay_Model_PaymentProfile::PERIOD_UNIT_DAY: {
                        $dateToPay = $todayClone->addDay($periodFrequency + $j)->getDate()->toString('yyyy-MM-dd');

                        break;
                    }
                    case Allopass_Hipay_Model_PaymentProfile::PERIOD_UNIT_SEMI_MONTH://TODO test this case !!!
                    {
                        $dateToPay = $todayClone->addDay(15 + $periodFrequency + $j)->getDate()->toString('yyyy-MM-dd');
                        break;
                    }
                    case Allopass_Hipay_Model_PaymentProfile::PERIOD_UNIT_WEEK: {
                        $dateToPay = $todayClone->addWeek($periodFrequency + $j)->getDate()->toString('yyyy-MM-dd');
                        break;
                    }
                    case Allopass_Hipay_Model_PaymentProfile::PERIOD_UNIT_YEAR: {
                        $dateToPay = $todayClone->addYear($periodFrequency + $j)->getDate()->toString('yyyy-MM-dd');
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
     *
     * @param Mage_Sales_Model_Order $order
     * @param Allopass_Hipay_Model_PaymentProfile|int $profile $profile
     */
    public function insertSplitPayment(
        $order,
        $profile,
        $customerId,
        $cardToken
    ) {
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
            $paymentsSplit = $this->splitPayment($profile, $total, $taxAmount);

            //remove last element because the first split is already paid
            //array_pop($paymentsSplit);
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
                    'split_number' => strval($numberSplit) . '-' . strval(count($paymentsSplit) + 1),
                );

                // First split is already paid
                if ($numberSplit == 1){
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
    public function splitPaymentsExists(
        $orderId
    ) {
        $collection = Mage::getModel('hipay/splitPayment')->getCollection()->addFieldToFilter('order_id', $orderId);
        if ($collection->count()) {
            return true;
        }

        return false;
    }

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

    public function checkSignature(
        $signature,
        $fromNotification = false,
        $response = null
    ) {
        $passphrase = $this->getConfig()->getSecretPassphrase();
        if (!is_null($response)) {
            $orderArr = $response->getOrder();

            /* @var $order Mage_Sales_Model_Order */
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderArr['id']);

            if ($order->getId()) {
                $method = $order->getPayment()->getMethodInstance();
                if ($method->getConfigData('is_test_mode')) {
                    $passphrase = $this->getConfig()->getSecretPassphraseTest();
                }
            }
        }


        if (empty($passphrase) || empty($signature)) {
            return true;
        }

        if ($fromNotification) {
            $rawPostData = file_get_contents("php://input");
            if ($signature == sha1($rawPostData . $passphrase)) {
                ;
            }
            return true;

            return false;
        }


        $parameters = $this->_getRequest()->getParams();
        $string2compute = "";
        unset($parameters['hash']);
        ksort($parameters);
        foreach ($parameters as $name => $value) {
            if (strlen($value) > 0) {
                $string2compute .= $name . $value . $passphrase;
            }
        }

        if (sha1($string2compute) == $signature) {
            return true;
        }

        return false;
    }

    public function checkIfCcExpDateIsValid(
        $customer
    ) {
        if (is_int($customer)) {
            $customer = Mage::getModel('customer/customer')->load($customer);
        }

        $expDate = $customer->getHipayCcExpDate();
        $alias = $customer->getHipayAliasOneclick();
        if (!empty($expDate) && !empty($alias)) {
            list($expMonth, $expYear) = explode("-", $expDate);

            return $this->checkIfCcIsExpired($expMonth, $expYear);

            /*$today = new Zend_Date(Mage::app()->getLocale()->storeTimeStamp());

            $currentYear = (int)$today->getYear()->toString("YY");
            $currentMonth = (int)$today->getMonth()->toString("MM");

            if($currentYear > (int)$expYear)
                return false;

            if($currentYear == (int)$expYear && $currentMonth > (int)$expMonth)
                return false;

            return true;*/
        }

        return false;
    }

    public function checkIfCcIsExpired(
        $expMonth,
        $expYear
    ) {
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
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param Allopass_Hipay_Model_Api_Response_Gateway $response
     * @param boolean $isRecurring
     */
    public function responseToCustomer(
        $customer,
        $response,
        $isRecurring = false
    ) {
        $paymentMethod = $response->getPaymentMethod();
        $paymentProduct = $response->getPaymentProduct();
        $token = isset($paymentMethod['token']) ? $paymentMethod['token'] : $response->getData('cardtoken');

        if ($isRecurring) {
            $customer->setHipayAliasRecurring($token);
        } else {
            $customer->setHipayAliasOneclick($token);
        }

        if (isset($paymentMethod['card_expiry_month']) && $paymentMethod['card_expiry_year']) {
            $customer->setHipayCcExpDate($paymentMethod['card_expiry_month'] . "-" . $paymentMethod['card_expiry_year']);
        } else {
            $customer->setHipayCcExpDate(substr($response->getData('cardexpiry'), 4,
                    2) . "-" . substr($response->getData('cardexpiry'), 0, 4));
        }

        $customer->setHipayCcNumberEnc(isset($paymentMethod['pan']) ? $paymentMethod['pan'] : $response->getData('cardpan'));
        //$customer->setHipayCcType(isset($paymentMethod['brand']) ? strtolower($paymentMethod['brand']) : strtolower($response->getData('cardbrand')));
        $customer->setHipayCcType($paymentProduct);

        $customer->getResource()->saveAttribute($customer, 'hipay_alias_oneclick');
        $customer->getResource()->saveAttribute($customer, 'hipay_cc_exp_date');
        $customer->getResource()->saveAttribute($customer, 'hipay_cc_number_enc');
        $customer->getResource()->saveAttribute($customer, 'hipay_cc_type');

        return $this;
    }

    protected function _cardTokenExist(
        $ccToken,
        $customer_id = 0
    ) {
        $cards = Mage::getResourceModel('hipay/card_collection')
            ->addFieldToSelect('card_id')
            ->addFieldToFilter('cc_token', $ccToken);

        if ($customer_id > 0) {
            $cards->addFieldToFilter('customer_id', $customer_id);
        }

        return $cards->count() > 0;
    }

    public function createCustomerCardFromResponse(
        $customerId,
        $response,
        $isRecurring = false
    ) {
        $paymentMethod = $response->getPaymentMethod();
        $paymentProduct = $response->getPaymentProduct();
        $token = isset($paymentMethod['token']) ? $paymentMethod['token'] : $response->getData('cardtoken');

        if ($this->_cardTokenExist($token, $customerId)) {
            return null;
        }

        $pan = isset($paymentMethod['pan']) ? $paymentMethod['pan'] : $response->getData('cardpan');

        $newCard = Mage::getModel('hipay/card');
        $newCard->setCustomerId($customerId);
        $newCard->setCcToken($token);
        $newCard->setCcNumberEnc($pan);
        $newCard->setCcType($paymentProduct);
        $newCard->setCcStatus(Allopass_Hipay_Model_Card::STATUS_ENABLED);
        $newCard->setName($this->__('Card %s - %s', $paymentProduct, $pan));

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

    public function reAddToCart(
        $incrementId
    ) {
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
                    Mage::getSingleton('checkout/session')->addException($e,
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
     * @param  Mage_Payment_Model_Info $payment
     * @param  string $requestType
     * @param  string $lastTransactionId
     * @param float $amount
     * @param string $exception
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
            $payment, $requestType, $lastTransactionId, $amount, $exception, $additionalMessage
        );
    }

    /**
     * Return message for gateway transaction request
     *
     * @param  Mage_Payment_Model_Info $payment
     * @param  string $requestType
     * @param  string $lastTransactionId
     * @param float $amount
     * @param string $exception
     * @param string $additionalMessage Custom message, which will be added to the end of generated message
     * @return bool|string
     */
    public function getExtendedTransactionMessage(
        $payment,
        $requestType,
        $lastTransactionId,
        $amount = false,
        $exception = false,
        $additionalMessage = false
    ) {
        $operation = 'Operation: ' . $requestType;// $this->_getOperation($requestType);

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

        if (!is_null($lastTransactionId)) {
            $pattern .= '<br />%s.';
            $texts[] = $this->__('Hipay Transaction ID %s', $lastTransactionId);
        }

        if ($additionalMessage) {
            $pattern .= '<br />%s.';
            $texts[] = $additionalMessage;
        }
        //$pattern .= '<br />%s';
        //$texts[] = $exception;

        return call_user_func_array(array($this, '__'), array_merge(array($pattern), $texts));
    }

    /**
     * Format price with currency sign
     * @param  Mage_Payment_Model_Info $payment
     * @param float $amount
     * @return string
     */
    protected function _formatPrice(
        $payment,
        $amount
    ) {
        return $payment->getOrder()->getBaseCurrency()->formatTxt($amount);
    }


    /**
     * Send email id payment is in Fraud status
     * @param Mage_Customer_Model_Customer $receiver
     * @param Mage_Sales_Model_Order $order
     * @param string $message
     * @return Mage_Checkout_Helper_Data
     */
    public function sendFraudPaymentEmail(
        $receiver,
        $order,
        $message,
        $email_key = 'fraud_payment'
    ) {
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

    protected function _getEmails(
        $configPath,
        $storeId
    ) {
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

    public function getCcTypeHipay(
        $ccTypeMagento,
        $exceptionIfNotFound = false
    ) {
        $ccTypes = Mage::getSingleton('hipay/config')->getCcTypesHipay();

        if (isset($ccTypes[$ccTypeMagento])) {
            return $ccTypes[$ccTypeMagento];
        }

        if ($exceptionIfNotFound) {
            Mage::throwException(Mage::helper('hipay')->__("Code Credit Card Type Hipay not found!"));
        }

        return $ccTypeMagento;
    }

    /*
     * TPPMAG1-2 - JPN
     */
    public function is3dSecure(
        $use3dSecure,
        $config3dsRules,
        $payment = false
    ) {
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
     * @return json
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
     *  Return customs data from Hipay
     *
     * @param array $payment
     * @param float $amount
     *
     */
    public function getCustomData(
        $payment,
        $amount,
        $method,
        $split_number = null
    ) {
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
     *
     *  Send an email to customer to pay his order
     *
     * @param $receiver
     * @param $order
     * @param $message
     * @param string $email_key
     * @return $this
     */
    public function sendLinkPaymentEmail(
        $receiver,
        $order
    ) {
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
}
