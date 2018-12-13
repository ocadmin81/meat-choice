<?php
/*
 * @category   Mageho
 * @package    Mageho_Sortproducts
 * @version    1.0.0
 * @copyright  Copyright (c) 2012  Mageho (http://www.mageho.com)
 * @license    http://www.mageho.com/license  Proprietary License
 */
 
class Mageho_Sortproducts_Helper_Data extends Mage_Core_Helper_Abstract
{
	const SORTPRODUCTS_DISPLAY_OUT_STOCK_PRODUCTS = 'sortproducts/general/display_out_stock_products';
	const SORTPRODUCTS_DISPLAY_ONLY_ENABLED_PRODUCTS = 'sortproducts/general/display_only_enabled_products';
	const SORTPRODUCTS_LIMIT_PRODUCTS = 'sortproducts/general/limit_products';
	
	public function displayOutStockProducts()
	{
		return Mage::getStoreConfigFlag(self::SORTPRODUCTS_DISPLAY_OUT_STOCK_PRODUCTS);	
	}
	
	public function displayOnlyEnabledProducts()
	{
		return Mage::getStoreConfigFlag(self::SORTPRODUCTS_DISPLAY_ONLY_ENABLED_PRODUCTS);
	}
	
	public function limitSortProducts()
	{
		$limit = (int) Mage::getStoreConfig(self::SORTPRODUCTS_LIMIT_PRODUCTS, 0);
		if ($limit > 0) {
			return $limit;	
		}
	}
	
	public function getImageWidth()
	{
		$width = (int) Mage::getStoreConfig('mageho_sortproducts/general/image_width');	
		if ($width > 0) {
			return $width;
		} else {
			return 60;
		}
	}
}