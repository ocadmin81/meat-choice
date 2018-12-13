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


class AW_Layerednavigation_Model_Observer
{
    const TRIGGER_REQUEST_PARAM = 'aw_layerednavigation';

    public function controllerFrontInitBefore($observer)
    {
        if (Mage::helper("aw_layerednavigation/config")->isEnabled() &&
            !(@class_exists('AW_Mobile2_Helper_Data') &&
                (AW_Mobile2_Helper_Data::isIphoneTheme() || AW_Mobile2_Helper_Data::isIPadTheme())
            )
        ) {
            return $this;
        }
        $config = Mage::getConfig();

        $node = $config->getNode('global/blocks/catalog/rewrite');
        unset($node->layer_view);
        unset($node->category_view);
        $node = $config->getNode('global/blocks/catalogsearch/rewrite');
        unset($node->layer);
        $node = $config->getNode('global/blocks/enterprise_search/rewrite');
        unset($node->catalog_layer_view);
        $node = $config->getNode('global/blocks/enterprise_search/rewrite');
        unset($node->catalogsearch_layer);
        $node = $config->getNode('global/blocks/awadvancedsearch/rewrite');
        unset($node->catalogsearch_layer);
        $node = $config->getNode('global/blocks/awshopbybrand/rewrite');
        unset($node->layer_view);
        $node = $config->getNode('global/blocks/awshopbybrand/rewrite');
        unset($node->brand);

        return $this;
    }

    public function beforeRenderLayout($observer)
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if (!$request->getParam(self::TRIGGER_REQUEST_PARAM, false)) {
            return $this;
        }
        unset($_GET[self::TRIGGER_REQUEST_PARAM]);
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestUri = str_replace(self::TRIGGER_REQUEST_PARAM . '=1', '', $requestUri);
        $_SERVER['REQUEST_URI'] = substr_replace($requestUri, '', -1);

        $layout = Mage::app()->getFrontController()->getAction()->getLayout();
        $blockList = array(
            'success'   => true,
        );
        $layout->setDirectOutput(false);
        $layout->getOutput();
        try {
            $blockList['block'] = array(
                'layer'     => $this->_getLayerHtml($layout),
                'catalog'   => $this->_getCatalogHtml($layout)
            );
        } catch (Exception $e) {
            Mage::logException($e);
            $blockList['success'] = false;
        }
        $this->_sendResponse(
            Zend_Json::encode($blockList)
        );
        return $this;
    }

    protected function _getLayerHtml($layout)
    {
        $blockNames = array(
            'catalog.leftnav',
            'catalogsearch.leftnav',
            'enterprisecatalog.leftnav',
            'enterprisesearch.leftnav',
            'advancedsearch.leftnav',
        );
        foreach ($blockNames as $name) {
            $block = $layout->getBlock($name);
            if (!$block) {
                continue;
            }
            return $block->toHtml();
        }
        return '';
    }

    protected function _getCatalogHtml($layout)
    {
        $blockNames = array(
            'category.products',
            'search.result',
            'brand',
        );
        foreach ($blockNames as $name) {
            $block = $layout->getBlock($name);
            if (!$block) {
                continue;
            }
            return $block->toHtml();
        }
        return '';
    }

    private function _sendResponse($html)
    {
        $response = Mage::app()->getResponse();
        $response->clearBody();
        $response->setHttpResponseCode(200);
        //remove location header from response
        $headers = $response->getHeaders();
        $response->clearHeaders();
        foreach ($headers as $header) {
            if ($header['name'] !== 'Location') {
                $response->setHeader($header['name'], $header['value'], $header['replace']);
            }
        }
        $response->sendHeaders();
        echo $html;
        exit(0);
    }
}