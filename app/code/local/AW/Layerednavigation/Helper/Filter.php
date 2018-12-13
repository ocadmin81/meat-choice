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


class AW_Layerednavigation_Helper_Filter
{
    /**
     * @param AW_Layerednavigation_Model_Filter $filter
     *
     * @return AW_Layerednavigation_Block_Filter_Type_Abstract
     */
    public function createFrontendFilterRendererBlock(AW_Layerednavigation_Model_Filter $filter)
    {
        return Mage::app()->getLayout()
            ->createBlock($filter->getFrontendRendererBlockName())
            ->setFilter($filter)
        ;
    }

    /**
     * @param AW_Layerednavigation_Model_Filter $filter
     *
     * @return AW_Layerednavigation_Block_Filter_Type_Abstract
     */
    public function createFrontendFilterHelpRendererBlock(AW_Layerednavigation_Model_Filter $filter)
    {
        return Mage::app()->getLayout()
            ->createBlock('aw_layerednavigation/filter_help')
            ->setTemplate('aw_layerednavigation/filter/help.phtml')
            ->setFilter($filter)
        ;
    }

    public function setIndexerLock($isLocked)
    {
        $processResource = Mage::getResourceModel('index/process');
        $processModel = Mage::getSingleton('index/indexer')->getProcessByCode('aw_layerednavigation_filter');
        if ($isLocked) {
            $processResource->startProcess($processModel);
            $processModel->lock();
        } else {
            $processModel->unlock();
            $processResource->endProcess($processModel);
        }
        return $this;
    }
}