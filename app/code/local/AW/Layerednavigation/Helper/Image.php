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

class AW_Layerednavigation_Helper_Image extends Mage_Core_Helper_Data
{
    const MEDIA_DIR_NAME = 'aw_layerednavigation';
    const CACHE_DIR_NAME = 'cache';

    const PLACEHOLDER_IMAGE_NAME = 'placeholder.png';

    const EXCEPTION_CODE_UNSUPPORTED_IMAGE_TYPE = 1;

    /**
     * Resize image and return resize image url
     *
     * @param int $optionId
     * @param string $imageName
     * @param int $width
     * @param int $height
     * @return string
     */
    public function resizeImage($optionId, $imageName, $width = 100, $height = null)
    {
        $cachedImagePath = $this->getCachedImagePath($optionId, $imageName, $width, $height);
        if ($this->isCached($cachedImagePath)) {
           // return $this->getCachedImageUrl($optionId, $imageName, $width, $height);
        }

        $originalImagePath = $this->getImagePath($optionId, $imageName);

        // If media/aw_layerednavigation not writable return original full size images
        if (!is_writable($this->getBaseFolderPath())) {
            if (file_exists($originalImagePath) && is_file($originalImagePath)) {
                return $this->getImageUrl($optionId, $imageName);
            }
            return $this->getPlaceholderUrl();
        }

        if (!file_exists($originalImagePath) || !is_file($originalImagePath)) {
            $originalImagePath = $this->getPlaceholderPath();
        }

        if (is_null($width) && is_null($height)) {
            list($width, $height) = getimagesize($originalImagePath);
        }

        $transparent = false;
        if (pathinfo($originalImagePath, PATHINFO_EXTENSION) == IMAGETYPE_PNG) {
            $transparent = true;
        }
        try {
            $imageObj = new Varien_Image($originalImagePath);
            $imageObj->constrainOnly(true);
            $imageObj->keepAspectRatio(true);
            $imageObj->keepFrame(true);
            $imageObj->keepTransparency($transparent);
            $imageObj->backgroundColor(array(255, 255, 255));
            $imageObj->resize($width, $height);
            $imageObj->save($cachedImagePath);
        } catch (Exception $e) {
            Mage::logException($e);
            return $this->getPlaceholderUrl();
        }

        return $this->getCachedImageUrl($optionId, $imageName, $width, $height);
    }

    /**
     * Upload file, and return uploaded file name
     *
     * @param string $optionId
     * @param string $requestOptionId
     *
     * @return null|string
     * @throws Exception
     */
    public function uploadImage($optionId, $requestOptionId)
    {
        $fileCode = 'option_' . $requestOptionId . '_image';
        $isFileUploaded = false;
        if (array_key_exists($fileCode, $_FILES) && array_key_exists('tmp_name', $_FILES[$fileCode])) {
            $isFileUploaded = !!$_FILES[$fileCode]['tmp_name'];
        }
        if (!$isFileUploaded) {
            return null;
        }

        if (!$this->isAllowedFileExtensions($_FILES[$fileCode]['type'])) {
            throw new Exception($_FILES[$fileCode]['name'], self::EXCEPTION_CODE_UNSUPPORTED_IMAGE_TYPE);
        }

        Mage::helper('aw_layerednavigation/image')->cleanOptionImage($optionId);

        $uploader = new Varien_File_Uploader($fileCode);
        $uploader->setAllowCreateFolders(true);
        $uploader->setAllowRenameFiles(true);

        // Set media as the upload dir
        $imagePath = $this->getImageFolderPath($optionId);

        // Upload the image
        $uploader->save($imagePath);
        $uploadedFileName = $uploader->getUploadedFileName();

        return $uploadedFileName;
    }

    /**
     * Check is file type allowed for upload
     * @param $fileType
     *
     * @return bool
     */
    public function isAllowedFileExtensions($fileType)
    {
        return array_key_exists($fileType, $this->getAllowedFileExtensions());
    }

    /**
     * Get allowed file extensions
     * @return array
     */
    public function getAllowedFileExtensions()
    {
        return array(
            'image/bmp'  => 'bmp',
            'image/gif'  => 'gif',
            'image/jpeg' => 'jpeg',
            'image/png'  => 'png',
        );
    }

