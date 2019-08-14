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

class Transperfect_Globallink_Block_Adminhtml_Glfield_Grid extends Mage_Adminhtml_Block_Widget_Grid {

  public function __construct() {
    parent::__construct();
    $this->setId('glfieldGrid');
    $this->setDefaultSort('globallink_field_id');
    $this->setDefaultDir('ASC');
    $this->setSaveParametersInSession(false);
    $this->setVarNameFilter('glfield_filter');
  }

  protected function _prepareCollection() {
    $object_type = $this->_getObjectType();
    $collection = Mage::getModel('globallink/glfield')->getCollection();
    $collection->addFieldToFilter('object_type', $object_type);
    $this->setCollection($collection);
    parent::_prepareCollection();
    return $this;
  }

  protected function _getObjectType() {
    $objectType = $this->getRequest()->getParam('selected_object_type', '');
    $new_objectType = str_replace("-", "/", $objectType);
    return $new_objectType;
  }

  protected function _prepareColumns() {

    $this->addColumn('globallink_field_id', array(
        'header_css_class' => 'a-center',
        'header' => '<input type="checkbox" id="selectAllCheckBox" onclick="javascript:selectAll(this);" >&nbsp;&nbsp;&nbsp;&nbsp;Translate?&nbsp;',
        'type' => 'hidden',
        'width' => '5%',
        'field_name' => 'gl_field_ids[]',
        'align' => 'center',
        'renderer' => new Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox(),
        'index' => 'globallink_field_id',
        'filter' => false,
        'sortable'  => false,
        'values' => Mage::helper("globallink")->getIncludeIds($this->_getObjectType()),
    ));

    $this->addColumn('field_code', array(
        'header' => Mage::helper('catalog')->__('Attribute Code'),
        'index' => 'field_code',
        'width' => '40%',
        'type' => 'varchar',
    ));

    $this->addColumn('field_name', array(
        'header' => Mage::helper('catalog')->__('Attribute Label'),
        'index' => 'field_name',
        'width' => '40%',
        'type' => 'varchar',
    ));

    $this->addColumn('action', array(
        'header_css_class' => 'a-center',
        'header' => Mage::helper('catalog')->__('Action'),
        'width' => '8%',
        'align' => 'center',
        'type' => 'action',
        'renderer' => 'transperfect_globallink_block_adminhtml_renderer_delete',
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

}