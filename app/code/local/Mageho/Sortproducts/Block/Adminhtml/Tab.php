<?php
/*
 * @category   Mageho
 * @package    Mageho_Sortproducts
 * @version    1.0.0
 * @copyright  Copyright (c) 2012  Mageho (http://www.mageho.com)
 * @license    http://www.mageho.com/license  Proprietary License
 */
 
class Mageho_Sortproducts_Block_Adminhtml_Tab extends Mage_Adminhtml_Block_Template
{
	public function __construct()
    {
        parent::__construct();
		$this->setTemplate('mageho/sortproducts/tab.phtml');
    }
	
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		return $this;
	}
	
    public function getSortProductsUrl()
	{
	    return $this->getUrl('adminhtml/mageho_sortproducts/index', array('_current' => true));	
	}
}
