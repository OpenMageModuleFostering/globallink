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
class Transperfect_Globallink_Block_Adminhtml_Glproduct extends Mage_Adminhtml_Block_Widget_Grid_Container {

  public function __construct() {
    parent::__construct();
    $this->_controller = 'adminhtml_glproduct';
    $this->_blockGroup = 'globallink';
    $this->setTemplate('globallink/adminhtml/glproduct.phtml');
  }

  /**
   * Prepare button and grid
   *
   * @return Mage_Adminhtml_Block_Catalog_Product
   */
  protected function _prepareLayout() {
    $this->setChild('grid', $this->getLayout()->createBlock('globallink/adminhtml_glproduct_grid', 'adminhtml_glproduct.grid')->setSaveParametersInSession(true));
    return parent::_prepareLayout();
  }

  /**
   * Render grid
   *
   * @return string
   */
  public function getGridHtml() {
    return $this->getChildHtml('grid');
  }

  /**
   * Check whether it is single store mode
   *
   * @return bool
   */
  public function isSingleStoreMode() {
    if (!Mage::app()->isSingleStoreMode()) {
      return false;
    }
    return true;
  }

  public function getMappedStores() {
    return Mage::helper("globallink")->get_mapped_locales();
  }

}

?>
