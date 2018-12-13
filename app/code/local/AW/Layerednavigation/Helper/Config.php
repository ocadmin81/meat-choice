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

class AW_Layerednavigation_Helper_Config extends Mage_Core_Helper_Data
{
    /**
     * "Enable Layered Navigation" from system config
     */
    const GENERAL_IS_ENABLED = 'aw_layerednavigation/general/is_enabled';

    /**
     * "Use AJAX" from system config
     */
    const GENERAL_IS_USE_AJAX = 'aw_layerednavigation/general/is_use_ajax';

    /**
     * "Enable reindex via cron" from system config
     */
    const GENERAL_IS_REINDEX_VIA_CRON = 'aw_layerednavigation/general/is_reindex_via_cron';

    /**
     * "Overlay Color" from system config
     */
    const STYLE_OVERLAY_COLOR = 'aw_layerednavigation/style/overlay_color';

    /**
     * "Overlay Opacity" from system config
     */
    const STYLE_OVERLAY_OPACITY = 'aw_layerednavigation/style/overlay_opacity';

    /**
     * "Overlay Image" from system config
     */
    const STYLE_OVERLAY_IMAGE = 'aw_layerednavigation/style/overlay_image';

    /**
     * "Overlay Image Width" from system config
     */
    const STYLE_OVERLAY_IMAGE_WIDTH = 'aw_layerednavigation/style/overlay_image_width';

    /**
     * "Overlay Image Height" from system config
     */
    const STYLE_OVERLAY_IMAGE_HEIGHT = 'aw_layerednavigation/style/overlay_image_height';

    /**
     * @param Mage_Core_Model_Store|int $store = null
     * @return boolean
     */
    public function isEnabled($store = null)
    {
        return $this->isModuleOutputEnabled() && Mage::getStoreConfigFlag(self::GENERAL_IS_ENABLED, $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store = null
     * @return boolean
     */
    public function isUseAjax($store = null)
    {
        return Mage::getStoreConfigFlag(self::GENERAL_IS_USE_AJAX, $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store = null
     * @return boolean
     */
    public function isReindexViaCron($store = null)
    {
        return Mage::getStoreConfigFlag(self::GENERAL_IS_REINDEX_VIA_CRON, $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store = null
     * @return boolean
     */
    public function getOverlayColor($store = null)
    {
        return Mage::getStoreConfig(self::STYLE_OVERLAY_COLOR, $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store = null
     * @return boolean
     */
    public function getOverlayOpacity($store = null)
    {
        return Mage::getStoreConfig(self::STYLE_OVERLAY_OPACITY, $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store = null
     * @return boolean
     */
    public function getOverlayImage($store = null)
    {
        if (!Mage::getStoreConfig(self::STYLE_OVERLAY_IMAGE, $store)) {
            return null;
        }
        $folderPath = Mage::getBaseUrl('media') . 'aw_layerednavigation' . '/' . 'config' . '/' . 'overlay' . '/';
        return $folderPath . Mage::getStoreConfig(self::STYLE_OVERLAY_IMAGE, $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store = null
     * @return boolean
     */
    public function getOverlayImageHeight($store = null)
    {
        return Mage::getStoreConfig(self::STYLE_OVERLAY_IMAGE_HEIGHT, $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store = null
     * @return boolean
     */
    public function getOverlayImageWidth($store = null)
    {
        return Mage::getStoreConfig(self::STYLE_OVERLAY_IMAGE_WIDTH, $store);
    }
}