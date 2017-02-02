<?php

$installer = $this;


$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('hipay_split_payment')} ADD COLUMN `tax_amount_to_pay` decimal(12,4) NOT NULL,
ADD COLUMN  `total_tax_amount` decimal(12,4) NOT NULL

"
);

$installer->endSetup();

