<?php
/* © 2013 Translations.com, a TransPerfect company 
 * Translations.com, Inc., its affiliates and its licensors (collectively, “Translations.com”) own all right, title and interest, 
 * including but not limited to all intellectual property rights, in and to this software and all associated documentation, updates, 
 * new releases and work product, if any (collectively, the “Software”), in both object code and source code formats.  
 * 
 * By making use of the Software, you agree 
 * (i)	to reproduce the copyright, trademark and other proprietary notices contained on or in the Software as delivered to you (including this Notice)
 *      on any reproductions you cause to be made of the Software and further agree not to remove any such notices (including this Notice) from the Software or any copies thereof; 
 * (ii) the Software shall not be further licensed, sold or otherwise transferred by you, except if otherwise approved in writing by an authorized Translations.com representative; 
 * (iii) not to release the results of any benchmark testing of the Software or use any trademark, logo or proprietary notice of Translations.com (except as required by this Notice or law) without Translations.com’s prior written approval; and 
 * (iv) you shall not modify, change nor create any derivative works of the Software.  
 * Any derivative works of the Software created by you, your employees, agents, or contractors, including any and all modifications or changes to the Software, in any format whatsoever, shall be the exclusive property of Translations.com.
*/
class Transperfect_Globallink_Block_Adminhtml_Glcategory_Grid extends Mage_Adminhtml_Block_Widget_Grid {

  public function __construct() {
    parent::__construct();
    $this->setId('glcategoryGrid');
    $this->setDefaultSort('entity_id');
    $this->setDefaultDir('DESC');
    $this->setSaveParametersInSession(true);
    $this->setVarNameFilter('category_filter');
  }

  public function get_categories() {
    $category = Mage::getModel('catalog/category');
    $tree = $category->getTreeModel();
    $tree->load();

    $ids = $tree->getCollection()->getAllIds();
    $arr = array();
    Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $ids);
    if ($ids) {
      foreach ($ids as $id) {
        $cat = Mage::getModel('catalog/category');
        $cat->load($id);
        array_push($arr, $cat);
      }
    }
    Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $arr);
    return $arr;
  }

  protected function _prepareCollection() {
    $store = $this->_getStore();
    $filter = Mage::getSingleton('core/session')->getCategoryFilter();
    $object_type = Mage::helper("globallink")->_get_object_type_category();
    Mage::getSingleton('core/session')->unsObjectType();
    Mage::getSingleton('core/session')->setObjectType($object_type);
    Mage::app()->setCurrentStore($store);
    $root_category_id = Mage::app()->getStore()->getRootCategoryId();
    $root_categories = Mage::getModel('catalog/category')->load($root_category_id)->getAllChildren();
    $root_category_ids = explode(',', $root_categories);
    $collection = Mage::getModel('catalog/category')->getCollection()
            ->addFieldToFilter('parent_id', array('in' => $root_category_ids))
            ->addAttributeToSort('name', 'ASC');

    if (($key = array_search($root_category_id, $root_category_ids)) !== false) {
      unset($root_category_ids[$key]);
      $root_category_ids = array_values($root_category_ids);
    }

    if (!$filter || $filter == 0) {
      $category_include_ids = array();
      foreach ($root_category_ids as $category_id) {
        $object_type = Mage::helper("globallink")->_get_object_type_category();
        $result = Mage::helper("globallink")->get_gl_object_id_status($store->store_id, $category_id, $object_type);
        if (!$result) {
          $category_include_ids[] = $category_id;
        }
      }

      $collection->addAttributeToFilter('entity_id', array('in' => $category_include_ids));
    }

    $this->setCollection($collection);
    parent::_prepareCollection();
    return $this;
  }

  protected function _getStore() {
    $storeId = $this->getRequest()->getParam('store', '');
    if ($storeId == '') {
      $storeId = Mage::helper("globallink")->get_default_gl_store_id();
    }
    return Mage::app()->getStore($storeId);
  }

  protected function _prepareColumns() {

//    $this->addColumn('entity_id', array(
//        'header_css_class' => 'a-center',
//        'header' => '',
//        'type' => 'checkbox',
//        'width' => '5%',
//        'field_name' => 'gl_category_ids[]',
//        'align' => 'center',
//        'renderer' => new Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox(),
//        'index' => 'entity_id',
//        'filter' => false,
//    ));

    $this->addColumn('name', array(
        'header' => Mage::helper('catalog')->__('Name'),
        'index' => 'name',
        'width' => '75%',
        'type' => 'varchar',
    ));

    $this->addColumn('action', array(
        'header' => Mage::helper('catalog')->__('Active Submission'),
        'width' => '20%',
        'type' => 'action',
        'renderer' => 'transperfect_globallink_block_adminhtml_renderer_action',
        'filter' => false,
        'sortable' => false,
    ));

    return parent::_prepareColumns();
  }

  public function getGridUrl() {
    return $this->getUrl('*/*', array('_current' => true));
  }
  
  public function getRowUrl() {
    return '"';
  }

  protected function _prepareMassaction() {
    $this->setMassactionIdField('entity_id');
    $this->getMassactionBlock()->setFormFieldName('gl_category_ids[]');
    $this->getMassactionBlock()->addItem('add', array(
        'label' => 'Send for Translation',
        'url' => $this->getUrl('*/*/massAdd'),
    ));
    return $this;
  }

}