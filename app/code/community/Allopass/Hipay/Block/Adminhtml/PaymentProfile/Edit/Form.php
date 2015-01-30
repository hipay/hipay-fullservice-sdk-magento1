<?php

class Allopass_Hipay_Block_Adminhtml_PaymentProfile_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
    	/* @var $profile Allopass_Hipay_Model_PaymentProfile */
    	$profile = Mage::registry('payment_profile');
    	
        $form = new Varien_Data_Form(array('id'=>'edit_form','action' => $this->getData('action'), 'method' => 'post'));
        
        $fieldset = $form->addFieldset('paymentProfile_form', array('legend'=>Mage::helper('hipay')->__('Payment Profile')));
        
        if ($profile->getProfileId()) {
        	$fieldset->addField('profile_id', 'hidden', array(
        			'name' => 'profile_id',
        	));
        }
        $fieldset->addField('name', 'text', array(
        		'label'     => Mage::helper('hipay')->__('Name'),
        		'title'     => Mage::helper('hipay')->__('Name'),
        		'class'     => 'required-entry',
        		'required'  => true,
        		'name'      => 'name',
        ));
        
        $fieldset->addField('period_unit', 'select', array(
        		'label'     => $profile->getFieldLabel('period_unit'),
        		'title'     => $profile->getFieldLabel('period_unit'),
        		'class' 	=> 'required-entry',
        		'name'      => 'period_unit',
        		'values'    => Mage::getSingleton('hipay/paymentProfile')->getAllPeriodUnits(),
        		'note'=>$this->__('Unit for billing during the subscription period.')
        		)
        );
        
        $fieldset->addField('period_frequency', 'text', array(
        		'label'     => $profile->getFieldLabel('period_frequency'),
        		'title'     => $profile->getFieldLabel('period_frequency'),
        		'class'     => 'required-entry validate-number',
        		'required'  => true,
        		'name'      => 'period_frequency',
        		'note'=>$this->__('Number of billing periods that make up one billing cycle.')
        ));
        
        $fieldset->addField('period_max_cycles', 'text', array(
        		'label'     => $profile->getFieldLabel('period_max_cycles'),
        		'title'     => $profile->getFieldLabel('period_max_cycles'),
        		'class'     => 'required-entry validate-number',
        		'required'  => true,
        		'name'      => 'period_max_cycles',
        		'note'=>$this->__('The number of billing cycles for payment period.')
        ));
        
        $fieldset->addField('payment_type', 'select', array(
        		'label'     => $profile->getPaymentTypeLabel('payment_type'),
        		'title'     => $profile->getPaymentTypeLabel('payment_type'),
        		'name'      => 'payment_type',
        		'values'    => $profile->getAllPaymentTypes(),
        )
        );
        
        $form->setUseContainer(true);
        $form->setValues($profile->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
