<?php

$installerCustomer = new Mage_Customer_Model_Entity_Setup('allopass_hipay_setup');
/* @var $installerCustomer Mage_Customer_Model_Entity_Setup */

$installerCustomer->startSetup();

$entityId = $installerCustomer->getEntityTypeId('customer');
$attribute = $installerCustomer->getAttribute($entityId,'hipay_alias_oneclick');
if(!$attribute)
{
	
	$installerCustomer->addAttribute('customer','hipay_alias_oneclick',array(
		'type'         => 'varchar',
	    'label'        => 'Alias Oneclick Hipay',
	    'visible'      => true,
	    'required'     => false,
		'unique'       => false,
		'sort_order'   	   => 700,
	    'default'	   => 0,
		'input'		   => 'text',

		));
		
	$usedInForms = array(
				'adminhtml_customer',
	        );
	
	$attribute   = Mage::getSingleton('eav/config')->getAttribute('customer', 'hipay_alias_oneclick');
	$attribute->setData('used_in_forms', $usedInForms);
	$attribute->setData('sort_order', 700);

	$attribute->save();

}

$entityId = $installerCustomer->getEntityTypeId('customer');
$attribute = $installerCustomer->getAttribute($entityId,'hipay_alias_recurring');
if(!$attribute)
{

	$installerCustomer->addAttribute('customer','hipay_alias_recurring',array(
			'type'         => 'varchar',
			'label'        => 'Alias Recurring Hipay',
			'visible'      => true,
			'required'     => false,
			'unique'       => false,
			'sort_order'   	   => 710,
			'default'	   => 0,
			'input'		   => 'text',

	));

	$usedInForms = array(
			'adminhtml_customer',
	);

	$attribute   = Mage::getSingleton('eav/config')->getAttribute('customer', 'hipay_alias_recurring');
	$attribute->setData('used_in_forms', $usedInForms);
	$attribute->setData('sort_order', 700);

	$attribute->save();

}

$attribute = $installerCustomer->getAttribute($entityId,'hipay_cc_number_enc');
if(!$attribute)
{

	$installerCustomer->addAttribute('customer','hipay_cc_number_enc',array(
			'type'         => 'varchar',
			'label'        => 'Card number encrypted hipay',
			'visible'      => true,
			'required'     => false,
			'unique'       => false,
			'sort_order'   	   => 750,
			'default'	   => 0,
			'input'		   => 'text',

	));

	$usedInForms = array(
			'adminhtml_customer',
	);

	$attribute   = Mage::getSingleton('eav/config')->getAttribute('customer', 'hipay_cc_number_enc');
	$attribute->setData('used_in_forms', $usedInForms);
	$attribute->setData('sort_order', 700);

	$attribute->save();

}

$attribute = $installerCustomer->getAttribute($entityId,'hipay_cc_exp_date');
if(!$attribute)
{

	$installerCustomer->addAttribute('customer','hipay_cc_exp_date',array(
			'type'         => 'varchar',
			'label'        => 'Card expiration date hipay',
			'visible'      => true,
			'required'     => false,
			'unique'       => false,
			'sort_order'   	   => 750,
			'default'	   => 0,
			'input'		   => 'text',

	));

	$usedInForms = array(
			'adminhtml_customer',
	);

	$attribute   = Mage::getSingleton('eav/config')->getAttribute('customer', 'hipay_cc_exp_date');
	$attribute->setData('used_in_forms', $usedInForms);
	$attribute->setData('sort_order', 700);

	$attribute->save();

}

$installerCustomer->endSetup();

