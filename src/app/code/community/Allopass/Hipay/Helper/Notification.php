<?php
/**
 * HiPay Enterprise SDK Magento
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 */


/**
 * Handle easy notification adding to Magento
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2019 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Helper_Notification extends Mage_Core_Helper_Abstract
{
    /**
     * Function adding a notification to the system if its corresponding URL has not been already registered
     * The function needs one array parameters with the following values
     *  - title : The notification's title
     *  - description : The notification's body, shown only in the notification admin screen
     *  - url : The redirection URL for the "Read more" link
     *  - severity : The notification's severity, from the Mage_AdminNotification_Model_Inbox class
     *  - date_added : The date corresponding to the notification
     *
     * @param $data array Notification parameters array
     */
    public function addNotification($data){
        // Checking if the notification has already been logged, even in another language
        $inboxModel = Mage::getModel('adminnotification/inbox');
        $noticeTable = $inboxModel->getResource()->getMainTable();
        $adapter = $inboxModel->getResource()->getReadConnection();
        $select = $adapter->select()
            ->from($noticeTable)
            ->where('is_remove=?', 0)
            ->where('url=:url')
            ->where('severity=:severity')
        ;

        $allNotices = $adapter->fetchAll($select, array(
            ":url" => $data['url'],
            ":severity" => $data['severity']
        ));


        if(count($allNotices) == 0) {
            /*
             * The parse function checks if the $versionData message exists in the inbox,
             * otherwise it will create it and add it to the inbox.
             */
            $inboxModel->parse(array($data));
        }
    }
}
