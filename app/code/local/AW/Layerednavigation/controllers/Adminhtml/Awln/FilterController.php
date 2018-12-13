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

class AW_Layerednavigation_Adminhtml_Awln_FilterController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init Filter model from request
     *
     * @param string $requestParamName
     *
     * @return AW_Layerednavigation_Model_Filter
     * @throws Exception
     */
    protected function _initFilter($requestParamName = 'id')
    {
        $filterId = (int)$this->getRequest()->getParam($requestParamName, 0);
        $filter = Mage::getModel('aw_layerednavigation/filter');

        $storeId = Mage::app()->getRequest()->getParam('store', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        $filter->setStoreId($storeId);

        $filter->load($filterId);
        if ($filter->getId() === null) {
            throw new Exception('Filter does not exist');
        }

        if ($data = Mage::getSingleton('adminhtml/session')->getFilterFormData()) {
            $filter->addData($data);
            Mage::getSingleton('adminhtml/session')->setFilterFormData(null);
        }

        Mage::register('current_filter', $filter);
        return $filter;
    }

    public function indexAction()
    {
        $this->_title($this->__('Layered Navigation'))->_title($this->__('Manage Filters'));
        $this->loadLayout();
        $this->_setActiveMenu('catalog/aw_layerednavigation');
        $this->renderLayout();
    }

    public function editAction()
    {
        try {
            $this->_initFilter();
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__($e->getMessage()));
            return $this->_redirect('*/*/index');
        }

        $this->_title($this->__('Layered Navigation'))->_title($this->__('Edit Filter'));
        $this->loadLayout();
        $this->_setActiveMenu('catalog/aw_layerednavigation');
        $this->renderLayout();
        return $this;
    }

    public function categoriesAction()
    {
        try {
            $this->_initFilter();
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__($e->getMessage()));
            return $this->_redirect('*/*/index');
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function categoriesJsonAction()
    {
        try {
            $this->_initFilter();
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__($e->getMessage()));
            return $this->_redirect('*/*/index');
        }
        $categoryBlock = $this->getLayout()->createBlock('aw_layerednavigation/adminhtml_filter_edit_tab_categories');
        $this->getResponse()->setBody(
            $categoryBlock->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    public function saveAction()
    {
        try {
            $filter = $this->_initFilter();
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__($e->getMessage()));
            return $this->_redirect('*/*/index');
        }

        $filterData = $this->getRequest()->getParams();
        $filterAdditionalData = $filter->getData('additional_data');
        $requestAdditionalData = $this->getRequest()->getParam('additional_data', array());
        $filterData['additional_data'] = array_merge($filterAdditionalData, $requestAdditionalData);

        try {
            $filter->addData($filterData)->save();
            if (array_key_exists('option', $filterData) && isset($filterData['option'])) {
                $this->_processFilterOptions($filter, $filterData['option']);
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Unable save filter'));
            Mage::getSingleton('adminhtml/session')->setFilterFormData($this->getRequest()->getParams());
            return $this->_redirect('*/*/edit', array('_current' => true, 'id' => $filter->getId()));
        }

        Mage::getSingleton('adminhtml/session')->addSuccess(
            $this->__('Filter "%s" has been successfully saved', $filter->getTitle())
        );
        if ($this->getRequest()->getParam('back')) {
            return $this->_redirect('*/*/edit', array('_current' => true, 'id' => $filter->getId()));
        }
        return $this->_redirect('*/*/index');
    }

    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);

        $filterCode = $this->getRequest()->getParam('code');
        $filterId = $this->getRequest()->getParam('id', 0);
        $filter = Mage::getModel('aw_layerednavigation/filter')->load($filterCode, 'code');

        if ($filter->getId() && ($filter->getId() != $filterId)) {
            Mage::getSingleton('adminhtml/session')
                ->addError(
                    Mage::helper('aw_layerednavigation')->__(
                        'Code "%s" is already used. Please specify another code.', $filterCode
                    )
                );
            $this->_initLayoutMessages('adminhtml/session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }
        $this->getResponse()->setBody($response->toJson());
    }

    public function massChangeStatusAction()
    {
        $filterIdList = $this->getRequest()->getParam('aw_layerednavigation_filter', array());
        $newStatus = $this->getRequest()->getParam('status', 0);
        if (count($filterIdList) === 0) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('aw_layerednavigation')->__('Please select filter(s).')
            );
        } else {
            try {
                foreach ($filterIdList as $filterId) {
                    $filterModel = Mage::getModel('aw_layerednavigation/filter')->load($filterId);
                    $filterModel->setIsEnabled($newStatus);
                    $filterModel->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('aw_layerednavigation')->__(
                        'Total of %d record(s) were updated.', count($filterIdList)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massChangeStatusInSearchAction()
    {
        $filterIdList = $this->getRequest()->getParam('aw_layerednavigation_filter', array());
        $newStatus = $this->getRequest()->getParam('status', 0);
        if (count($filterIdList) === 0) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('aw_layerednavigation')->__('Please select filter(s).')
            );
        } else {
            try {
                foreach ($filterIdList as $filterId) {
                    $filterModel = Mage::getModel('aw_layerednavigation/filter')->load($filterId);
                    $filterModel->setIsEnabledInSearch($newStatus);
                    $filterModel->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('aw_layerednavigation')->__(
                        'Total of %d record(s) were updated.', count($filterIdList)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function synchronizeAction()
    {
        $result = array(
            'result'  => false,
        );
        if ($this->_isAjax()) {
            try {
                Mage::getModel('aw_layerednavigation/synchronization')->run();
                $result['result'] = true;
            } catch (Exception $e) {
                $result['message'] = $e->getMessage();
            }
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    protected function _processFilterOptions($filter, $optionsData = array())
    {
        foreach ($optionsData as $optionId => $optionData) {

            $optionModel = Mage::getModel('aw_layerednavigation/filter_option')->setStoreId($filter->getStoreId());
            $optionModel->load(intval($optionId));

            if (isset($optionData['delete']) && $optionData['delete']) {
                $optionModel->delete();
            } else {
                if (isset($optionData['image_delete'])) {
                    $optionData['image'] = null;
                }

                $optionModel
                    ->setData('filter_id', $filter->getId())
                    ->addData($optionData)
                    ->save()
                ;

                if ($optionModel->getData('image_delete')) {
                    Mage::helper('aw_layerednavigation/image')->cleanOptionImage($optionId);
                } else {
                    $this->_processImage($optionModel, $optionId);
                }
            }
        }
        return $this;
    }

    protected function _processImage($optionModel, $requestOptionId)
    {
        $failedFiles = array();
        $uploadedFileName = null;
        try {
            $uploadedFileName = Mage::helper('aw_layerednavigation/image')->uploadImage(
                $optionModel->getId(), $requestOptionId
            );
            if (!is_null($uploadedFileName)) {
                $optionModel->setData('image', $uploadedFileName)->save();
            }
        } catch (Exception $e) {
            if ($e->getCode() == AW_Layerednavigation_Helper_Image::EXCEPTION_CODE_UNSUPPORTED_IMAGE_TYPE) {
                $failedFiles[] = $e->getMessage();
            } else {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        return $this;
    }

    protected function _isAjax()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return true;
        }
        if ($this->getRequest()->getParam('ajax') || $this->getRequest()->getParam('isAjax')) {
            return true;
        }
        return false;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/aw_layerednavigation/manage_filters');
    }
}