<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Layerednavigation
 * @version    1.3.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Layerednavigation_Model_Indexer_Filter extends Mage_Index_Model_Indexer_Abstract
{
    protected $_matchedEntities = array(
        Mage_Catalog_Model_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE,
            Mage_Index_Model_Event::TYPE_MASS_ACTION,
        ),
        Mage_Catalog_Model_Convert_Adapter_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mage_Core_Model_Config_Data::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        )
    );

    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $entity = $event->getEntity();
        if ($entity == Mage_Catalog_Model_Convert_Adapter_Product::ENTITY) {
            $event->addNewData('aw_layerednavigation_filter_reindex_all', true);
        }
        if ($entity == Mage_Catalog_Model_Product::ENTITY && $event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
            $this->_registerCatalogProductDeleteEvent($event);
        }
        if($entity == Mage_Core_Model_Config_Data::ENTITY){
            $configData = $event->getDataObject();
            if ($configData->getPath() == Mage_CatalogInventory_Helper_Data::XML_PATH_SHOW_OUT_OF_STOCK) {
                Mage::getSingleton('index/indexer')->getProcessByCode('aw_layerednavigation_filter')
                    ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
            }
        }
    }

    public function getName()
    {
        return Mage::helper('aw_layerednavigation')->__('Layered Navigation by aheadWorks');
    }

    public function getDescription()
    {
        return Mage::helper('aw_layerednavigation')->__('Index filters for layered navigation building');
    }

    protected function _getResource()
    {
        return Mage::getResourceModel('aw_layerednavigation/indexer_filter');
    }

    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if (!empty($data['aw_layerednavigation_filter_reindex_all'])) {
            $this->reindexAll();
        }
        $this->callEventHandler($event);
    }

    protected function _registerCatalogProductDeleteEvent(Mage_Index_Model_Event $event)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = $event->getDataObject();
        if ($product->getId()) {
            $event->addNewData('delete_product_id', $product->getId());
        }
        return $this;
    }
}