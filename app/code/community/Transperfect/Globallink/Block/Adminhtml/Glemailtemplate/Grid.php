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
class Transperfect_Globallink_Block_Adminhtml_Glemailtemplate_Grid extends Mage_Adminhtml_Block_Widget_Grid {

  public function __construct() {
    parent::__construct();
    $this->setId('glemailtemplateGrid');
    $this->setDefaultSort('template_id');
    $this->setDefaultDir('DESC');
    $this->setSaveParametersInSession(true);
    $this->setVarNameFilter('product_filter');
  }

  protected function _prepareCollection() {
    $collection = Mage::getResourceSingleton('core/email_template_collection');
    $this->setCollection($collection);
    return parent::_prepareCollection();
  }

  protected function _prepareColumns() {

//    $this->addColumn('template_id', array(
//        'header_css_class' => 'a-center',
//        'header' => '',
//        'type' => 'checkbox',
//        'width' => '5%',
//        'field_name' => 'gl_template_ids[]',
//        'align' => 'center',
//        'renderer' => new Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox(),
//        'index' => 'template_id',
//        'filter' => false,
//    ));

    $this->addColumn('code', array(
        'header' => Mage::helper('adminhtml')->__('Template Name'),
        'index' => 'template_code',
        'width' => '15%',
    ));

    $this->addColumn('added_at', array(
        'header' => Mage::helper('adminhtml')->__('Date Added'),
        'index' => 'added_at',
        'gmtoffset' => true,
        'type' => 'datetime',
        'width' => '20%',
    ));

    $this->addColumn('modified_at', array(
        'header' => Mage::helper('adminhtml')->__('Date Updated'),
        'index' => 'modified_at',
        'gmtoffset' => true,
        'type' => 'datetime',
        'width' => '20%',
    ));

    $this->addColumn('subject', array(
        'header' => Mage::helper('adminhtml')->__('Subject'),
        'index' => 'template_subject',
        'width' => '20%',
    ));

    $this->addColumn('type', array(
        'header' => Mage::helper('adminhtml')->__('Template Type'),
        'index' => 'template_type',
        'filter' => 'adminhtml/system_email_template_grid_filter_type',
        'renderer' => 'adminhtml/system_email_template_grid_renderer_type',
        'width' => '20%',
    ));

    return $this;
  }

  public function getGridUrl() {
    return $this->getUrl('*/*', array('_current' => true));
  }
  
  public function getRowUrl() {
    return '"';
  }

  protected function _prepareMassaction() {
    $this->setMassactionIdField('entity_id');
    $this->getMassactionBlock()->setFormFieldName('gl_template_ids[]');
    $this->getMassactionBlock()->addItem('add', array(
        'label' => 'Send for Translation',
        'url' => $this->getUrl('*/*/massAdd'),
    ));
    return $this;
  }

}