<?php

$installerCustomer = new Mage_Customer_Model_Entity_Setup('allopass_hipay_setup');
/* @var $installerCustomer Mage_Customer_Model_Entity_Setup */

$installerCustomer->startSetup();

$entityId = $installerCustomer->getEntityTypeId('customer');
$attribute = $installerCustomer->getAttribute($entityId, 'hipay_cc_type');
if (!$attribute) {

    $installerCustomer->addAttribute(
        'customer',
        'hipay_cc_type',
        array(
            'type' => 'varchar',
            'label' => 'Card Type hipay',
            'visible' => true,
            'required' => false,
            'unique' => false,
            'sort_order' => 800,
            'default' => 0,
            'input' => 'text',

        )
    );

    $usedInForms = array(
        'adminhtml_customer',
    );

    $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'hipay_cc_type');
    $attribute->setData('used_in_forms', $usedInForms);
    $attribute->setData('sort_order', 800);

    $attribute->save();

}

$installerCustomer->endSetup();

