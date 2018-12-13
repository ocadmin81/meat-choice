<?php
require_once "Mage/Catalog/controllers/ProductController.php";
class Magentothem_Ajaxcartsuper_IndexController extends Mage_Catalog_ProductController
{
    public function indexAction()
    {
    	
		$this->loadLayout();     
		$this->renderLayout();
    }
    
        protected function _initProduct()
    {
      $categoryId = Mage::getSingleton('catalog/layer')
               ->getCurrentCategory()
               ->getId();
        $productId  = $this->getRequest()->getParam('id');

        $params = new Varien_Object();
        $params->setCategoryId($categoryId);

        return Mage::helper('catalog/product')->initProduct($productId, $this, $params);
    }
    
    public function productviewAction()
    { 
        if ($product = $this->_initProduct()) {

            if ($this->getRequest()->getParam('options')) {
                $notice = $product->getTypeInstance(true)->getSpecifyOptionMessage();
                Mage::getSingleton('catalog/session')->addNotice($notice);
            }

            Mage::getSingleton('catalog/session')->setLastViewedProductId($product->getId());
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
    
    public function cartdeleteAction() { 
	  
	  if($this->getRequest()->getParam('callback')) {
			$id = $this->getRequest()->getParam('id');
			$id = $this->getRequest()->getParam('id');
			if ($id) {
				try {
					Mage::getSingleton('checkout/cart')->removeItem($id)
							->save();
				} catch (Exception $e) {
					Mage::getSingleton('checkout/session')->addError($this->__('Cannot remove item'));
				}
			}			
			$this->loadLayout();
			$ajaxData = array();
			$content = "";
			if($this->loadLayout()->getLayout()->getBlock('content')) {
				$content = $this->loadLayout()->getLayout()->getBlock('content')->toHtml();
			}
			$ajaxData['subtotal'] = Mage::helper('checkout')->formatPrice(Mage::helper('checkout/cart')->getQuote()->getSubtotal());
			$ajaxData['qty'] = Mage::getSingleton('checkout/cart')->getSummaryQty();
			$mini_cart = "";
			if ($this->getLayout()->getBlock('minicart_head')) {
				$mini_cart = $this->getLayout()->getBlock('minicart_head')->toHtml();
			}			
			$formCart = $this->getLayout()->getBlockSingleton('checkout/cart')->setTemplate("checkout/cart.phtml")->toHtml();
			$ajaxData['form_cart']  = $content;
			$ajaxData['top_cart'] = $mini_cart;
			$this->getResponse()->setBody($this->getRequest()->getParam('callback').'('.Mage::helper('core')->jsonEncode($ajaxData).')');
		}
 
    }
}