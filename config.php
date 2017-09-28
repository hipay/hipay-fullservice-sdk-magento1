<?php

return array(
  'extension_name'         => 'Allopass_Hipay',
  'summary'                => 'Official HiPay Fullservice payment extension.',
  'description'            => 'HiPay Fullservice is the first payment platform oriented towards merchants that responds to all matters related to online payment: transaction processing, risk management, relationship management with banks and acquirers, financial reconciliation or even international expansion.',
  'notes'                  => 'Official HiPay Fullservice payment extension',
  'extension_version'      => '0.1.0',
  'skip_version_compare'   => false,
  'auto_detect_version'    => true,

  'stability'              => 'stable',
  'license'                => 'General Public License (GPL)',
  'channel'                => 'community',

  'author_name'            => 'Kassim Belghait',
  'author_user'            => 'Sirateck',
  'author_email'           => 'kassim@sirateck.com',

  'base_dir'               => __DIR__.'/dist',
  'archive_files'          => 'Allopass_Hipay.tar',
  'path_output'            => __DIR__.'/dist',

  'php_min'                => '5.2.0',
  'php_max'                => '6.0.0',

  'extensions'             => array()
);
