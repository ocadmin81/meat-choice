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

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
    CREATE  TABLE IF NOT EXISTS `{$installer->getTable('aw_layerednavigation/filter')}` (
        `entity_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `type` VARCHAR(255) NOT NULL,
        `code` VARCHAR(255) NOT NULL,
        `position` INT NOT NULL,
        `display_type` TINYINT UNSIGNED NOT NULL,
        `image_position` TINYINT UNSIGNED NOT NULL,
        `is_row_count_limit_enabled` TINYINT UNSIGNED NOT NULL,
        `row_count_limit` INT UNSIGNED NULL,
        `additional_data` TEXT NULL,
        PRIMARY KEY (`entity_id`),
        UNIQUE (`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Filter Table';


    CREATE  TABLE IF NOT EXISTS `{$installer->getTable('aw_layerednavigation/filter_eav')}` (
        `eav_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `filter_id` INT UNSIGNED NOT NULL,
        `store_id` INT UNSIGNED NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `value` TEXT NOT NULL,
        PRIMARY KEY (`eav_id`, `filter_id`),
        INDEX `fk_aw_layerednavigation_filter_eav_aw_layerednavigation_f_idx` (`filter_id` ASC),
        CONSTRAINT `fk_aw_layerednavigation_filter_eav__filter`
            FOREIGN KEY (`filter_id`)
            REFERENCES `{$installer->getTable('aw_layerednavigation/filter')}` (`entity_id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Filter Eav Table';


    CREATE  TABLE IF NOT EXISTS `{$installer->getTable('aw_layerednavigation/filter_option')}` (
        `option_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `filter_id` INT UNSIGNED NOT NULL,
        `image` VARCHAR(255) NULL,
        `position` INT NOT NULL,
        `additional_data` TEXT NULL,
        PRIMARY KEY (`option_id`, `filter_id`),
        INDEX `fk_aw_layerednavigation_filter_option_aw_layerednavigation__idx` (`filter_id` ASC),
        CONSTRAINT `fk_aw_layerednavigation_filter_option__filter`
            FOREIGN KEY (`filter_id`)
            REFERENCES `{$installer->getTable('aw_layerednavigation/filter')}` (`entity_id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Filter Option Table';


    CREATE  TABLE IF NOT EXISTS `{$installer->getTable('aw_layerednavigation/filter_option_eav')}` (
        `eav_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `option_id` INT UNSIGNED NOT NULL,
        `store_id` INT UNSIGNED NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `value` TEXT NOT NULL,
        PRIMARY KEY (`eav_id`, `option_id`),
        INDEX `fk_aw_layerednavigation_filter_option_eav_aw_layerednavig_idx` (`option_id` ASC),
        CONSTRAINT `fk_aw_layerednavigation_filter_option_eav__filter_option`
            FOREIGN KEY (`option_id`)
            REFERENCES `{$installer->getTable('aw_layerednavigation/filter_option')}` (`option_id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Filter Option Eav Table';

");

$installer->run("
    INSERT INTO {$this->getTable('core_config_data')} (`config_id`, `scope`, `scope_id`, `path`, `value`)
        VALUES (NULL, 'default', '0', 'aw_layerednavigation/style/overlay_image', 'default/ajax-loader-48px.gif');
");

$installer->endSetup();