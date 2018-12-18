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
	
	public function catAction(){
		header('Content-type: application/json');
		header('Content-Type: text/html; charset=UTF-8');		
		$cat_id = $_POST['catId'];
		$products = Mage::getModel('catalog/category')->load($idLoad)
			->getProductCollection()
			->addAttributeToSelect('*')
			->addAttributeToFilter('status', 1)
			->addAttributeToFilter('visibility', 4);
		$productBlock = $this->getLayout()->createBlock('catalog/product_price');
		$customerId = null;
		$wishlist = null;
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			$customerId = $customer->getId();
		}
		if (isset($customerId) && $customerId) {
			$wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId, true);
		}		
		$html = '';
		$html .= '<div class="owl-carousel owl-theme" id="carousel-'.$cat_id.'"">';
		foreach ($products as $_product){
			$hasProduct = false;
			if (isset($customerId) && isset($wishlist) && $customerId) {
				$collection = Mage::getModel('wishlist/item')->getCollection()
					->addFieldToFilter('wishlist_id', $wishlist->getId())
					->addFieldToFilter('product_id', $_product->getId());
					$hasProduct = ($collection->count() ? true : false);
			}
			$_wishlistItem = Mage::getModel('wishlist/item')->loadByProductWishlist(
				Mage::helper('wishlist')->getWishlist()->getId(), $_product->getId(), $_product->getStoreId()
			);			
			$html .= '<div class="item">' ;
				$html .= '<div class="product-image-basket">';
					$html .= '<a href="'.$_product->getProductUrl().'" title="'.$_product->getName().'" class="product-image">';
						$html .= '<img src="'.Mage::helper('catalog/image')->init($_product, 'small_image')->resize(336,259).'" alt="'.$_product->getName().'" />';
					$html .= '</a>';
					$html .= '<div class="cart-block">';
					$html .= '<span class="hidden wishlistSubmitUrl">'.Mage::helper('wishlist')->getAddUrl($_product).'</span>';
					$html .= '<span class="hidden productId">'.$_product->getId().'</span>';
						if($_product->isSaleable())
							$html .= '<a href="javascript:void(0);" data-href="'.Mage::getUrl('ajax/product/view/id/' . $_product->getId()).'" title="'.$_product->getName().'" class="ajax quick-view">'.$this->__('Add to Cart').' >></a>';
					$html .= '<ul class="add-to-links">';
						if ($hasProduct)
							$html .= '<li id="item-'.$_product->getId().'" class="in-wishlist"><a href="'.Mage::helper('wishlist')->getRemoveUrl($_wishlistItem).'" title="'.$this->__('WHISHLIST').'" class="link-wishlist wishItem-'.$_product->getId().'">&nbsp;</a></li>';
						else
							$html .= '<li id="item-'.$_product->getId().'"><a href="'.Mage::helper('wishlist')->getAddUrl($_product).'" title="'.$this->__('WHISHLIST').'" class="link-wishlist wishItem-'.$_product->getId().'">&nbsp;</a></li>';
					$html .= '</ul>';
					$html .= '</div>';
				$html .= '</div>';
				$html .= '<div class="name-price">';
					$html .= '<p class="product-name"><a href="'.$_product->getProductUrl().'" title="'.$_product->getName().'">'.$_product->getName().'</a></p>';
					$html .= '<div>'.$productBlock->getPriceHtml($_product, true).'</div>';
				$html .= '</div>';
			$html .= '</div>';
		}
		$html .= '</div>';		
        $output=array();
        $output['html']=$html;
        echo json_encode($html);
		exit;
	}
	
}