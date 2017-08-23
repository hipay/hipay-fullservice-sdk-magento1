<?php

abstract class Allopass_Hipay_Model_Method_Abstract extends Mage_Payment_Model_Method_Abstract
{
    const OPERATION_SALE = "Sale";
    const OPERATION_AUTHORIZATION = "Authorization";
    const OPERATION_MAINTENANCE_CAPTURE = "Capture";
    const OPERATION_MAINTENANCE_REFUND = "Refund";
    const OPERATION_MAINTENANCE_ACCEPT_CHALLENGE = 'acceptChallenge';
    const OPERATION_MAINTENANCE_DENY_CHALLENGE = 'denyChallenge';
    const OPERATION_MAINTENANCE_CANCEL = 'cancel';


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

    /**
     * Bit masks to specify different payment method checks.
     * @see Mage_Payment_Model_Method_Abstract::isApplicableToQuote
     */
    const CHECK_USE_FOR_COUNTRY = 1;
    const CHECK_USE_FOR_CURRENCY = 2;
    const CHECK_USE_CHECKOUT = 4;
    const CHECK_USE_FOR_MULTISHIPPING = 8;
    const CHECK_USE_INTERNAL = 16;
    const CHECK_ORDER_TOTAL_MIN_MAX = 32;
    const CHECK_RECURRING_PROFILES = 64;
    const CHECK_ZERO_TOTAL = 128;

    //const STATUS_PENDING_CAPTURE = 'pending_capture';

    /**
     * Availability options
     */
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_canSaveCc = false;
    protected $_canReviewPayment = false;

    //protected $_allowCurrencyCode = array('EUR');

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = array('token', 'cardtoken', 'card_number', 'cvc');


    public function isInitializeNeeded()
    {
        return true;
    }


