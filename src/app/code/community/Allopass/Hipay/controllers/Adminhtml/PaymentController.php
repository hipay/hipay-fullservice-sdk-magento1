<?php
class Allopass_Hipay_Adminhtml_PaymentController extends Mage_Adminhtml_Controller_Action
{
    /**
     *
     * @var Mage_Sales_Model_Order $order
     */
    protected $_order = null;

    public function reviewCapturePaymentAction()
    {
        /* @var $order Mage_Sales_Model_Order */
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);

        try {
            $order->getPayment()->accept();
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, Allopass_Hipay_Model_Method_Cc::STATUS_PENDING_CAPTURE);
            $message = $this->__('The payment has been accepted.');
            $order->save();
            $this->_getSession()->addSuccess($message);

            //Capture Payment
            /**
             * Check invoice create availability
             */
            if (!$order->canInvoice()) {
                $this->_getSession()->addError($this->__('The order does not allow creating an invoice.'));
                $this->_redirect('adminhtml/sales_order/view', array('order_id' => $order->getId()));
                return $this;
            }

            $invoice = $order->prepareInvoice();
            if (!$invoice->getTotalQty()) {
                Mage::throwException($this->__('Cannot create an invoice without products.'));
            }

            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);

            $invoice->register();
            $invoice->getOrder()->setIsInProcess(true);

            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());

            $transactionSave->save();

            $message = $this->__('The Capture was requested.');
            $this->_getSession()->addSuccess($message);

            $message = $this->__('You must reload the page to see new status.');
            $this->_getSession()->addSuccess($message);
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Failed to update the payment.'));
            Mage::logException($e);
        }
        $this->_redirect('adminhtml/sales_order/view', array('order_id' => $order->getId()));
    }

    public function sendRequestAction()
    {
        $order = $this->getOrder();
        $payment = $order->getPayment();

        $methodInstance = $this->_getMethodInstance();

        try {
            $redirectUrl = $methodInstance->place($payment, $order->getBaseTotalDue());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('adminhtml/sales_order/index');
            return $this;
        }

        // Send Mail to customer with payment information
        $url = $payment ->getAdditionalInformation('redirectUrl');
        if ($url && (strpos($order->getPayment()->getMethod(), 'hipay_hosted') !== false)) {
            $receiver = Mage::getModel('customer/customer')->load($payment->getOrder()->getCustomerId());
            Mage::helper('hipay')->sendLinkPaymentEmail($receiver, $payment->getOrder());
        }

        $this->_redirectUrl($redirectUrl);

        return $this;
    }

    /**
     *
     * @return Mage_Sales_Model_Order
     */
    protected function getOrder()
    {
        if (is_null($this->_order)) {
            if (($profileIds = $this->getCheckout()->getLastRecurringProfileIds())) {
                if (is_array($profileIds)) {
                    foreach ($profileIds as $profileId) {
                        /* @var $profile Mage_Sales_Model_Recurring_Profile */
                        $profile = Mage::getModel('sales/recurring_profile')->load($profileId);
                        /* @var $_helperRecurring Allopass_Hipayrecurring_Helper_Data */
                        $_helperRecurring = Mage::helper('hipayrecurring');

                        if ($_helperRecurring->isInitialProfileOrder($profile)) {
                            $this->_order = $_helperRecurring->createOrderFromProfile($profile);
                        } else {
                            $orderId = current($profile->getChildOrderIds());
                            $this->_order = Mage::getModel('sales/order')->load($orderId);

                            $additionalInfo = $profile->getAdditionalInfo();

                            $this->_order->getPayment()->setCcType(isset($additionalInfo['ccType']) ? $additionalInfo['ccType'] : "");
                            $this->_order->getPayment()->setCcExpMonth(isset($additionalInfo['ccExpMonth']) ? $additionalInfo['ccExpMonth'] : "");
                            $this->_order->getPayment()->setCcExpYear(isset($additionalInfo['ccExpYear']) ? $additionalInfo['ccExpYear'] : "");
                            $this->_order->getPayment()->setAdditionalInformation('token', isset($additionalInfo['token']) ? $additionalInfo['token'] : "");
                            $this->_order->getPayment()->setAdditionalInformation('create_oneclick', isset($additionalInfo['create_oneclick']) ? $additionalInfo['create_oneclick'] : 1);
                            $this->_order->getPayment()->setAdditionalInformation('use_oneclick', isset($additionalInfo['use_oneclick']) ? $additionalInfo['use_oneclick'] : 0);
                            $this->_order->getPayment()->setAdditionalInformation('selected_oneclick_card', isset($additionalInfo['selected_oneclick_card']) ? $additionalInfo['selected_oneclick_card'] : 0);
                        }



                        return $this->_order; //because only one nominal item in cart is authorized and Hipay not manage many profiles
                    }
                }

                Mage::throwException("An error occured. Profile Ids not present!");
            } else {
                $this->_order = Mage::getModel('sales/order')->load($this->getCheckout()->getLastOrderId());
            }
        }

        return $this->_order;
    }

    /**
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     *
     * @return Allopass_Hipay_Model_Method_Abstract $methodInstance
     */
    protected function _getMethodInstance()
    {
        $modelName = Mage::getStoreConfig('payment/'.$this->getCheckout()->getMethod()."/model");
        return Mage::getSingleton($modelName);
    }

    public function acceptAction()
    {
        if (($profileIds = Mage::getSingleton('checkout/session')->getLastRecurringProfileIds())) {
            if (is_array($profileIds)) {
                /* @var $gatewayResponse Allopass_Hipay_Model_Api_Response_Gateway */
                $gatewayResponse  = Mage::getSingleton('hipay/api_response_gateway', $this->getRequest()->getParams());
                $collection = Mage::getModel('sales/recurring_profile')->getCollection()
                    ->addFieldToFilter('profile_id', array('in' => $profileIds))
                ;
                $profiles = array();
                foreach ($collection as $profile) {
                    $additionalInfo = array();
                    $additionalInfo['ccType'] = $gatewayResponse->getBrand();
                    $additionalInfo['ccExpMonth'] = $gatewayResponse->getCardExpiryMonth() ;
                    $additionalInfo['ccExpYear'] = $gatewayResponse->getCardExpiryYear();
                    $additionalInfo['token'] = $gatewayResponse->getToken();
                    $additionalInfo['transaction_id'] = $gatewayResponse->getTransactionReference();
                    $profile->setAdditionalInfo($additionalInfo);

                    $profile->setState(Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE);

                    $profile->save();
                }
            }
        }

        $this->processResponse();

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->_redirect('adminhtml/sales_order/view', array('order_id' => $this->getOrder()->getId()));
        } else {
            $this->_redirect('adminhtml/sales_order/index');
        }


        return $this;
    }

    protected function processResponse()
    {
        $order = $this->getOrder();
        $payment = $order->getPayment();

        /* @var $gatewayResponse Allopass_Hipay_Model_Api_Response_Gateway */
        $gatewayResponse  = Mage::getSingleton('hipay/api_response_gateway', $this->getRequest()->getParams());

        $this->_getMethodInstance()->processResponseToRedirect($gatewayResponse, $payment, $order->getBaseTotalDue());
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
        $this->_redirect('adminhtml/sales_order_create/');
        //$this->_redirect('checkout/onepage/failure');
        return $this;
    }

    public function exceptionAction()
    {
        //$this->_redirect('checkout/onepage/failure');
        $this->_redirect('adminhtml/sales_order_create/');
        return $this;
    }

    public function cancelAction()
    {
        $this->processResponse();
        //$this->_redirect('checkout/cart');
        $this->_redirect('adminhtml/sales_order_create/');
        return $this;
    }

    /**
     * Add method to calculate amount from recurring profile
     * @param Mage_Sales_Model_Recurring_Profile $profile
     * @return int $amount
     **/
    public function getAmountFromProfile(Mage_Sales_Model_Recurring_Profile $profile)
    {
        $amount = $profile->getBillingAmount() + $profile->getTaxAmount() + $profile->getShippingAmount();

        if ($this->isInitialProfileOrder($profile)) {
            $amount += $profile->getInitAmount() ;
        }

        return $amount;
    }

    protected function isInitialProfileOrder(Mage_Sales_Model_Recurring_Profile $profile)
    {
        if (count($profile->getChildOrderIds()) && current($profile->getChildOrderIds()) == "-1") {
            return true;
        }

        return false;
    }

    /**
     *  Check if user is allowed to use this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order');
    }
}
