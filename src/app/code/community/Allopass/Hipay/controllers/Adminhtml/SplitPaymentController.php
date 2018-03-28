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

/**
 *
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Adminhtml_SplitPaymentController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Init actions
     *
     * @return Allopass_Hipay_Adminhtml_SplitPaymentController
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('sale/hipay_payment')
            ->_addBreadcrumb(
                Mage::helper('hipay')->__('Hipay Split payments'),
                Mage::helper('hipay')->__('Hipay Split payments')
            );
        return $this;
    }


    public function indexAction()
    {
        $this->_title($this->__('Hipay'))
            ->_title($this->__('Hipay Split payments'));

        $this->_initAction()
            ->renderLayout();

        return $this;
    }

    public function editAction()
    {
        $this->_title($this->__('Hipay'))
            ->_title($this->__('Hipay Split Payment'));

        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('split_payment_id');
        $model = Mage::getModel('hipay/splitPayment');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('hipay')->__('This split payment no longer exists.')
                );
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title(
            $model->getId() ? $this->__("Split Payment for order %s", $model->getRealOrderId()) : $this->__(
                'New split payment'
            )
        );

        // 3. Set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        Mage::register('split_payment', $model);

        // 5. Build edit form

        $this->_initAction()->renderLayout();
    }

    public function newAction()
    {
        $this->_redirect('*/*/edit');
    }

    public function saveAction()
    {
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {
            $data = $this->_filterDates($data, array("date_to_pay"));
            //init model and set data
            $model = Mage::getModel('hipay/splitPayment');

            if ($id = $this->getRequest()->getParam('split_payment_id')) {
                $model->load($id);
            }

            $model->setData($data);

            // try to save it
            try {
                // save the data
                $model->save();

                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('hipay')->__('The split payment has been saved.')
                );
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('split_payment_id' => $model->getId(), '_current' => true));
                    return;
                }
                // go to grid
                $this->_redirect('*/*/');
                return;

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException(
                    $e,
                    Mage::helper('hipay')->__('An error occurred while saving the split payment.')
                );
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect(
                '*/*/edit',
                array('split_payment_id' => $this->getRequest()->getParam('split_payment_id'))
            );
            return;

        }
        $this->_redirect('*/*/');
    }

    public function payNowAction()
    {

        //init model and set data
        /* @var $model Allopass_Hipay_Model_SplitPayment */
        $model = Mage::getModel('hipay/splitPayment');

        if ($id = $this->getRequest()->getParam('split_payment_id')) {
            $model->load($id);

            try {
                $model->pay();
                switch ($model->getStatus()) {
                    case Allopass_Hipay_Model_SplitPayment::SPLIT_PAYMENT_STATUS_COMPLETE:
                        $this->_getSession()->addSuccess(
                            Mage::helper('hipay')->__('The split payment has been paid')
                        );
                        break;
                    case Allopass_Hipay_Model_SplitPayment::SPLIT_PAYMENT_STATUS_FAILED:
                    default:
                        $this->_getSession()->addError(
                            Mage::helper('hipay')->__('The split payment has NOT been paid')
                        );
                        break;
                }

            } catch (Exception $e) {

                $this->_getSession()->addException(
                    $e,
                    Mage::helper('hipay')->__('An error occurred while paid the split payment.')
                );

            }

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('split_payment_id' => $model->getId(), '_current' => true));
                return;
            }
        }

        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('system/config');
    }

}