    protected function getOperation()
    {
        switch ($this->getConfigPaymentAction()) {
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

    public function assignInfoData($info, $data)
    {
        $oneclickMode = $data->getData($this->getCode() . '_oneclick');
        /** @noinspection PhpUndefinedMethodInspection */
        $oneclickCard = $data->getData($this->getCode() . '_oneclick_card');
        $splitPaymentId = $data->getData($this->getCode() . '_split_payment_id');
        $token = $data->getData($this->getCode() . '_cc_token');

        $info->setAdditionalInformation('create_oneclick', $oneclickMode == "create_oneclick" ? 1 : 0)
            ->setAdditionalInformation('use_oneclick', $oneclickMode == "use_oneclick" ? 1 : 0)
            ->setAdditionalInformation('selected_oneclick_card', $oneclickCard == "" ? 0 : $oneclickCard)
            ->setAdditionalInformation('split_payment_id', $splitPaymentId != "" ? $splitPaymentId : 0)
            ->setAdditionalInformation('token', $token != "" ? $token : "")
            ->setAdditionalInformation('device_fingerprint', $data->getData('device_fingerprint'));
    }

    /**
     * A request instructing the payment gateway to cancel a previously authorized transaction.
     * Only authorized transactions can be cancelled, captured transactions must be refunded.
     * @param Mage_Payment_Model_Info $payment
     * @return bool
     */
    public function cancelTransaction(Mage_Payment_Model_Info $payment)
    {
        $transactionId = $payment->getLastTransId();
        $order = $payment->getOrder();

        $gatewayParams = array('operation' => self::OPERATION_MAINTENANCE_CANCEL);

        /* @var $request Allopass_Hipay_Model_Api_Request */
        $request = Mage::getModel('hipay/api_request', array($this));
        $uri = Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_MAINTENANCE . $transactionId;

        if ($transactionId) {
            $gatewayResponse = $request->gatewayRequestMaintenance($uri, $gatewayParams,
                $payment->getOrder()->getStoreId());

            if (is_a($gatewayResponse, 'Allopass_Hipay_Model_Api_Response_Error')) {
                $order->addStatusHistoryComment(Mage::helper('hipay')->__('Error in  canceling  Transaction ID: "%s". %s',
                    $transactionId, $gatewayResponse->getMessage()), false);
            } else {
                $response = Mage::getModel('hipay/api_response_gateway', $gatewayResponse);

                if ($response->getStatus() == '115') {
                    $order->addStatusHistoryComment(Mage::helper('hipay')->__('Cancel Transaction ID: "%s".',
                        $transactionId), false);
                } else {
                    $order->addStatusHistoryComment(Mage::helper('hipay')->__('Error in  canceling transaction ID: "%s". %s',
                        $transactionId, $gatewayResponse->getMessage()), false);
                }

                $this->_debug($response->debug());
            }
        } else {
            $order->addStatusHistoryComment(Mage::helper('hipay')->__('No Cancel Transaction because no transaction number'),
                false);
        }

        // Return false because payment is accepted by notification
        return false;
    }


    public function acceptPayment(Mage_Payment_Model_Info $payment)
    {
        parent::acceptPayment($payment);
        $amount = $payment->getAmountAuthorized();

        $transactionId = $payment->getLastTransId();

        $gatewayParams = array('operation' => self::OPERATION_MAINTENANCE_ACCEPT_CHALLENGE, 'amount' => $amount);
        $this->_debug($gatewayParams);
        /* @var $request Allopass_Hipay_Model_Api_Request */
        $request = Mage::getModel('hipay/api_request', array($this));
        $uri = Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_MAINTENANCE . $transactionId;

        $gatewayResponse = $request->gatewayRequest($uri, $gatewayParams, $payment->getOrder()->getStoreId());

        $this->_debug($gatewayResponse->debug());
        $receiver = Mage::getModel('customer/customer')->load($payment->getOrder()->getCustomerId());
        $message = Mage::helper('hipay')->__('Your transaction has been approved.');
        $email_key = "fraud_payment_accept";
        if ($this->canSendFraudEmail($payment->getOrder()->getStoreId())) {
            $this->getHelper()->sendFraudPaymentEmail($receiver, $payment->getOrder(), $message, $email_key);
        }

        $payment->setPreparedMessage(Mage::helper('hipay')->__('Transaction is in pending notification.'));

        // Return false because payment is accepted by notification
        return false;
    }

    public function denyPayment(Mage_Payment_Model_Info $payment)
    {

        /*@var $payment Mage_Sales_Model_Order_Payment */
        parent::denyPayment($payment);
        $amount = $payment->getAmountAuthorized();

        $transactionId = $payment->getLastTransId();

        $gatewayParams = array('operation' => self::OPERATION_MAINTENANCE_DENY_CHALLENGE, 'amount' => $amount);
        $this->_debug($gatewayParams);
        /* @var $request Allopass_Hipay_Model_Api_Request */
        $request = Mage::getModel('hipay/api_request', array($this));
        $uri = Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_MAINTENANCE . $transactionId;

        $gatewayResponse = $request->gatewayRequest($uri, $gatewayParams, $payment->getOrder()->getStoreId());

        $this->_debug($gatewayResponse->debug());

        $receiver = Mage::getModel('customer/customer')->load($payment->getOrder()->getCustomerId());
        $message = Mage::helper('hipay')->__('Your transaction has been refused.');
        $email_key = "fraud_payment_deny";
        if ($this->canSendFraudEmail($payment->getOrder()->getStoreId())) {
            $this->getHelper()->sendFraudPaymentEmail($receiver, $payment->getOrder(), $message, $email_key);
        }

        return true;
    }

    /**
     *
     * @param Allopass_Hipay_Model_Api_Response_Gateway $gatewayResponse
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return bool
     */
    public function processResponse($gatewayResponse, $payment, $amount)
    {
        $logs = array();
        $order = $payment->getOrder();
        $newTransactionType = null;
        $requestType = null;
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        $defaultExceptionMessage = Mage::helper('hipay')->__('Payment error.');

        $useOrderCurrency = Mage::getStoreConfig('hipay/hipay_api/currency_transaction', Mage::app()->getStore());

        if ($useOrderCurrency) {
            $currency = $order->getOrderCurrency();
            $total = $order->getGrandTotal();
        } else {
            $currency = Mage::app()->getStore()->getBaseCurrency();
            $total = $order->getBaseGrandTotal();
        }

        // Process some logs if debug mode is enabled
        $logs['HIPAY PROCESS RESPONSE START'] = '';
        $logs['Gateway Status'] = $gatewayResponse->getStatus();
        $logs['Order Status'] = $order->getStatus();
        $logs['UseOrderCurrency'] = $useOrderCurrency;
        $logs['Currency'] = $currency->getData("currency_code");
        $logs['Total'] = $total;
        $logs['Amount'] = $amount;

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

        //add data to payment object
        if ($payment->getCcType() == "") {
            $payment->setCcType($gatewayResponse->getPaymentProduct());
        }

        switch ($gatewayResponse->getState()) {
            case self::STATE_COMPLETED:
            case self::STATE_PENDING:
                switch ((int)$gatewayResponse->getStatus()) {
                    case 111: //denied

                        $this->addTransaction(
                            $payment,
                            $gatewayResponse->getTransactionReference(),
                            $newTransactionType,
                            array('is_transaction_closed' => 0),
                            array(),
                            Mage::helper('hipay')->getTransactionMessage(
                                $payment, $requestType, /*$gatewayResponse->getTransactionReference()*/
                                null, $amount
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

                        $order->addStatusHistoryComment(Mage::helper('hipay')->getTransactionMessage(
                            $payment, self::OPERATION_AUTHORIZATION, null, $amount, true, $gatewayResponse->getMessage()
                        ),$status);

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
                                $payment, self::OPERATION_AUTHORIZATION, $gatewayResponse->getTransactionReference(),
                                $amount, true
                            )
                        );
                        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
                        $status = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
                        if (defined('Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW')) {
                            $state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                            $status = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                        }


                        $this->_setFraudDetected($gatewayResponse, $customer, $payment, $amount);

                        $order->setState($state, $status, $gatewayResponse->getMessage());

                        $payment->setAmountAuthorized($gatewayResponse->getAuthorizedAmount());
                        $payment->setBaseAmountAuthorized($gatewayResponse->getAuthorizedAmount());

                        $order->save();
                        break;

                    case 142: //Authorized Requested
                        if ($order->getStatus() == self::STATUS_CAPTURE_REQUESTED || $order->getStatus() == Mage_Sales_Model_Order::STATE_PROCESSING
                            || $order->getStatus() == Mage_Sales_Model_Order::STATE_COMPLETE || $order->getStatus() == Mage_Sales_Model_Order::STATE_CLOSED
                            || $order->getStatus() == self::STATUS_PENDING_CAPTURE
                        ) {// for logic process
                            break;
                        }

                        $this->addTransaction(
                            $payment,
                            $gatewayResponse->getTransactionReference(),
                            Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
                            array('is_transaction_closed' => 0),
                            array(
                                $this->_realTransactionIdKey => $gatewayResponse->getTransactionReference(),
                            ),
                            Mage::helper('hipay')->getTransactionMessage(
                                $payment, self::OPERATION_AUTHORIZATION, $gatewayResponse->getTransactionReference(),
                                $amount, true
                            )
                        );
                        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
                        if (defined('Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW')) {
                            $state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                        }
                        $status = self::STATUS_AUTHORIZATION_REQUESTED;

                        $order->setState($state, $status, $gatewayResponse->getMessage());

                        $payment->setAmountAuthorized($gatewayResponse->getAuthorizedAmount());
                        $payment->setBaseAmountAuthorized($gatewayResponse->getAuthorizedAmount());

                        $order->save();
                        break;

                    case 114: //Expired
                        if ($order->getStatus() != self::STATUS_PENDING_CAPTURE) {// for logic process
                            break;
                        }

                        $this->addTransaction(
                            $payment,
                            $gatewayResponse->getTransactionReference(),
                            Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID,
                            array('is_transaction_closed' => 0),
                            //Transaction was not closed, because admin can try capture after expiration
                            array(
                                $this->_realTransactionIdKey => $gatewayResponse->getTransactionReference(),
                            ),
                            Mage::helper('hipay')->getTransactionMessage(
                                $payment, self::OPERATION_AUTHORIZATION, $gatewayResponse->getTransactionReference(),
                                $amount, true
                            )
                        );

                        /**
                         * We change status to expired and state to holded
                         * So the administrator can try to capture transaction even if
                         * the auhorization was expired
                         *
                         */
                        $state = Mage_Sales_Model_Order::STATE_HOLDED;
                        $status = self::STATUS_EXPIRED;
                        $order->setState(
                            $state,
                            $status,
                            $gatewayResponse->getMessage());

                        $order->save();
                        break;
                    case 115: //Canceled
                        if ($order->cancel()) {
                            $order->cancel();

                            $this->addTransaction(
                                $payment,
                                $gatewayResponse->getTransactionReference(),
                                Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID,
                                array('is_transaction_closed' => 1),
                                //Transaction was not closed, because admin can try capture after expiration
                                array(
                                    $this->_realTransactionIdKey => $gatewayResponse->getTransactionReference(),
                                ),
                                Mage::helper('hipay')->getTransactionMessage(
                                    $payment, self::OPERATION_AUTHORIZATION,
                                    $gatewayResponse->getTransactionReference(), $amount, true
                                )
                            );
                        }


                        break;
                    case 116: //Authorized

                        //check if this order was in state fraud detected
                        $fraud_type = $order->getPayment()->getAdditionalInformation('fraud_type');
                        $fraud_score = $order->getPayment()->getAdditionalInformation('scoring');
                        $has_fraud = !empty($fraud_type) && !empty($fraud_score);

                        if ($order->getStatus() == 'capture_requested' || ($order->getStatus() == 'processing' && !$has_fraud) //check fraud for allow notif in payment review case
                            || $order->getStatus() == 'complete' || $order->getStatus() == 'closed'
                        ) {// for logic process
                            break;
                        }
                        if (!$this->isPreauthorizeCapture($payment)) {
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
                        }

                        $order->setState(
                            Mage_Sales_Model_Order::STATE_PROCESSING,
                            self::STATUS_PENDING_CAPTURE,
                            Mage::helper('hipay')
                                ->__("Waiting for capture transaction ID '%s' of amount %s",
                                    $gatewayResponse->getTransactionReference(),
                                    $currency->formatTxt($total)),
                            $notified = true);

                        $order->save();
                        // Send order confirmation email - TPPMAG1-29
                        if (!$order->getEmailSent() && $order->getCanSendNewEmailFlag()) {
                            try {
                                if (method_exists($order, 'queueNewOrderEmail')) {
                                    $order->queueNewOrderEmail();
                                } else {
                                    $order->sendNewOrderEmail();
                                }
                            } catch (Exception $e) {
                                Mage::logException($e);
                            }
                        }

                        $payment->setAmountAuthorized($gatewayResponse->getAuthorizedAmount());
                        $payment->setBaseAmountAuthorized($gatewayResponse->getAuthorizedAmount());


                        break;
                    /** @noinspection PhpMissingBreakStatementInspection */
                    case 117: //Capture Requested

                        if ($order->getStatus() == 'capture' || $order->getStatus() == 'processing') {// for logic process
                            break;
                        }

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

                        /** @noinspection PhpMethodParametersCountMismatchInspection */
                        $order->setState(
                            Mage_Sales_Model_Order::STATE_PROCESSING, 'capture_requested', $message, null, false
                        );

                        $payment->setAmountAuthorized($gatewayResponse->getAuthorizedAmount());
                        $payment->setBaseAmountAuthorized($gatewayResponse->getAuthorizedAmount());

                        //If status Capture Requested is not configured to validate the order, we break.
                        if (((int)$this->getConfigData('hipay_status_validate_order') == 117) === false) {
                            break;
                        }

                    case 118: //Capture. There are 2 ways to enter in this case: 1. direct capture notification. 2. After 117 case, when it is configured for valid order with 117 status.
                        $acceptMessage = Mage::helper("hipay")->__('Payment accepted by Hipay.');

                        if (!$status = $this->getConfigData('order_status_payment_accepted')) {
                            $status = $order->getStatus();
                        }

                        if ($order->getStatus() == $this->getConfigData('order_status_payment_accepted')) {
                            break;
                        }
                        //If status Capture Requested is configured to validate the order and is a direct capture notification (118), we break because order is already validate.
                        if (((int)$this->getConfigData('hipay_status_validate_order') == 117) === true && (int)$gatewayResponse->getStatus() == 118) {
                            // if callback 118 and config validate order = 117 and no 117 in history - execute treatment alse break
                            $histories = Mage::getResourceModel('sales/order_status_history_collection')
                                ->setOrderFilter($order)
                                ->addFieldToFilter('comment', array('like' => '%code-117%'));
                            if ($histories->count() > 0) {
                                break;
                            }
                        }

                        //Check if it is split payment and insert it
                        if (($profile = (int)$payment->getAdditionalInformation('split_payment_id')) && $customer->getId()) {
                            $token = isset($gatewayResponse->paymentMethod['token']) ? $gatewayResponse->paymentMethod['token'] : $gatewayResponse->getData('cardtoken');
                            $this->getHelper()->insertSplitPayment($order, $profile, $customer->getId(), $token);
                        }

                        if ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
                            $order->unhold();
                        }

                        // Create invoice
                        if ($this->getConfigData('invoice_create', $order->getStoreId()) && !$order->hasInvoices()) {
                            if (abs($amount - $total) > Allopass_Hipay_Helper_Data::EPSYLON && !$profile && $order->getState() != Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW) {
                                $transactionId = $gatewayResponse->getTransactionReference();
                                $order->addStatusHistoryComment(Mage::helper('hipay')->__('Notification "Capture". Capture issued by merchant. Registered notification about captured amount of %s. Transaction ID: "%s". 
                                Invoice has not been created. Please create offline Invoice. ( Authorized amount was %s )',
                                    $currency->formatTxt($amount), $transactionId, $currency->formatTxt($total)),
                                    false);

                                // In case of 117 is disabled or not received
                                if ($order->getStatus() != Mage_Sales_Model_Order::STATE_PROCESSING) {
                                    $this->processStatusOrder($order, $status, $acceptMessage);
                                }
                                break;
                            }
                            $invoice = $this->create_invoice($order, $gatewayResponse->getTransactionReference(),
                                false);

                            Mage::getModel('core/resource_transaction')
                                ->addObject($invoice)->addObject($invoice->getOrder())
                                ->save();
                            $logs['Create invoice'] = $invoice->getIncrementId();
                        } elseif ($order->hasInvoices()) {
                            foreach ($order->getInvoiceCollection() as $invoice) {
                                if ($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_OPEN && (round(($invoice->getBaseGrandTotal() + $order->getBaseTotalPaid()),
                                            2) == $gatewayResponse->getCapturedAmount() || round(($invoice->getBaseGrandTotal()),
                                            2) == $gatewayResponse->getCapturedAmount())
                                ) {
                                    $invoice->pay();
                                    $logs['Pay invoice'] = $invoice->invoice->getIncrementId() . ' ' . $invoice->getBaseGrandTotal();
                                    Mage::getModel('core/resource_transaction')
                                        ->addObject($invoice)->addObject($invoice->getOrder())
                                        ->save();
                                }
                            }
                        }

                        if (($profile = (int)$payment->getAdditionalInformation('split_payment_id')) && $customer->getId()) {
                            $token = isset($gatewayResponse->paymentMethod['token']) ? $gatewayResponse->paymentMethod['token'] : $gatewayResponse->getData('cardtoken');
                            $this->getHelper()->insertSplitPayment($order, $profile, $customer->getId(), $token);
                            $logs['Insert Split Payment'] = 'Customer : ' . $customer->getId() . ' Split' . $payment->getAdditionalInformation('split_payment_id');
                        }

                        $this->processStatusOrder($order, $status, $acceptMessage);

                        $payment->setAmountAuthorized($gatewayResponse->getAuthorizedAmount());
                        $payment->setBaseAmountAuthorized($gatewayResponse->getAuthorizedAmount());


                        // Send order confirmation email - TPPMAG1-29
                        if (!$order->getEmailSent() && $order->getCanSendNewEmailFlag()) {
                            try {
                                if (method_exists($order, 'queueNewOrderEmail')) {
                                    $order->queueNewOrderEmail();
                                } else {
                                    $order->sendNewOrderEmail();
                                }
                            } catch (Exception $e) {
                                Mage::logException($e);
                            }
                        }

                        break;
                    case 124: //Refund Requested

                        $message = Mage::helper("hipay")->__('Refund Requested by Hipay.');

                        /** @noinspection PhpMethodParametersCountMismatchInspection */
                        $order->setState(
                            Mage_Sales_Model_Order::STATE_PROCESSING, 'refund_requested', $message, null, false
                        );

                        break;
                    case 125: //Refund
                    case 126: //Partially Refund

                        if ($order->hasCreditmemos()) {
                            $total_already_refunded = 0;

                            /* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
                            //We get total already refunded for found the amount of this creditmemo
                            foreach ($order->getCreditmemosCollection() as $creditmemo) {
                                if ($creditmemo->getState() == Mage_Sales_Model_Order_Creditmemo::STATE_REFUNDED) {
                                    $total_already_refunded += $creditmemo->getGrandTotal();
                                }
                            }

                            $cm_amount_check = round($gatewayResponse->getRefundedAmount() - $total_already_refunded,
                                2);
                            $status = $order->getStatus();
                            if (round($gatewayResponse->getRefundedAmount(), 2) < round($order->getGrandTotal(), 2)) {
                                $status = self::STATUS_PARTIAL_REFUND;
                            }

                            /* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
                            foreach ($order->getCreditmemosCollection() as $creditmemo) {
                                if ($creditmemo->getState() == Mage_Sales_Model_Order_Creditmemo::STATE_OPEN
                                    && round($creditmemo->getGrandTotal(), 2) == $cm_amount_check
                                ) {
                                    $creditmemo->setState(Mage_Sales_Model_Order_Creditmemo::STATE_REFUNDED);

                                    $message = Mage::helper("hipay")->__('Refund accepted by Hipay.');

                                    $order-> $order->addStatusHistoryComment($message,$status);

                                    Mage::getModel('core/resource_transaction')
                                        ->addObject($creditmemo)->addObject($creditmemo->getOrder())
                                        ->save();

                                    break;
                                }
                            }
                        } elseif ($order->canCreditmemo()) {
                            if ($amount != $total) {
                                $transactionId = $gatewayResponse->getTransactionReference();
                                $order->addStatusHistoryComment(Mage::helper('hipay')->__('Notification "Refunded". Refund issued by merchant. Registered notification about refunded amount of %s. Transaction ID: "%s". Credit Memo has not been created. Please create offline Credit Memo.',
                                    $currency->formatTxt($amount), $transactionId), false);
                                return $this;
                            }

                            $amountTxt = $currency->formatTxt($amount);

                            $transactionId = $gatewayResponse->getTransactionReference();

                            $comment = Mage::helper('hipay')->__('Refunded amount of %s. Transaction ID: "%s"',
                                $amountTxt, $transactionId);

                            $isRefundFinal = $gatewayResponse->getRefundedAmount() == $order->getGrandTotal();
                            $payment->setIsTransactionClosed($isRefundFinal)
                                ->registerRefundNotification($amount);
                            $order->addStatusHistoryComment($comment, false);

                            // TODO: there is no way to close a capture right now
                            $creditmemo = $payment->getCreatedCreditmemo();
                            if ($creditmemo) {
                                $creditmemo->sendEmail();
                                $order->addStatusHistoryComment(
                                    Mage::helper('hipay')->__('Notified customer about creditmemo #%s.',
                                        $creditmemo->getIncrementId())
                                )
                                    ->setIsCustomerNotified(true)
                                    ->save();
                            }
                        }

                        break;
                    default:
                        $message = Mage::helper("hipay")->__('Message Hipay: %s. Status: %s',
                            $gatewayResponse->getMessage(), $gatewayResponse->getStatus());
                        $order->addStatusHistoryComment($message,$order->getStatus());
                        break;
                }

                if ($gatewayResponse->getState() == self::STATE_COMPLETED) {
                    if (in_array($gatewayResponse->getPaymentProduct(),
                            array('visa', 'american-express', 'mastercard', 'cb'))
                        && ((int)$gatewayResponse->getEci() == 9 || $payment->getAdditionalInformation('create_oneclick'))
                        && !$order->isNominal()
                    ) { //Recurring E-commerce

                        if ($customer->getId()) {
                            $this->responseToCustomer($customer, $gatewayResponse);
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
                if (/* @TODO wait for response from hipay support
                 * About issue #10 les notifications des différentes transactions HiPay se croisent
                 * $order->getStatus() == self::STATUS_CAPTURE_REQUESTED || $order->getStatus() == self::STATUS_PENDING_CAPTURE ||*/
                    $order->getStatus() == Mage_Sales_Model_Order::STATE_PROCESSING
                    || $order->getStatus() == Mage_Sales_Model_Order::STATE_COMPLETE || $order->getStatus() == Mage_Sales_Model_Order::STATE_CLOSED
                ) {// for logic process
                    break;
                }

                $statusCode = (int)$gatewayResponse->getStatus();
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
                        $payment, $requestType, null, $amount, true,
                        "Code: " . $reason['code'] . " " . Mage::helper('hipay')->__("Reason") . " : " . $reason['message']
                    )
                );


                if ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
                    $order->unhold();
                }

                if (!$status = $this->getConfigData('order_status_payment_refused')) {
                    $status = $order->getStatus();
                }

                if (in_array($statusCode, array(110))) {
                    $this->_setFraudDetected($gatewayResponse, $customer, $payment, $amount, true);
                }

                if ($status == Mage_Sales_Model_Order::STATE_HOLDED && $order->canHold()) {
                    $order->hold();
                } elseif ($status == Mage_Sales_Model_Order::STATE_CANCELED && $order->canCancel()) {
                    $order->cancel();
                }

                $order->addStatusHistoryComment(Mage::helper('hipay')->getTransactionMessage(
                    $payment, $this->getOperation(), null, $amount, true, $gatewayResponse->getMessage()
                ),$status);

                $order->save();
                break;

            case self::STATE_ERROR:
            default:
                $logs['HIPAY PROCESS RESPONSE ERROR '] = '';
                $this->debugData($logs);
                Mage::throwException($defaultExceptionMessage);
                break;

        }
        $logs['HIPAY PROCESS RESPONSE END'] = '';
        $this->debugData($logs);

