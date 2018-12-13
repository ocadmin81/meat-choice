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
 * @copyright  Copyright (c) 2012 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Advanced Product Options extension
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @author     MageWorx Dev Team
 */
class MageWorx_Adminhtml_Block_Customoptions_Reports extends MageWorx_Adminhtml_Block_Customoptions_Abstract {

    protected function _prepareLayout() {        
        $this->setChild('reports_grid', $this->getLayout()->createBlock('mageworx/customoptions_reports_grid', 'customoptions.reports.grid'));
        
        return parent::_prepareLayout();
    }
    
    public function getGridHtml() {
        return $this->getChildHtml('reports_grid');
    }

}