    /**
     * Retrieve full cached image path by $typeId, $imageName and sizes
     *
     * @param int $optionId
     * @param string $imageName
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getCachedImagePath($optionId, $imageName, $width, $height)
    {
        if (is_null($imageName) || !$imageName) {
            $imageName = self::PLACEHOLDER_IMAGE_NAME;
        }
        return $cachedImagePath = $this->getCacheFolderPath() .
            $optionId . DS .
            $width . 'x' . (!is_null($height) ? $height : '') . DS .
            $imageName
        ;
    }

    /**
     * Get URL to cached image
     *
     * @param int $optionId
     * @param string $imageName
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getCachedImageUrl($optionId, $imageName, $width, $height)
    {
        if (is_null($imageName) || !$imageName) {
            $imageName = self::PLACEHOLDER_IMAGE_NAME;
        }
        return $this->getCacheFolderUrl() .
            $optionId . '/'.
            $width . 'x' . (!is_null($height) ? $height : '') . '/' .
            $imageName
        ;
    }

    /**
     * Check is cached image by $cachedImagePath exists
     *
     * @param string $cachedImagePath
     * @return bool
     */
    public function isCached($cachedImagePath)
    {
        if (file_exists($cachedImagePath) && is_file($cachedImagePath)) {
            return true;
        }
        return false;
    }

    /**
     * Retrieve folder path for option by $optionId
     *
     * @param int $optionId
     * @return string
     */
    public function getImageFolderPath($optionId)
    {
        return $this->getBaseFolderPath() . $optionId . DS;
    }

    /**
     * Retrieve full image path by $optionId and $image name
     *
     * @param int $optionId
     * @param string $imageName
     * @return string
     */
    public function getImagePath($optionId, $imageName)
    {
        return $this->getImageFolderPath($optionId) . $imageName;
    }

    /**
     * Retrieve Base Url to image folder
     *
     * @return string
     */
    public function getBaseFolderUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . self::MEDIA_DIR_NAME . '/';
    }

    /**
     * Retrieve Url for option folder by $optionId
     *
     * @param int $optionId
     * @return string
     */
    public function getImageFolderUrl($optionId)
    {
        return $this->getBaseFolderUrl() . $optionId . '/';
    }

    /**
     * Retrieve full image url by $optionId and $image name
     *
     * @param int $optionId
     * @param string $imageName
     * @return string
     */
    public function getImageUrl($optionId, $imageName)
    {
        return $this->getImageFolderUrl($optionId) . $imageName;
    }

    /**
     * Retrieve full placeholder image url
     *
     * @return string
     */
    public function getPlaceholderUrl()
    {
        return $this->getBaseFolderUrl() . self::PLACEHOLDER_IMAGE_NAME;
    }

    /**
     * Retrieve placeholder image path
     * @return string
     */
    public function getPlaceholderPath()
    {
        return $this->getBaseFolderPath() . self::PLACEHOLDER_IMAGE_NAME;
    }

    /**
     * Retrieve Path to base AW_Layerednavigation folder
     *
     * @return string
     */
    public function getBaseFolderPath()
    {
        return Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . self::MEDIA_DIR_NAME . DS;
    }

    /**
     * Retrieve Path to cache folder
     *
     * @return string
     */
    public function getCacheFolderPath()
    {
        return $this->getBaseFolderPath() . self::CACHE_DIR_NAME . DS;
    }

    /**
     * Retrieve Base Url to cache folder
     *
     * @return string
     */
    public function getCacheFolderUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)
            . self::MEDIA_DIR_NAME . '/' . self::CACHE_DIR_NAME . '/'
        ;
    }

    /**
     * Clear image folder by option id
     * @param int $optionId
     *
     * @return bool
     */
    public function cleanOptionImage($optionId)
    {
        return $this->removeDir($this->getImageFolderPath($optionId));
    }

    /**
     * Clean Image Cache
     *
     * @return bool
     */
    public function cleanImageCache()
    {
        $cacheImageDir = $this->getCacheFolderPath();
        return $this->removeDir($cacheImageDir);
    }

    /**
     * Recursively remove folders with contains files
     *
     * @param string $path
     * @return bool
     */
    static public function removeDir($path)
    {
        // TODO: check is_exists, is_writable, is_dir and remove @
        // TODO: Process unlink result and get_last_error for throws Exception
        // TODO: Check on empty dir before remove
        if (is_file($path)) {
            @unlink($path);
        } else {
            array_map(array('AW_Layerednavigation_Helper_Image', 'removeDir'), glob($path . '/*'));
        }
        return @rmdir($path);
    }
}