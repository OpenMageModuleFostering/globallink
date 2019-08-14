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
class Transperfect_Globallink_Block_Adminhtml_Glcmsblock_Grid extends Mage_Adminhtml_Block_Widget_Grid {

  public function __construct() {
    parent::__construct();
    $this->setId('glcmsblockGrid');
    $this->setDefaultSort('entity_id');
    $this->setDefaultDir('DESC');
    $this->setSaveParametersInSession(true);
    $this->setVarNameFilter('product_filter');
  }

  protected function _prepareCollection() {
    $store = $this->_getStore();
    $object_type = Mage::helper("globallink")->_get_object_type_block();
    Mage::getSingleton('core/session')->unsObjectType();
    Mage::getSingleton('core/session')->setObjectType($object_type);
    $collection = Mage::getModel('cms/block')->getCollection()->addStoreFilter($store->getId());
    $this->setCollection($collection);
    return parent::_prepareCollection();
  }

  protected function _getStore() {
    $storeId = $this->getRequest()->getParam('store', '');
    if ($storeId == '') {
      $storeId = Mage::helper("globallink")->get_default_gl_store_id();
    }
    return Mage::app()->getStore($storeId);
  }

  protected function _prepareColumns() {

//    $this->addColumn('block_id', array(
//        'header_css_class' => 'a-center',
//        'header' => '',
//        'type' => 'checkbox',
//        'width' => '5%',
//        'field_name' => 'gl_block_ids[]',
//        'align' => 'center',
//        'renderer' => new Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox(),
//        'index' => 'block_id',
//        'filter' => false,
//    ));

    $this->addColumn('title', array(
        'header' => Mage::helper('cms')->__('Title'),
        'align' => 'left',
        'index' => 'title',
        'width' => '25%',
    ));

    $this->addColumn('identifier', array(
        'header' => Mage::helper('cms')->__('Identifier'),
        'align' => 'left',
        'index' => 'identifier',
        'width' => '20%',
    ));
    
    $this->addColumn('is_active', array(
        'header' => Mage::helper('cms')->__('Status'),
        'index' => 'is_active',
        'type' => 'options',
        'options'   => array(
                0 => Mage::helper('cms')->__('Disabled'),
                1 => Mage::helper('cms')->__('Enabled')
            ),
        'width' => '5%',
    ));

    $this->addColumn('creation_time', array(
        'header' => Mage::helper('cms')->__('Date Created'),
        'index' => 'creation_time',
        'type' => 'datetime',
        'width' => '15%',
    ));

    $this->addColumn('update_time', array(
        'header' => Mage::helper('cms')->__('Last Modified'),
        'index' => 'update_time',
        'type' => 'datetime',
        'width' => '15%',
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
    $this->getMassactionBlock()->setFormFieldName('gl_block_ids[]');
    $this->getMassactionBlock()->addItem('add', array(
        'label' => 'Send for Translation',
        'url' => $this->getUrl('*/*/massAdd'),
    ));
    return $this;
  }

}