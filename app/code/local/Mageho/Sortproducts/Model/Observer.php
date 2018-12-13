<?php
/*
 * @category   Mageho
 * @package    Mageho_Sortproducts
 * @version    1.0.0
 * @copyright  Copyright (c) 2012  Mageho (http://www.mageho.com)
 * @license    http://www.mageho.com/license  Proprietary License
 */
 
class Mageho_Sortproducts_Model_Observer 
{
	public function addTabToCategoryPage($observer)
	{
	    $event = $observer->getEvent();
		$tabs = $event->getTabs();
		$category = $tabs->getCategory();
		$count = $category->getProductCount();
		
	    if ($category->getId() && ($count > 1))
		{
			$tabs->addTab('mageho_sortproducts', array(
				'label'     => Mage::helper('mageho_sortproducts')->__('Sort Category Products'),
				'content'   => $tabs->getLayout()->createBlock('mageho_sortproducts/adminhtml_tab')->setTemplate('mageho/sortproducts/tab.phtml')->toHtml()
				
				/*	
				 * Pour charger le bloc en ajax ajouter ces lignes et enlever la clé content juste au-dessus
				 */
				 
				#'url'       => Mage::getUrl('adminhtml/mageho_sortproducts/tab', array('_current' => true)),
				#'class'     => 'ajax'
			));
		}
		
		return $this;
	}
}