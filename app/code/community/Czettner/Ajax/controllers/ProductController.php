<?php

require_once 'Mage/Catalog/controllers/ProductController.php';

class Czettner_Ajax_ProductController extends Mage_Catalog_ProductController {

    public function viewAction() {
        $product = $this->_initProduct();
		Mage::dispatchEvent('catalog_controller_product_compare', array('product'=>$product));
		if ($product) {
            Mage::dispatchEvent('catalog_controller_product_view', array('product'=>$product));

            if ($this->getRequest()->getParam('options')) {
                $notice = $product->getTypeInstance(true)->getSpecifyOptionMessage();
                Mage::getSingleton('catalog/session')->addNotice($notice);
            }

          //  Mage::getSingleton('catalog/session')->setLastViewedProductId($product->getId());
           // Mage::getModel('catalog/design')->applyDesign($product, Mage_Catalog_Model_Design::APPLY_FOR_PRODUCT);

            $this->_initProductLayout($product);
            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('tag/session');
            $this->_initLayoutMessages('checkout/session');
			
            $this->renderLayout();
			
        }
        else {
            if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
                $this->_redirect('');
            } elseif (!$this->getResponse()->isRedirect()) {
                $this->_forward('noRoute');
            }
        }
    }

}

