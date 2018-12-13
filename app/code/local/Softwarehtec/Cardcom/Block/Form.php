<?php


class Softwarehtec_Cardcom_Block_Form extends Mage_Payment_Block_Form
{
  	protected function _construct()
 	{
 		$this->setTemplate('softcardcom/form.phtml');
 		parent::_construct();
 	}

   	protected function _getConfig()
 	{
 		return Mage::getSingleton('payment/config');
 	}

}