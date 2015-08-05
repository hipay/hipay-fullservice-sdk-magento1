<?php

require_once 'Mage/Adminhtml/controllers/Sales/Order/CreateController.php';

class Allopass_Hipay_Sales_Order_CreateController extends Mage_Adminhtml_Sales_Order_CreateController
{


   
    /**
     * Saving quote and create order
     */
    public function saveAction()
    {
        try {
            $this->_processActionData('save');
            $paymentData = $this->getRequest()->getPost('payment');
            if ($paymentData) {
                $paymentData['checks'] = Allopass_Hipay_Model_Method_Abstract::CHECK_USE_INTERNAL
                    | Allopass_Hipay_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                    | Allopass_Hipay_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                    | Allopass_Hipay_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                    | Allopass_Hipay_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
            }

            $order = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($this->getRequest()->getPost('order'))
                ->createOrder();

            $this->_getSession()->clear();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The order has been created.'));
            

            /**
             * if payment method is hipay so we need to change redirection 
             */
            if(strpos($order->getPayment()->getMethod(), 'hipay') !== false)
            {
            	
            	$this->_redirect('hipay/adminhtml_payment/sendRequest',array('_secure' => true));
            
	            // add order information to the session
	            Mage::getSingleton('checkout/session')->setLastOrderId($order->getId())
	            ->setMethod($order->getPayment()->getMethod())
	            ->setLastRealOrderId($order->getIncrementId());
            }
            else 
            {
            	
	             if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
	                 $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
	             } else {
	                 $this->_redirect('*/sales_order/index');
	            }
            }
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if( !empty($message) ) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        } catch (Mage_Core_Exception $e){
            $message = $e->getMessage();
            if( !empty($message) ) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        }
        catch (Exception $e){
            $this->_getSession()->addException($e, $this->__('Order saving error: %s', $e->getMessage()));
            $this->_redirect('*/*/');
        }
    }

}
