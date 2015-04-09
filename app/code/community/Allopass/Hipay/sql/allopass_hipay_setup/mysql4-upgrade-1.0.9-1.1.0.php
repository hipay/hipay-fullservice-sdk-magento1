<?php

$installer = $this;


$installer->startSetup();

$currentVersion = Mage::getVersion();
if (version_compare($currentVersion, '1.4.2') == 1)
{
	

	$statusTable        = $installer->getTable('sales/order_status');
	$statusStateTable   = $installer->getTable('sales/order_status_state');
	$statusLabelTable   = $installer->getTable('sales/order_status_label');
	
	$statues = array('authorization_requested'=>
									array('label'=>'Authorization Requested',
											'code'=>'processing',
											'is_default'=>0
									),
					 'expired' => array('label'=>'Transaction Expired',
										'code'=>'holded',
										'is_default'=>0
									),
					 'partial_refund'=> array('label'=>'Partial Refund',
										'code'=>'processing',
										'is_default'=>0
									),
					'partial_capture'=> array('label'=>'Partial Capture',
							'code'=>'processing',
							'is_default'=>0
					),
	);
	
	foreach ($statues as $status=>$infos)
	{
		$label = $infos['label'];
		$code = $infos['code'];
		$is_default = $infos['is_default'];
		
		//Insert new Status in DB
		$data[0] = array(
				'status'    => $status,
				'label'     => $label,
		);
		
		$installer->getConnection()->insertArray($statusTable, array('status', 'label'), $data);
		//Insert relation between state and status
		$data[0] = array(
				'status'    => $status,
				'state'     => $code,
				'is_default'=> $is_default,
		);
		
		$installer->getConnection()->insertArray(
				$statusStateTable,
				array('status', 'state', 'is_default'),
				$data
		);
	}
	
	
}
 


$installer->endSetup();

