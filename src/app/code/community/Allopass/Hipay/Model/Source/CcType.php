<?php

/**
 *
 * Allopass Hipay Credit cards types
 *
 */
class Allopass_Hipay_Model_Source_CcType extends Varien_Object
{
    public function toOptionArray()
    {

        $options = array();

        foreach (Mage::getSingleton('hipay/config')->getCcTypes() as $code => $name) {
            $options[] = array(
                'value' => $code,
                'label' => $name
            );
        }

        return $options;
    }

    public function toConfigOption()
    {
        $types = Mage::getSingleton('hipay/config')->getCcTypes();
        if ($this->getPath()) {
            $configData = Mage::getStoreConfig($this->getPath());
            $availableTypes = explode(",", $configData);
            $ordered = array();
            foreach ($availableTypes as $key) {
                if (array_key_exists($key, $types)) {
                    $ordered[$key] = $types[$key];
                    unset($types[$key]);
                }
            }

            return array_merge($ordered, $types);
        }
        return $types;
    }


}
