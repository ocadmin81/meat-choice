<?php

class Aurora_Askfordetails_Helper_Data extends Mage_Core_Helper_Abstract {

    public function checkIfMailForAskForDetailsIsSet() {
        $config_value = Mage::getStoreConfig('trans_email/ident_custom4/email');
        return ($config_value != "") ? TRUE : FALSE;
    }

}
