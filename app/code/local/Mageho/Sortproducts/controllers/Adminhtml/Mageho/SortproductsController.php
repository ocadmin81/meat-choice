<?php
/*
 * @category   Mageho
 * @package    Mageho_Sortproducts
 * @version     1.0.0
 * @copyright  Copyright (c) 2012  Mageho (http://www.mageho.com)
 * @license      http://www.mageho.com/license  Proprietary License
 */
 
class Mageho_Sortproducts_Adminhtml_Mageho_SortproductsController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
	
	public function tabAction()
	{
		$this->getResponse()->setBody(
	        $this->getLayout()->createBlock('mageho_sortproducts/adminhtml_tab')->toHtml()
		);	
	}
	
	public function infoAction()
	{
		$productId  = $this->getRequest()->getParam('id', false);
		if ($productId) {
			$product = Mage::getModel('catalog/product')
            	->setStoreId($this->getRequest()->getParam('store', 0))
				->load($productId);
			
			if ($product) {
				Mage::register('product', $product);
				Mage::register('current_product', $product);
					
				$this->getResponse()->setBody(
					$this->getLayout()->createBlock('mageho_sortproducts/adminhtml_info')->toHtml()
				);
			}
        }
	}
	
	public function saveAction()
	{
	    $data = $this->getRequest()->getPost('data');
		$iframe = $this->getRequest()->getPost('iframe');
		$categoryId = $this->getRequest()->getParam('id', false);
		
		$saved = false;
		
        if ($categoryId && ctype_digit($categoryId)) 
        {
        	parse_str($data);
			for ($i = 0; $i < count($sortlist); $i++) 
			{
				$position = $i;
				$productId = $sortlist[$i];
							
				Mage::getResourceModel('mageho_sortproducts/position')->save($position, $productId, $categoryId);
			}
			
			// Disable below because too slow to process
					
			// Delete all cache for all categories 
			// Mage::app()->cleanCache(array(Mage_Catalog_Model_Category::CACHE_TAG));
					
			// Delete current category cache
			Mage::app()->cleanCache(array(Mage_Catalog_Model_Category::CACHE_TAG.'_'.$categoryId));
					
			Mage::getSingleton('adminhtml/session')->addSuccess(
				Mage::helper('mageho_sortproducts')->__('The position of products has been saved with success.')
			);
				
			$saved = true;
		}
		
		if ($saved) 
		{
			if ($iframe == 'true') {
				$resetUrl = Mage::helper('adminhtml')->getUrl('adminhtml/catalog_category/edit', array('active_tab_id' => 'category_info_tabs_sortproducts', '_current' => true));
				$this->getResponse()->setBody("window.top.categoryReset('{$resetUrl}', true);");
			} else {
				$stringLocalSavedPosition = Mage::helper('core')->jsQuoteEscape($this->__('The position of products has been saved with success.'));
				$this->getResponse()->setBody("window.opener.location.reload(true); alert('{$stringLocalSavedPosition}');");
			}
		}
	}
	
	/**
     * Check if admin has permissions to visit categories pages
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/categories');
    }
}