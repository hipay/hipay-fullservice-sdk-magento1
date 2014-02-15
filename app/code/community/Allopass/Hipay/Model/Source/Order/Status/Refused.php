<?php

class Allopass_Hipay_Model_Source_Order_Status_Refused extends Allopass_Hipay_Model_Source_Order_Status {

    // set null to enable all possible
    protected $_stateStatuses = array(
        Mage_Sales_Model_Order::STATE_HOLDED,
        Mage_Sales_Model_Order::STATE_CANCELED,
        Mage_Sales_Model_Order::STATE_CLOSED
    );

}