        return true;
    }

    /**
     *Change Status of order when capture is done
     *
     * @param $order
     * @param $status
     * @param $message
     */
    protected function processStatusOrder($order, $status, $message)
    {
        if ($status == Mage_Sales_Model_Order::STATE_PROCESSING) {
            $order->setState(
                Mage_Sales_Model_Order::STATE_PROCESSING, $status, $message
            );
        } else {
            if ($status == Mage_Sales_Model_Order::STATE_COMPLETE) {
                $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
                $order->addStatusToHistory($status, $message, true);
                /*$order->setState(
                        Mage_Sales_Model_Order::STATE_COMPLETE, $status, $message, null, false
                );*/
            } else {
                $order->addStatusToHistory($status, $message, true);
            }
        }
    }

    /**
     *
     * @param Allopass_Hipay_Model_Api_Response_Gateway $gatewayResponse
     * @param $customer
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param $amount
     * @param bool $addToHistory
     */
    protected function _setFraudDetected($gatewayResponse, $customer, $payment, $amount, $addToHistory = false)
    {
        if ($fraudScreening = $gatewayResponse->getFraudScreening()) {
            if (isset($fraudScreening['result']) && isset($fraudScreening['scoring'])) {
                $order = $payment->getOrder();
                $payment->setIsFraudDetected(true);

                if (defined('Mage_Sales_Model_Order::STATUS_FRAUD')) {
                    $status = Mage_Sales_Model_Order::STATUS_FRAUD;
                }else{
                    $status = null;
                }

                $payment->setAdditionalInformation('fraud_type', $fraudScreening['result']);
                $payment->setAdditionalInformation('fraud_score', $fraudScreening['scoring']);
                $payment->setAdditionalInformation('fraud_review', $fraudScreening['review']);

                if ($addToHistory) {
                    $order->addStatusHistoryComment(Mage::helper('hipay')->getTransactionMessage(
                        $payment, $this->getOperation(), null, $amount, true, $gatewayResponse->getMessage()
                    ),$status);
                }

                $message = Mage::helper('hipay')->__($gatewayResponse->getMessage());

                if ($this->canSendFraudEmail($order->getStoreId())) {
                    $email_key = 'fraud_payment';
                    if ($fraudScreening['result'] != 'challenged' || $gatewayResponse->getState() == self::STATE_DECLINED) {
                        $email_key = 'fraud_payment_deny';
                    }

                    $this->getHelper()->sendFraudPaymentEmail($customer, $order, $message, $email_key);
                }
            }
        }
    }

    /**
     *
     * @param int $storeId
     * @return bool
     */
    protected function canSendFraudEmail($storeId = null)
    {
        return (bool)$this->getConfigData('send_fraud_payment_email', $storeId);
    }

    /**
     * Create object invoice
     * @param Mage_Sales_Model_Order $order
     * @param string $transactionReference
     * @param boolean $capture
     * @param boolean $paid
     * @return Mage_Sales_Model_Order_Invoice $invoice
     */
    protected function create_invoice($order, $transactionReference, $capture = true, $paid = false)
    {
        /* @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = $order->prepareInvoice();
        $invoice->setTransactionId($transactionReference);

        $capture_case = Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE;
        if ($capture) {
            $capture_case = Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE;
        }
        $invoice->setRequestedCaptureCase($capture_case);

        $invoice->register();

        $invoice->getOrder()->setIsInProcess(true);

        if ($paid) {
            $invoice->setIsPaid(1);
        }

        return $invoice;
    }

    /**
     *
     * @param Allopass_Hipay_Model_Api_Response_Gateway $gatewayResponse
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return bool|string
     * @internal param float $amount
     */
    public function processResponseToRedirect($gatewayResponse, $payment)
    {
        $order = $payment->getOrder();
        $defaultExceptionMessage = Mage::helper('hipay')->__('Payment error.');

        switch ($this->getConfigPaymentAction()) {
            case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE:
                $defaultExceptionMessage = Mage::helper('hipay')->__('Payment authorization error.');
                break;
            case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE:
                $defaultExceptionMessage = Mage::helper('hipay')->__('Payment capturing error.');
                break;
        }

        $urlAdmin = Mage::getUrl('adminhtml/sales_order/index');
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $urlAdmin = Mage::getUrl('adminhtml/sales_order/view', array('order_id' => $order->getId()));
        }

        if ($gatewayResponse->getState()) {
            switch ($gatewayResponse->getState()) {
                case self::STATE_COMPLETED:
                    return $this->isAdmin() ? $urlAdmin : Mage::helper('hipay')->getCheckoutSuccessPage($payment);

                case self::STATE_FORWARDING:
                    $payment->setIsTransactionPending(1);
                    $order->save();
                    return $gatewayResponse->getForwardUrl();

                case self::STATE_PENDING:
                    if ($this->getConfigData('re_add_to_cart')) {
                        $this->getHelper()->reAddToCart($order->getIncrementId());
                    }

                    return $this->isAdmin() ? $urlAdmin : Mage::getUrl($this->getConfigData('pending_redirect_page'));

                case self::STATE_DECLINED:

                    if ($this->getConfigData('re_add_to_cart')) {
                        $this->getHelper()->reAddToCart($order->getIncrementId());
                    }

                    return $this->isAdmin() ? $urlAdmin : Mage::getUrl('checkout/onepage/failure');

                case self::STATE_ERROR:
                default:

                    if ($this->getConfigData('re_add_to_cart')) {
                        $this->getHelper()->reAddToCart($order->getIncrementId());
                    }

                    $this->_getCheckout()->setErrorMessage($defaultExceptionMessage);
                    return $this->isAdmin() ? $urlAdmin : Mage::getUrl('checkout/onepage/failure');

            }
        }
        return true;
    }

    /**
     *
     * @return Allopass_Hipay_Helper_Data|Mage_Core_Helper_Abstract
     */
    protected function getHelper()
    {
        return Mage::helper('hipay');
    }


    /**
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param Allopass_Hipay_Model_Api_Response_Gateway $response
     * @return $this
     */
    protected function responseToCustomer($customer, $response)
    {
        $this->getHelper()->responseToCustomer($customer, $response);
        $this->getHelper()->createCustomerCardFromResponse($customer->getId(), $response);
        return $this;
    }

    /**
     *
     * @param Mage_Sales_Model_Order_Payment|Varien_Object $payment
     * @param float $amount
     * @return $this|Mage_Payment_Model_Abstract @return Mage_Payment_Model_Abstract
     */
    public function refund(Varien_Object $payment, $amount)
    {
        parent::refund($payment, $amount);

        $transactionId = $payment->getLastTransId();

        $gatewayParams = array('operation' => 'refund', 'amount' => $amount);

        if (Mage::helper('hipay')->isSendCartItemsRequired($payment->getCcType())) {
            $gatewayParams['basket'] = Mage::helper('hipay')->getCartInformation($payment->getOrder(),
                Allopass_Hipay_Helper_Data::STATE_REFUND, $payment);
        }

        /* @var $request Allopass_Hipay_Model_Api_Request */
        $request = Mage::getModel('hipay/api_request', array($this));
        $action = Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_MAINTENANCE . $transactionId;


        $this->_debug($gatewayParams);
        $gatewayResponse = $request->gatewayRequest($action, $gatewayParams, $payment->getOrder()->getStoreId());
        $this->_debug($gatewayResponse->debug());

        switch ($gatewayResponse->getStatus()) {
            case "124":
            case "125":
            case "126":

                /* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
                $creditmemo = $payment->getCreditmemo();
                $creditmemo->setState(Mage_Sales_Model_Order_Creditmemo::STATE_OPEN);//State open = pending state

                break;
            default:
                Mage::throwException($gatewayResponse->getStatus() . " ==> " . $gatewayResponse->getMessage());
                break;
        }

        return $this;
	}
	
	/**
	 *
	 * @param Mage_Sales_Model_Order_Payment $payment
	 * @param float $amount
	 * @param string|null $token
     * @param string $split_number
	 * @return multitype:
	 */
	public function getGatewayParams($payment,$amount,$token=null,$split_number = null)
	{
        $params = array();
        $params['orderid'] = $payment->getOrder()->getIncrementId();
        $paymentProduct = null;
        $longDesc = "";

        $taxAmount = $payment->getOrder()->getTaxAmount();
        if (($profile = $payment->getAdditionalInformation('split_payment_id'))) {
            //Check if this order is already split
            $spCollection = Mage::getModel('hipay/splitPayment')->getCollection()
                ->addFieldToFilter('order_id', $payment->getOrder()->getId());

            if (!$spCollection->count()) {
                $longDesc = Mage::helper('hipay')->__('Split payment');
                $paymentsSplit = $this->getHelper()->splitPayment((int)$profile, $amount, $taxAmount);

                $amount = $paymentsSplit[0]['amountToPay'];
                $taxAmount = $paymentsSplit[0]['taxAmountToPay'];
            }
        }

        $params['description'] = Mage::helper('hipay')->__("Order %s by %s", $payment->getOrder()->getIncrementId(),
            $payment->getOrder()->getCustomerEmail());//MANDATORY
        $params['long_description'] = $longDesc;// optional

        $useOrderCurrency = Mage::getStoreConfig('hipay/hipay_api/currency_transaction', Mage::app()->getStore());

        if ($useOrderCurrency) {
            $params['currency'] = $payment->getOrder()->getOrderCurrencyCode();
        } else {
            $params['currency'] = $payment->getOrder()->getBaseCurrencyCode();
        }
        $params['amount'] = $amount;
        $params['shipping'] = $payment->getOrder()->getShippingAmount();
        $params['tax'] = $taxAmount;
        $params['tax_rate'] = Mage::helper('hipay')->getTaxeRateInformation($payment->getOrder());

        $params['cid'] = $payment->getOrder()->getCustomerId();//CUSTOMER ID

        // Astropay needs National identification number
        if ($payment->getAdditionalInformation('national_identification_number')){
            $params['national_identification_number']  = $payment->getAdditionalInformation('national_identification_number');
        }

        $remoteIp = $payment->getOrder()->getRemoteIp();

        //Check if it's forwarded and in this case, explode and retrieve the first part
        if (!is_null($payment->getOrder()->getXForwardedFor())) {
            if (strpos($payment->getOrder()->getXForwardedFor(), ",") !== false) {
                $xfParts = explode(",", $payment->getOrder()->getXForwardedFor());
                $remoteIp = current($xfParts);
            } else {
                $remoteIp = $payment->getOrder()->getXForwardedFor();
            }
        }

        $params['ipaddr'] = $remoteIp;

        $params['http_accept'] = "*/*";
        $params['http_user_agent'] = Mage::helper('core/http')->getHttpUserAgent();
        $params['language'] = Mage::app()->getLocale()->getLocaleCode();//strpos(Mage::app()->getLocale()->getLocaleCode(), "fr") !== false ? "fr_FR" : 'en';

        /**
         * Parameters specific to the payment product
         */
        if (!is_null($token)) {
            $params['cardtoken'] = $token;
        }

        $params['authentication_indicator'] = Mage::helper('hipay')->is3dSecure($this->getConfigData('use_3d_secure'),
            $this->getConfigData('config_3ds_rules'), $payment);

        $isAdmin = $this->isAdmin();

        /**
         * Electronic Commerce Indicator
         */
        if ($payment->getAdditionalInformation('use_oneclick')) {
            $params['eci'] = 9; //Recurring E-commerce
        } elseif ($isAdmin) {
            $params['eci'] = 1; //MO/TO (Card Not Present). This value prevent from 3ds redirection in Admin payment.
        }

        /**
         * Redirect urls
         */
        if ($this->sendMailToCustomer() && $this->getCode() == 'hipay_hosted') {
            $paramsMoto['order'] = $payment->getOrder()->getIncrementId();

            // MOTO with mail to customer
            $params['accept_url'] = Mage::getUrl($this->getConfigData('accept_url'), $paramsMoto);
            $params['decline_url'] = Mage::getUrl($this->getConfigData('decline_url'), $paramsMoto);
            $params['pending_url'] = Mage::getUrl($this->getConfigData('pending_url'), $paramsMoto);
            $params['exception_url'] = Mage::getUrl($this->getConfigData('exception_url'), $paramsMoto);
            $params['cancel_url'] = Mage::getUrl($this->getConfigData('cancel_url'), $paramsMoto);
            $params['moto_url_redirect'] = Mage::helper('adminhtml')->getUrl('*/payment/accept');
        } else {
            // MOTO is not activated
            $params['accept_url'] = $isAdmin ? Mage::helper('adminhtml')->getUrl('*/payment/accept') : Mage::getUrl($this->getConfigData('accept_url'));
            $params['decline_url'] = $isAdmin ? Mage::helper('adminhtml')->getUrl('*/payment/decline') : Mage::getUrl($this->getConfigData('decline_url'));
            $params['pending_url'] = $isAdmin ? Mage::helper('adminhtml')->getUrl('*/payment/pending') : Mage::getUrl($this->getConfigData('pending_url'));
            $params['exception_url'] = $isAdmin ? Mage::helper('adminhtml')->getUrl('*/payment/exception') : Mage::getUrl($this->getConfigData('exception_url'));
            $params['cancel_url'] = $isAdmin ? Mage::helper('adminhtml')->getUrl('*/payment/cancel') : Mage::getUrl($this->getConfigData('cancel_url'));
        }

        $params = $this->getCustomerParams($payment, $params);
        $params = $this->getShippingParams($payment, $params);

        //add url to order in BO Magento
        $params['cdata1'] = Mage::getUrl('adminhtml/sales_order/view',
            array('_secure' => true, 'order_id' => $payment->getOrder()->getId()));

        $customDataHipay = Mage::helper('hipay')->getCustomData($payment, $amount, $this, $split_number);

        // Add custom data for transaction request
        if (file_exists(Mage::getModuleDir('', 'Allopass_Hipay') . DS . 'Helper' . DS . 'CustomData.php')) {
            if (class_exists('Allopass_Hipay_Helper_CustomData', true)) {
                if (method_exists(Mage::helper('hipay/customData'), 'getCustomData')) {
                    $customData = Mage::helper('hipay/customData')->getCustomData($payment, $amount);
                    if (is_array($customData)) {
                        $customDataHipay = array_merge($customData, $customDataHipay);
                    }
                }
            }
        }

        $params['custom_data'] = json_encode(($customDataHipay));

        // Add device fingerprint for the transaction request (Token of device)
        $params['device_fingerprint'] = $payment->getAdditionalInformation('device_fingerprint');

        if (Mage::helper('hipay')->isSendCartItemsRequired($payment->getCcType())) {
            $params['basket'] = Mage::helper('hipay')->getCartInformation($payment->getOrder(),
                Allopass_Hipay_Helper_Data::STATE_AUTHORIZATION);
        }

        // Check if delivery method is required for the payment method
        if (Mage::helper('hipay')->isDeliveryMethodAndCartItemsRequired($payment->getCcType())) {
            if ($payment->getOrder()->getShippingMethod() && !$payment->getOrder()->getIsVirtual()) {
                Mage::helper('hipay')->processDeliveryInformation($payment->getOrder()->getShippingMethod(), Mage::app()->getStore(),$this, $params);
            } else{
                //TODO
            }
        }

        // Add Request resource (Informations module and cms)
        $params['source'] = Mage::helper('hipay')->getRequestSource();

        return $params;
    }

    /**
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param array $params
     * @return array $params
     */
    protected function getCustomerParams($payment, $params = array())
    {
        $order = $payment->getOrder();
        $params['email'] = $order->getCustomerEmail();
        $params['phone'] = $order->getBillingAddress()->getTelephone();

        if (($dob = $order->getCustomerDob()) != "") {
            $dob = new Zend_Date($dob);
            $validator = new Zend_Validate_Date();
            if ($validator->isValid($dob)) {
                $params['birthdate'] = $dob->toString('YYYYMMdd');
            }
        }

        $gender = $order->getCustomerGender();

        $customer = Mage::getModel('customer/customer');
        $customer->setData('gender', $gender);
        $attribute = $customer->getResource()->getAttribute('gender');
        if ($attribute) {
            $gender = $attribute->getFrontend()->getValue($customer);
            $gender = strtoupper(substr($gender, 0, 1));
        }

        if ($gender != "M" && $gender != "F") {
            $gender = "U";
        }


        $params['gender'] = $gender;
        $params['firstname'] = $order->getCustomerFirstname();
        $params['lastname'] = $order->getCustomerLastname();
        $params['recipientinfo'] = $order->getBillingAddress()->getCompany();
        $params['streetaddress'] = $order->getBillingAddress()->getStreet1();
        $params['streetaddress2'] = $order->getBillingAddress()->getStreet2();
        $params['city'] = $order->getBillingAddress()->getCity();

        $params['state'] = $order->getBillingAddress()->getRegionCode() ? $order->getBillingAddress()->getRegionCode() : $order->getBillingAddress()->getCity();

        $zipcode = explode('-', $order->getBillingAddress()->getPostcode());
        $params['zipcode'] = $zipcode[0];
        //$params['zipcode'] = $order->getBillingAddress()->getPostcode();
        $params['country'] = $order->getBillingAddress()->getCountry();

        return $params;
    }

    /**
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param array $params
     * @return array $params
     */
    protected function getShippingParams($payment, $params = array())
    {
        if ($payment->getOrder()->getIsVirtual()) {
            return $params;
        }

        $shippingAddress = $payment->getOrder()->getShippingAddress();
        $params['shipto_firstname'] = $shippingAddress->getFirstname();
        $params['shipto_lastname'] = $shippingAddress->getLastname();
        $params['shipto_recipientinfo'] = $shippingAddress->getCompany();
        $params['shipto_streetaddress'] = $shippingAddress->getStreet1();
        $params['shipto_streetaddress2'] = $shippingAddress->getStreet2();
        $params['shipto_city'] = $shippingAddress->getCity();

        $params['shipto_state'] = $shippingAddress->getRegionCode() ? $shippingAddress->getRegionCode() :  $shippingAddress->getCity();

        $params['shipto_zipcode'] = $shippingAddress->getPostcode();
        $params['shipto_country'] = $shippingAddress->getCountry();
        $params['shipto_msisdn'] = $shippingAddress->getTelephone();


        return $params;
    }

    /**
     *
     * @param $splitPayment
     * @return bool
     * @internal param Allopass_Hipay_Model_SplitPayment $spiltPayment
     */
    public function paySplitPayment($splitPayment)
    {
        $request = Mage::getModel('hipay/api_request', array($this));

        $order = Mage::getModel('sales/order')->load($splitPayment->getOrderId());
        if ($order->getId()) {
            $gatewayParams = $this->getGatewayParams($order->getPayment(), $splitPayment->getAmountToPay(), null,
                $splitPayment->getSplitNumber());

            //Added because if the same order_id tpp respond "Max Attempts exceed!"
            $gatewayParams['orderid'] .= $this->generateSplitOrderId($splitPayment);

            $gatewayParams['description'] = Mage::helper('hipay')->__("Order SPLIT %s by %s", $order->getIncrementId(),
                $order->getCustomerEmail());//MANDATORY;
            $gatewayParams['eci'] = 9;
            $gatewayParams['operation'] = self::OPERATION_SALE;
            $gatewayParams['payment_product'] = $this->getCcTypeHipay($order->getPayment()->getCcType());

            /**
             * Parameters specific to the payment product
             */
            $gatewayParams['cardtoken'] = $splitPayment->getCardToken();

            $gatewayParams['authentication_indicator'] = 0;//$this->getConfigData('use_3d_secure');
            $this->_debug($gatewayParams);

            $gatewayResponse = $request->gatewayRequest(Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_ORDER,
                $gatewayParams);

            $this->_debug($gatewayResponse->debug());


            return $gatewayResponse->getState();
        }
        return false;
    }

    protected function getCcTypeHipay($ccTypeMagento)
    {
        $ccTypes = Mage::getSingleton('hipay/config')->getCcTypesHipay();

        if (isset($ccTypes[$ccTypeMagento])) {
            return $ccTypes[$ccTypeMagento];
        } else { //Maybe it's already hipay code, we return it directly
            return $ccTypeMagento;
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

        if (!$lastTransaction) {
            return false;
        }

        /*if ($this->getOperation() == self::OPERATION_SALE && $lastTransaction->getTxnType() == Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH  )
            return false;
        */
        if ($lastTransaction->getTxnType() == Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE && $this->orderDue($payment->getOrder())) {
            return true;
        }

        if ($lastTransaction->getTxnType() != Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return $this
     */
    protected function _preauthorizeCapture($payment, $amount)
    {
        $transactionId = $payment->getLastTransId();

        $gatewayParams = array('operation' => 'capture', 'amount' => $amount);

        if (Mage::helper('hipay')->isSendCartItemsRequired($payment->getCcType())) {
            $gatewayParams['basket'] = Mage::helper('hipay')->getCartInformation($payment->getOrder(),
                Allopass_Hipay_Helper_Data::STATE_CAPTURE);
        }

        $this->_debug($gatewayParams);
        /* @var $request Allopass_Hipay_Model_Api_Request */
        $request = Mage::getModel('hipay/api_request', array($this));
        $uri = Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_MAINTENANCE . $transactionId;

        $gatewayResponse = $request->gatewayRequest($uri, $gatewayParams, $payment->getOrder()->getStoreId());

        $this->_debug($gatewayResponse->debug());

        switch ($gatewayResponse->getStatus()) {
            case "116":
                $this->addTransaction(
                    $payment,
                    $gatewayResponse->getTransactionReference(),
                    Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
                    array('is_transaction_closed' => 0),
                    array(),
                    Mage::helper('hipay')->getTransactionMessage(
                        $payment, self::OPERATION_MAINTENANCE_ACCEPT_CHALLENGE,
                        $gatewayResponse->getTransactionReference(), $amount
                    )
                );
                $payment->setIsTransactionPending(true);
                break;
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
                        $payment, self::OPERATION_MAINTENANCE_CAPTURE, $gatewayResponse->getTransactionReference(),
                        $amount
                    )
                );

                $payment->setIsTransactionPending(true);
                break;
            default:
                Mage::throwException($gatewayResponse->getStatus() . " ==> " . $gatewayResponse->getMessage() . " is not processed!");
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
     * @param bool $message
     * @return Mage_Sales_Model_Order_Payment_Transaction|null
     */
    public function addTransaction(
        Mage_Sales_Model_Order_Payment $payment,
        $transactionId,
        $transactionType,
        array $transactionDetails = array(),
        array $transactionAdditionalInfo = array(),
        $message = false
    ) {
        $payment->setTransactionId($transactionId);
        if (method_exists($payment, "resetTransactionAdditionalInfo")) {
            $payment->resetTransactionAdditionalInfo();
        }
        foreach ($transactionDetails as $key => $value) {
            $payment->setData($key, $value);
        }
        foreach ($transactionAdditionalInfo as $key => $value) {
            $payment->setTransactionAdditionalInfo($key, $value);
        }

        if (!class_exists("Mage_Sales_Model_Order_Payment_Transaction")) {
            return null;
        }

        if (method_exists($payment, "addTransaction")) {
            $transaction = $payment->addTransaction($transactionType, null, false, $message);
        } else {
            $transaction = $this->_addTransaction($payment, $transactionType, null, false);
        }

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
     * @return bool|Mage_Sales_Model_Order_Payment_Transaction|null
     */
    protected function _addTransaction($payment, $type, $salesDocument = null, $failsafe = false)
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
            $transaction = $this->_lookupTransaction($payment, $transactionId);

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
                    $parentTransaction = $this->_lookupTransaction($payment, $parentTransactionId);//
                    if ($parentTransaction) {
                        $parentTransaction->isFailsafe($failsafe)->close(false);
                        $payment->getOrder()->addRelatedObject($parentTransaction);
                    }
                }
            }
            return $transaction;
        }
        return false;
    }

    /**
     * Find one transaction by ID or type
     * @param $payment
     * @param string $txnId
     * @param bool|string $txnType
     * @return false|Mage_Sales_Model_Order_Payment_Transaction
     * @internal param $Mage_Sales_Model_Order_Payment
     */
    protected function _lookupTransaction($payment, $txnId, $txnType = false)
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
        if (!$this->getConfigData('currency') || $currencyCode == $this->getConfigData('currency')
            || in_array($currencyCode, $this->getConfigData('currency'))) {
            return true;
        }
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
        $fraud_review = $payment->getAdditionalInformation('fraud_review');
        return parent::canReviewPayment($payment) && ($fraud_type == 'challenged' && $fraud_review != 'allowed');
    }

    public function canRefund()
    {
        return $this->_canRefund;
    }

    protected function orderDue($order)
    {
        return $order->hasInvoices() && $order->getBaseTotalDue() > 0;
    }


    /**
     *
     * @return Mage_Checkout_Model_Session|Mage_Core_Model_Abstract
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
    public function _debug($debugData)
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

    /**
     *  With MOTO
     *
     * @return mixed
     */
    public function sendMailToCustomer()
    {
        return Mage::getStoreConfig('hipay/hipay_api_moto/moto_send_email', Mage::app()->getStore());
    }

    /**
     * Return an id with informations to TPP
     *
     * @param $splitPayment
     * @return string
     */
    public function generateSplitOrderId($splitPayment)
    {
        return "-split-" . $splitPayment->getSplitNumber() . "-" . $splitPayment->getAttempts() . "-" . $splitPayment->getId();
    }
}
