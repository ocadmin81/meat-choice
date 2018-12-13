<?php

class Softwarehtec_Cardcom_Block_Redirect extends Mage_Core_Block_Template
{

	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('softcardcom/redirect.phtml');
	}
 
	protected function getRedirectUrl()
	{
		$model = Mage::getModel('softcardcom/payment');
		return $model->getUkashUrl();

	}

 
	protected function getMode()
	{
		$model = Mage::getModel('softcardcom/payment');
		return $model->getConfigData("mode");

	}

}