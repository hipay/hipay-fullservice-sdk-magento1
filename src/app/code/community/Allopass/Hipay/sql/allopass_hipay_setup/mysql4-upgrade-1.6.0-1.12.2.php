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

$installer = $this;


$installer->startSetup();


$installer->run(
    "

ALTER TABLE {$this->getTable('hipay_customer_card')} ADD COLUMN `created_at` DATE NOT NULL DEFAULT CURRENT_DATE

"
);

$installer->run(
    "

UPDATE {$this->getTable('hipay_customer_card')} SET created_at = CURDATE()

"
);

$installer->endSetup();

