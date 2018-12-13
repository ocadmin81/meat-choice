<?php
/*
 * @category   Mageho
 * @package    Mageho_Sortproduct
 * @version    1.0.0
 * @copyright  Copyright (c) 2012  Mageho (http://www.mageho.com)
 * @license    http://www.mageho.com/license  Proprietary License
 */

class Mageho_Sortproducts_Model_Resource_Position extends Mage_Catalog_Model_Resource_Category
{
    /**
     * Initialize resource
     */
    public function __construct()
    {
        parent::__construct();
        $resource = Mage::getSingleton('core/resource');
        $this->setType('catalog_category')
		    ->setConnection(
                $resource->getConnection('catalog_read'),
                $resource->getConnection('catalog_write')
            );
			
        $this->_productCategoryTable = $resource->getTableName('catalog/category_product');
        $this->_productCategoryIndexTable = $resource->getTableName('catalog/category_product_index');
    }
    
    public function save($position, $productId, $categoryId)
	{
		$adapter = $this->_getWriteAdapter();
        	
		$eventParams = array(
            'object' => $this,
            'position' => $position,
            'product_id' => $productId,
            'category_id' => $categoryId
        );
        
		$moveComplete = false;

        $adapter->beginTransaction();
        try {
	        $condition = array(
				$adapter->quoteInto("product_id=?", $productId),
				$adapter->quoteInto("category_id=?", $categoryId)
			);
			
			$adapter->update($this->_productCategoryTable, array('position' => $position), $condition);
			$adapter->update($this->_productCategoryIndexTable, array('position' => $position), $condition);
            $adapter->commit();
            
            $moveComplete = true;
        } catch (Exception $e) {
            $adapter->rollBack();
            Mage::logException($e->getMessage());
        }
	
		if ($moveComplete) {
			Mage::dispatchEvent('sortproducts_move', $eventParams);
		}
		
		return $this;
    }
}