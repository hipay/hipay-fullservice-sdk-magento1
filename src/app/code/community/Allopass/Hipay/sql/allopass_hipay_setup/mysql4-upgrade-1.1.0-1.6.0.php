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

ALTER TABLE {$this->getTable('hipay_split_payment')} ADD COLUMN  `split_number` varchar(150)

"
);

$installer->run(
    "

ALTER TABLE {$this->getTable('hipay_split_payment')} ADD COLUMN `tax_amount_to_pay` decimal(12,4) NOT NULL,
ADD COLUMN  `total_tax_amount` decimal(12,4) NOT NULL

"
);


$installer->endSetup();

