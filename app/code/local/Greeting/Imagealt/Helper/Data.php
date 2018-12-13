<?php
class Greeting_Imagealt_Helper_Data extends Mage_Core_Helper_Abstract
{
   public function getImg($product, $w, $h, $imgVersion='image', $file=NULL)
    {
        $url = '';
        if ($h <= 0)
        {
            $url = Mage::helper('catalog/image')
                ->init($product, $imgVersion, $file)
                ->constrainOnly(true)
                ->keepAspectRatio(true)
                ->keepFrame(false)
                //->setQuality(90)
                ->resize($w);
        }
        else
        {
            $url = Mage::helper('catalog/image')
                ->init($product, $imgVersion, $file)
                ->resize($w, $h);
        }
        return $url;
    }
    function altImage($product, $val, $w, $h, $imgVersion='small_image'){
        $product->load('media_gallery');
        $column = 'position';
        $value = $val;
        $gal = $product->getMediaGalleryImages();
        if ($gal = $product->getMediaGalleryImages())
        {
            if ($altImg = $gal->getItemByColumnValue($column, $value))
            {
                return
                '<img class="alt-img lazyOwl" src="' . $this->getImg($product, $w, $h, $imgVersion, $altImg->getFile()) . '" alt="' . $product->getName() . '" />';
                
            }
        }
        return '';
    }
}
?>