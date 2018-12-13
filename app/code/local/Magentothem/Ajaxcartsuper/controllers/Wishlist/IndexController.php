<?php
require_once 'Mage/Wishlist/controllers/IndexController.php';
class Magentothem_Ajaxcartsuper_Wishlist_IndexController extends Mage_Wishlist_IndexController
{
    /**
     * ovveride Adding new item
     */
    public function addAction()
    {   
		//Mage::log(Mage::getSingleton('customer/session')->isLoggedIn(), null,'wishlist.log');
      if ($this->getRequest()->getParam('callback')) {
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return $this->norouteAction();
        }
        $session = Mage::getSingleton('customer/session');
        $productId = (int) $this->getRequest()->getParam('product');
        if (!$productId) {
            $this->_redirect('*/');
            return;
        }		
        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $session->addError($this->__('Cannot specify product.'));
            $this->_redirect('*/');
            return;
        }
        try {
            $requestParams = $this->getRequest()->getParams();
            if ($session->getBeforeWishlistRequest()) {
                $requestParams = $session->getBeforeWishlistRequest();
                $session->unsBeforeWishlistRequest();
            }
            $buyRequest = new Varien_Object($requestParams);
            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                Mage::throwException($result);
            }
            $wishlist->save();
            Mage::dispatchEvent(
                'wishlist_add_product',
                array(
                    'wishlist'  => $wishlist,
                    'product'   => $product,
                    'item'      => $result
                )
            );
            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
            } else {
                $referer = $this->_getRefererUrl();
            }

            /**
             *  Set referer to avoid referring to the compare popup window
             */
            $session->setAddActionReferer($referer);
            Mage::helper('wishlist')->calculate();
            $message = $this->__('%1$s has been added to your wishlist. Click <a href="%2$s">here</a> to continue shopping.', $product->getName(), Mage::helper('core')->escapeUrl($referer));
            //$session->addSuccess($message);
        }
        catch (Mage_Core_Exception $e) {
            $session->addError($this->__('An error occurred while adding item to wishlist: %s', $e->getMessage()));
        }
        catch (Exception $e) {
            $session->addError($this->__('An error occurred while adding item to wishlist.'));
        }
        $this->loadLayout();
        $sidebarWishlist = "";
        if($this->getLayout()->getBlock('wishlist_sidebar')){
            $sidebarWishlist = $this->getLayout()->getBlock('wishlist_sidebar')->toHtml();
        }
        if($this->getLayout()->getBlock('top.links')){
                    $toplink = $this->getLayout()->getBlock('top.links')->toHtml();
        }
		$_wishlistItem = Mage::getModel('wishlist/item')->loadByProductWishlist(
				Mage::helper('wishlist')->getWishlist()->getId(),
				$product->getId(),
				$product->getStoreId()
		);
		$_wishlistRemoveUrl = Mage::helper('wishlist')->getRemoveUrl($_wishlistItem);
		$whishlistCount = Mage::helper('wishlist')->getItemCount();
        $ajaxData['status'] = 1;
        $ajaxData['wishlist_sidebar'] = $sidebarWishlist;
        $ajaxData['type_sidebar'] = 'wishlist';
        $ajaxData['top_link'] = $toplink;
		$ajaxData['wishlistItem'] = $_wishlistRemoveUrl;
		$ajaxData['product_id'] = $product->getId();
		$ajaxData['wish_count'] = $whishlistCount;
		
        if (Mage::getStoreConfig('ajaxcartsuper/ajaxcartsuper_config/show_confirm')) {
			$pimage = Mage::helper('catalog/image')->init($product, 'small_image')->resize(55);
			$ajaxData['product_info'] = Mage::helper('ajaxcartsuper/data')->productHtml($product->getName(), $product->getProductUrl(), $pimage);
		}
        $this->getResponse()->setBody($this->getRequest()->getParam('callback').'('.Mage::helper('core')->jsonEncode($ajaxData).')');
        return;
      }else {
          parent::addAction();
      }
    }
    
    
      /**
     * Override Remove item
     */
    public function removeAction()
    {
        
          if ($this->getRequest()->getParam('callback')) {
            $ajaxData = array();
            $id = (int) $this->getRequest()->getParam('item');
            $item = Mage::getModel('wishlist/item')->load($id);
            if (!$item->getId()) {
                return $this->norouteAction();
            }
            $wishlist = $this->_getWishlist($item->getWishlistId());
            if (!$wishlist) {
                return $this->norouteAction();
            }
            try {
                $item->delete();
                $wishlist->save();
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('customer/session')->addError(
                        $this->__('An error occurred while deleting the item from wishlist: %s', $e->getMessage())
                );
            } catch (Exception $e) {
                Mage::getSingleton('customer/session')->addError(
                        $this->__('An error occurred while deleting the item from wishlist.')
                );
            }

            Mage::helper('wishlist')->calculate();
            $this->loadLayout();
            $sidebarWishlist = "";
            if ($this->getLayout()->getBlock('wishlist_sidebar')) {
                $sidebarWishlist = $this->getLayout()->getBlock('wishlist_sidebar')->toHtml();
            }
            if($this->getLayout()->getBlock('top.links')){
                    $toplink = $this->getLayout()->getBlock('top.links')->toHtml();
            }
            $ajaxData['status'] = 1;
            $ajaxData['wishlist_sidebar'] = $sidebarWishlist;
            $ajaxData['type_sidebar'] = 'wishlist';
            $ajaxData['top_link'] = $toplink;
            $this->getResponse()->setBody($this->getRequest()->getParam('callback').'('.Mage::helper('core')->jsonEncode($ajaxData).')');
            return;
        } else {
            parent::removeAction();
        }
  
    }


}