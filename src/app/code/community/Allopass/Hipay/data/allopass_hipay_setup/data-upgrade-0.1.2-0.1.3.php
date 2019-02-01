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

$installer = $this;

$installer->startSetup();

$currentVersion = Mage::getVersion();
if (version_compare($currentVersion, '1.4.2') == 1) {

    $statusTable = $installer->getTable('sales/order_status');
    $statusStateTable = $installer->getTable('sales/order_status_state');
    $statusLabelTable = $installer->getTable('sales/order_status_label');

    $status = 'capture_requested';
    $label = 'Capture Requested';
    $code = "processing";

    //Insert new Status in DB
    $data[0] = array(
        'status' => $status,
        'label' => $label,
    );

    try {
        $installer->getConnection()->insertArray($statusTable, array('status', 'label'), $data);
        //Insert relation between state and status
        $data[0] = array(
            'status' => $status,
            'state' => $code,
            'is_default' => 0,
        );

        $installer->getConnection()->insertArray(
            $statusStateTable,
            array('status', 'state', 'is_default'),
            $data
        );

        $status = 'refund_requested';
        $label = 'Refund Requested';
        $code = "processing";

        //Insert new Status in DB
        $data[0] = array(
            'status' => $status,
            'label' => $label,
        );

        $installer->getConnection()->insertArray($statusTable, array('status', 'label'), $data);
        //Insert relation between state and status
        $data[0] = array(
            'status' => $status,
            'state' => $code,
            'is_default' => 0,
        );

        $installer->getConnection()->insertArray(
            $statusStateTable,
            array('status', 'state', 'is_default'),
            $data
        );
    } catch (Exception $ex) {
        //Most likely integrity constraint violation. May happen if all setup scripts are run together (including the
        //ones in the Magento core) on a Magento version at least equal to 1.6.0
    }
}

$installer->endSetup();
