<?php

class Allopass_Hipay_Model_Source_Order_Status_New extends Allopass_Hipay_Model_Source_Order_Status
{

    // set null to enable all possible
    protected $_stateStatuses = array(
        Mage_Sales_Model_Order::STATE_NEW,
        Mage_Sales_Model_Order::STATE_PROCESSING
    );
}
