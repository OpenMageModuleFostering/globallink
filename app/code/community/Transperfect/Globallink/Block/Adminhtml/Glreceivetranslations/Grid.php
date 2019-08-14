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
class Transperfect_Globallink_Block_Adminhtml_Glreceivetranslations_Grid extends Mage_Adminhtml_Block_Widget_Grid {

  public function __construct() {
    parent::__construct();
    $this->setId('glreceivetranslationsGrid');
    $this->setDefaultSort('globallink_job_id');
    $this->setDefaultDir('DESC');
    $this->setSaveParametersInSession(false);
    $this->setVarNameFilter('glreceivetranslations_filter');
  }

  protected function _prepareCollection() {
    $gl_arr = $this->_getReadyTargets();
    $id_arr = array();
    foreach ($gl_arr as $glObj) {
      $id_arr[] = $glObj->glRowId;
    }
    $collection = Mage::getModel('globallink/glstatus')->getCollection();
    $collection->addFieldToFilter('globallink_job_id', array('in' => $id_arr));
    $this->setCollection($collection);
    parent::_prepareCollection();
    return $this;
  }

  public function _getReadyTargets() {
    $gl_arr = Mage::registry('gl_target_arr');
    Mage::helper("globallink")->gl_debug('in grid _getReadyTargets ', $gl_arr);
    return $gl_arr;
  }

  protected function _prepareColumns() {

    $this->addColumn('globallink_job_id', array(
        'header_css_class' => 'a-center',
        'header' => '',
        'type' => 'checkbox',
        'width' => '5%',
        'field_name' => 'gl_status_ids[]',
        'align' => 'center',
        'renderer' => new Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox(),
        'index' => 'globallink_job_id',
        'filter' => false,
    ));

    $this->addColumn('submission_name', array(
        'header' => Mage::helper('catalog')->__('Submission Name'),
        'index' => 'submission_name',
        'width' => '18%',
        'type' => 'options',
        'options' => Mage::helper("globallink")->getSubmissionOptions(),
    ));
    
    $this->addColumn('object_type', array(
        'header' => Mage::helper('catalog')->__('Type'),
        'index' => 'object_type',
        'width' => '10%',
        'type' => 'options',
        'options' => Mage::helper("globallink")->getObjectTypeOptions(),
    ));

    $this->addColumn('object_name', array(
        'header' => Mage::helper('catalog')->__('Name'),
        'index' => 'object_name',
        'width' => '20%',
        'type' => 'varchar',
    ));
    
    $this->addColumn('submitter', array(
        'header' => Mage::helper('catalog')->__('Submitter'),
        'index' => 'submitter',
        'width' => '9%',
        'type' => 'options',
        'options' => Mage::helper("globallink")->getSubmitters(),
    ));

    $this->addColumn('gl_source_locale', array(
        'header' => Mage::helper('catalog')->__('Source Language'),
        'index' => 'gl_source_locale',
        'width' => '14%',
        'type' => 'options',
        'options' => Mage::helper("globallink")->get_gl_locale_options(),
    ));

    $this->addColumn('gl_target_locale', array(
        'header' => Mage::helper('catalog')->__('Target Language'),
        'index' => 'gl_target_locale',
        'width' => '14%',
        'type' => 'options',
        'options' => Mage::helper("globallink")->get_gl_locale_options(),
    ));
    
    $this->addColumn('status', array(
        'header' => Mage::helper('catalog')->__('Status'),
        'index' => 'status',
        'width' => '10%',
        'type' => 'options',
        'options' => Mage::helper("globallink")->get_gl_status_options(),
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