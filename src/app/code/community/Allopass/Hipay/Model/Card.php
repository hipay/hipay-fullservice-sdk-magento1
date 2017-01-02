<?php
/**
 *
 *
 * @method getCustomerId() int
 * @method getName() string
 * @method getCcExpMonth() int
 * @method getCcExpYear() int
 * @method getCcSecureVerify() int
 * @method getCclast4() int
 * @method getCcOwner() string
 * @method getCcType() string
 * @method getCcNumberEnc() string
 * @method getCcStatus() int
 * @method getCcToken() string
 * @method getIsDefault() bool
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 *
 */
class Allopass_Hipay_Model_Card extends Mage_Core_Model_Abstract
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

  /**
     * Init resource model and id field
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('hipay/card');
        $this->setIdFieldName('card_id');
    }
}
