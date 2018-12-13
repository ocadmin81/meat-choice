<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2014 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Advanced Product Options extension
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @author     MageWorx Dev Team
 */

/* @var $installer MageWorx_CustomOptions_Model_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

// view_mode = hidden => to type = 'hidden';
$installer->run("UPDATE `{$installer->getTable('catalog_product_option')}` AS option_tbl, 
    `{$installer->getTable('customoptions/option_view_mode')}` AS view_mode_tbl 
    SET option_tbl.`type` = 'hidden', view_mode_tbl.`view_mode` = 1
    WHERE option_tbl.`type` IN ('drop_down', 'drop_down', 'multiple', 'radio', 'checkbox', 'swatch', 'multiswatch') 
    AND option_tbl.`option_id` = view_mode_tbl.`option_id` AND view_mode_tbl.`view_mode` = 2;");

    
// fix and clean up the debris of tables whith options
$installer->run("
    DELETE t1 FROM `{$installer->getTable('catalog_product_option')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_entity')}` WHERE `entity_id` = t1.`product_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('catalog_product_option_title')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('catalog_product_option_price')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('catalog_product_option_type_value')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('catalog_product_option_type_title')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option_type_value')}` WHERE `option_type_id` = t1.`option_type_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('catalog_product_option_type_price')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option_type_value')}` WHERE `option_type_id` = t1.`option_type_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('customoptions/option_type_image')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option_type_value')}` WHERE `option_type_id` = t1.`option_type_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('custom_options_relation')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('customoptions/option_view_mode')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('customoptions/option_description')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('customoptions/option_default')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option')}` WHERE `option_id` = t1.`option_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('customoptions/option_type_tier_price')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option_type_price')}` WHERE `option_type_price_id` = t1.`option_type_price_id`) = 0;
    DELETE t1 FROM `{$installer->getTable('customoptions/option_type_special_price')}` AS t1 WHERE (SELECT COUNT(*) FROM `{$installer->getTable('catalog_product_option_type_price')}` WHERE `option_type_price_id` = t1.`option_type_price_id`) = 0;        
");


// set Enable Option Description = Yes
$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
$select = $connection->select()->from($installer->getTable('customoptions/option_description'), array('COUNT(*)'));
$descriptions = $connection->fetchOne($select);
if ($descriptions>0) {
    $installer->run("INSERT IGNORE INTO `{$installer->getTable('core_config_data')}` (`path`, `value`) VALUES ('mageworx_catalog/customoptions/option_description_enabled', 1);");
}

// set Enable Option Description = Tooltip
$select = $connection->select()->from($installer->getTable('core_config_data'), array('value'))->where("`path` = 'mageworx_catalog/customoptions/description_appearance'");
$appearance = $connection->fetchOne($select);
if ($appearance==2) {
    $installer->run("UPDATE `{$installer->getTable('core_config_data')}` SET `value` = 2 WHERE `path` = 'mageworx_catalog/customoptions/option_description_enabled';");
    $installer->run("DELETE FROM `{$installer->getTable('core_config_data')}` WHERE `path` = 'mageworx_catalog/customoptions/description_appearance';");
}
    
// set Link Assigned Product's Attributes to Option via SKU = Qty
$select = $connection->select()->from($installer->getTable('core_config_data'), array('value'))->where("`path` = 'mageworx_catalog/customoptions/assigned_product_attributes'");
$assigned = $connection->fetchOne($select);
if ($assigned!==false) {
    $assigned = explode(',', $assigned);
    if (!in_array('5', $assigned)) $assigned[] = 5;
    $assigned = implode(',', $assigned);
    $installer->run("UPDATE IGNORE `{$this->getTable('core_config_data')}` SET `value` = '". $assigned ."' WHERE `path` = 'mageworx_catalog/customoptions/assigned_product_attributes'");
}

$installer->run("
-- DROP TABLE IF EXISTS {$installer->getTable('customoptions/option_type_description')};
CREATE TABLE IF NOT EXISTS {$installer->getTable('customoptions/option_type_description')} (
  `option_type_description_id` int(10) unsigned NOT NULL auto_increment,
  `option_type_id` int(10) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY  (`option_type_description_id`),
  UNIQUE KEY `option_type_id+store_id` (`option_type_id`,`store_id`),
  KEY `store_id` (`store_id`),
  CONSTRAINT `FK_MAGEWORX_CUSTOM_OPTIONS_TYPE_DESCRIPTION_OPTION` FOREIGN KEY (`option_type_id`) REFERENCES `{$installer->getTable('catalog/product_option_type_value')}` (`option_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_MAGEWORX_CUSTOM_OPTIONS_TYPE_DESCRIPTION_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


// set product attr options_container = container1
//$attribute = $this->getAttribute('catalog_product', 'options_container');
//if (!empty($attribute['attribute_id'])) {
//    $this->run("INSERT INTO `{$this->getTable('catalog_product_entity_varchar')}` (`entity_type_id`, `attribute_id`, `entity_id`, `value`) SELECT '{$attribute['entity_type_id']}' AS entity_type_id, '{$attribute['attribute_id']}' AS attribute_id, `entity_id`, 'container1' AS value FROM `{$this->getTable('catalog_product_entity')}` WHERE entity_id IN (SELECT entity_id FROM {$this->getTable('catalog_product_entity')}) ON DUPLICATE KEY UPDATE `value` = 'container1';");
//}    

// set has_options and required_options = 1 flag to products
//$this->run("UPDATE `{$installer->getTable('catalog_product_entity')}` SET `has_options` = 1 WHERE `entity_id` IN (SELECT DISTINCT `product_id` FROM `{$installer->getTable('catalog_product_option')}`);");
//$this->run("UPDATE `{$installer->getTable('catalog_product_entity')}` SET `required_options` = 1 WHERE `entity_id` IN (SELECT DISTINCT `product_id` FROM `{$installer->getTable('catalog_product_option')}` WHERE `is_require` = 1);");

$installer->endSetup();