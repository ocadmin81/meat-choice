<?php
/*
 * @category   Mageho
 * @package    Mageho_Sortproducts
 * @version    1.0.0
 * @copyright  Copyright (c) 2012  Mageho (http://www.mageho.com)
 * @license    http://www.mageho.com/license  Proprietary License
 */
 
class Mageho_Sortproducts_Block_Adminhtml_Sortgrid extends Mage_Adminhtml_Block_Template
{
	public function __construct()
    {
        parent::__construct();
		$this->setTemplate('mageho/sortproducts/sortgrid.phtml');
    }
	
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		return $this;
	}
	
	/**
     * ACL validation before html generation
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (Mage::getSingleton('admin/session')->isAllowed('catalog/categories')) {
            return parent::_toHtml();
        }
        return '';
    }
	
	public function getProductsCollection()
	{
		if (!$this->_productsCollection) 
		{
			$store = (int) $this->getRequest()->getParam('store', 0);
			$category_id = (int) $this->getRequest()->getParam('id', 0);
			
			$category = Mage::getSingleton('catalog/category')->setStoreId($store)->load($category_id);	
			$products = $category->getProductsPosition();		   
			
			$collection = Mage::getModel('catalog/product')->getCollection()
			    #->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
				->addAttributeToSelect('*')
				->joinField('position', 'catalog/category_product', 'position', 'product_id=entity_id', '{{table}}.category_id='.$category_id, 'left')
				#->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')
				#->joinField('rating', 'review/review_aggregate', 'rating_summary', 'entity_pk_value=entity_id', '{{table}}.store_id=1', 'left') // Modifier store_id
				->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store)
				->addFieldToFilter('visibility', array('in' => array(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG, Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)))
				->addFieldToFilter('entity_id', array('in' => array_keys($products)))
				->addStoreFilter($store);
			
			if (Mage::helper('mageho_sortproducts')->displayOnlyEnabledProducts()) {
				$collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $store)
					->addFieldToFilter('status', 1);
			}

			// On cache les produits épuisés
			if (Mage::helper('mageho_sortproducts')->displayOutStockProducts()) {
				/*
				if ($websiteId = Mage::app()->getWebsite()->getWebsiteId()) {
					$collection->joinField('stock_status', 'cataloginventory/stock_status', 'stock_status', 'product_id=entity_id', array('stock_status' => Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK, 'website_id' => $websiteId));
				}
				*/
				
				$collection->joinField('stock_status', 'cataloginventory/stock_status', 'stock_status', 'product_id=entity_id', array('stock_status' => Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK));
			}
			
			$collection->setOrder('position', 'asc');
			
			if ($limit = Mage::helper('mageho_sortproducts')->limitSortProducts()) {
				$collection->setPageSize($limit);	
			}
			
			$collection->setCurPage(1);

			$this->_productsCollection = $collection;
		}
		return $this->_productsCollection;
	}
	
	public function getImageWidth()
	{
		return Mage::helper('mageho_sortproducts')->getImageWidth();
	}
	
    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getFormKey()
    {
        return Mage::getSingleton('core/session')->getFormKey();
    }
	
	public function getSaveUrl()
	{
	    return $this->getUrl('*/*/save', array('_current' => true));	
	}
	
	public function getProductInfoUrl($product)
	{
	    return $this->getUrl('*/*/info', array('id' => $product->getId(), '_current' => true));	
	}
	
	public function isDisabled($product) 
	{
		return $product->getStatus() && ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED);	
	}
}