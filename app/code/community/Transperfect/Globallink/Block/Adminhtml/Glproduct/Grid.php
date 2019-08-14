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
class Transperfect_Globallink_Block_Adminhtml_Glproduct_Grid extends Mage_Adminhtml_Block_Widget_Grid {

  public function __construct() {
    parent::__construct();
    $this->setId('glproductGrid');
    $this->setDefaultSort('entity_id');
    $this->setDefaultDir('DESC');
    $this->setSaveParametersInSession(true);
    $this->setVarNameFilter('product_filter');
  }

  protected function _prepareCollection() {
    $store = $this->_getStore();
    $object_type = Mage::helper("globallink")->_get_object_type_product();
    Mage::getSingleton('core/session')->unsObjectType();
    Mage::getSingleton('core/session')->setObjectType($object_type);
    $filter = Mage::getSingleton('core/session')->getProductFilter();
    $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id');
    $object_id_collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('object_id');

    if (!$filter || $filter == 0) {
      $object_id_arr = array();
      foreach ($object_id_collection as $object_id) {
        $object_type = Mage::helper("globallink")->_get_object_type_product();
        $result = Mage::helper("globallink")->get_gl_object_id_status($store->store_id, $object_id['entity_id'], $object_type);
        if (!$result) {
          $object_id_arr[] = $object_id['entity_id'];
        }
      }

      $collection->addFieldToFilter('entity_id', array('in' => $object_id_arr));
    }

    if ($store->getId()) {
      $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
      $collection->addStoreFilter($store);
      $collection->joinAttribute(
              'name', 'catalog_product/name', 'entity_id', null, 'inner', $adminStore
      );
      $collection->joinAttribute(
              'custom_name', 'catalog_product/name', 'entity_id', null, 'inner', $store->getId()
      );
      $collection->joinAttribute(
              'status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId()
      );
      $collection->joinAttribute(
              'visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId()
      );
    }
    else {
      $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
      $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
    }

    $this->setCollection($collection);

    parent::_prepareCollection();
    $this->getCollection()->addWebsiteNamesToResult();
    return $this;
  }

  protected function _addColumnFilterToCollection($column) {
    if ($this->getCollection()) {
      if ($column->getId() == 'websites') {
        $this->getCollection()->joinField('websites', 'catalog/product_website', 'website_id', 'product_id=entity_id', null, 'left');
      }
    }
    return parent::_addColumnFilterToCollection($column);
  }

  protected function _getStore() {
    $storeId = $this->getRequest()->getParam('store', '');
    if ($storeId == '') {
      $storeId = Mage::helper("globallink")->get_default_gl_store_id();
    }
    return Mage::app()->getStore($storeId);
  }

  protected function _prepareColumns() {

//    $this->addColumn('product_id', array(
//        'header_css_class' => 'a-center',
//        'header' => '',
//        'type' => 'checkbox',
//        'width' => '5%',
//        'field_name' => 'gl_product_ids[]',
//        'align' => 'center',
//        'renderer' => new Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox(),
//        'index' => 'entity_id',
//        'filter' => false,
//    ));

    $this->addColumn('entity_id', array(
        'header' => Mage::helper('catalog')->__('ID'),
        'width' => '5%',
        'type' => 'number',
        'index' => 'entity_id',
    ));

    $store = $this->_getStore();
    if ($store->getId()) {
      $this->addColumn('custom_name', array(
          'header' => Mage::helper('catalog')->__('Name'),
          'index' => 'custom_name',
          'width' => '17%',
      ));
    }

    $this->addColumn('type', array(
        'header' => Mage::helper('catalog')->__('Type'),
        'width' => '10%',
        'index' => 'type_id',
        'type' => 'options',
        'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
    ));

    $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

    $this->addColumn('set_name', array(
        'header' => Mage::helper('catalog')->__('Attrib. Set Name'),
        'width' => '8%',
        'index' => 'attribute_set_id',
        'type' => 'options',
        'options' => $sets,
    ));

    $this->addColumn('sku', array(
        'header' => Mage::helper('catalog')->__('SKU'),
        'width' => '5%',
        'index' => 'sku',
    ));

    $this->addColumn('visibility', array(
        'header' => Mage::helper('catalog')->__('Visibility'),
        'width' => '10%',
        'index' => 'visibility',
        'type' => 'options',
        'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
    ));

    $this->addColumn('status', array(
        'header' => Mage::helper('catalog')->__('Status'),
        'width' => '5%',
        'index' => 'status',
        'type' => 'options',
        'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
    ));

    if (!Mage::app()->isSingleStoreMode()) {
      $this->addColumn('websites', array(
          'header' => Mage::helper('catalog')->__('Websites'),
          'width' => '10%',
          'sortable' => false,
          'index' => 'websites',
          'type' => 'options',
          'options' => Mage::getModel('core/website')->getCollection()->toOptionHash(),
      ));
    }

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

  protected function _prepareMassaction() {
    $this->setMassactionIdField('entity_id');
    $this->getMassactionBlock()->setFormFieldName('gl_product_ids[]');
    $this->getMassactionBlock()->addItem('add', array(
        'label' => 'Send for Translation',
        'url' => $this->getUrl('*/*/massAdd'),
    ));
    return $this;
  }

  public function getRowUrl() {
    return '"';
  }

}