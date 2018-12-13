<?php
 
 
class  Softwarehtec_Cardcom_Block_Info extends Mage_Payment_Block_Info
{

	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('softcardcom/info.phtml');
	}

	public function getTitle()
	{ 
			$model = Mage::getModel('softcardcom/payment');
			return $model->getTitle(); 
	}



}