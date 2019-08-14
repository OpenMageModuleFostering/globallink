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


require_once 'gl_ws/GlobalLink.php';
require_once 'gl_ws/gl_ws_send_translations.php';

class Transperfect_Globallink_Adminhtml_GlcategoryController extends Mage_Adminhtml_Controller_Action {

  public function indexAction() {
    $this->loadLayout();
    $this->_setActiveMenu('globalLinkHeader/globallink_sendfortranslation');
    $this->renderLayout();
  }
  
  public function filterAction() {
    $post = $this->getRequest()->getPost();
    $radio = $post['globallink_category_filter'];
    Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $post);
    Mage::getSingleton('core/session')->unsCategoryFilter();
    Mage::getSingleton('core/session')->setCategoryFilter($radio);
    $store = $post['store_id'];
    if ($store != '') {
      $this->_redirect('globallink/adminhtml_glcategory', array('_query' => array('store' => $store)));
      return $this;
    }
    else {
      $this->_redirect('*/*');
      return $this;
    }
  }

  public function massAddAction() {
    $object_type = Mage::helper("globallink")->_get_object_type_category();
    Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", "Starting Object Upload.");
    $post = $this->getRequest()->getPost();
    Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "]", $post);

    $selected_category_ids = $post['gl_category_ids'];
    $selected_store_id = $post['store'];
    if (count($selected_category_ids) > 0) {
      Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Category Id Selected]", $selected_category_ids[0]);
      Mage::getSingleton('adminhtml/session')->setData('gl_selected_ids', $selected_category_ids[0]);
      $this->_redirect('globallink/adminhtml_glcreatesubmission', array('_query' => array('gl_object_type' => $object_type, 'store' => $selected_store_id)));
      return $this;
    }
    else {
      Mage::getSingleton('adminhtml/session')->addError('Please select a record.');
      $this->_redirect('*/*');
      return $this;
    }
  }

}
