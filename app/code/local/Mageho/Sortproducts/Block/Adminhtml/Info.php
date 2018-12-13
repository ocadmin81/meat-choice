<?php
/*
 * @category   Mageho
 * @package    Mageho_Sortproducts
 * @version    1.0.0
 * @copyright  Copyright (c) 2012  Mageho (http://www.mageho.com)
 * @license    http://www.mageho.com/license  Proprietary License
 */
 
class Mageho_Sortproducts_Block_Adminhtml_Info extends Mage_Adminhtml_Block_Template
{
	public function __construct()
    {
        parent::__construct();
		$this->setTemplate('mageho/sortproducts/info.phtml');
    }
	
	public function getSavePercentage($product) 
	{
	    if ($product->getPrice() == $product->getFinalPrice()) {
			return false;
		}
		
		$save = number_format( (100 - ( $product->getFinalPrice() / $product->getPrice() ) * 100) );
		return '-' . $save  . '%';
	}
	
	public function getAttributesToShow()
	{
		$columnSettings = array();
		$attributes = explode(',', Mage::getStoreConfig('mageho_sortproducts/general/show_attributes'));
        foreach ($attributes as $attribute) {
            $columnSettings[] = trim($attribute);
        }
		
		if (empty($columnSettings)) {
			return false;
		}
		
		$countColumnSettings = count($columnSettings);
		
		if ($countColumnSettings < 1) {
			return false;	
		}
		
		if ($countColumnSettings == 1 && current($columnSettings) == '') {
			return false;	
		}
		
        return $columnSettings;
    }
	
	public function getAttributeFrontendLabel($product, $attribute)
	{
		return $product->getResource()->getAttribute($attribute)->getFrontendLabel();
	}
	
	public function getAttributeFrontendValue($product, $attribute)
	{
		if ($product->getAttributeText($attribute)) {
			return $product->getAttributeText($attribute);
		} elseif ($product->getData($attribute)) {
			return $product->getData($attribute);
		} else {
			return $this->__('N.C.');
		}
	}
}