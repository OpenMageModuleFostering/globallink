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

class Transperfect_Globallink_Adminhtml_AttributesetsController extends Mage_Adminhtml_Controller_Action {

  public function indexAction() {
    $this->loadLayout();
    $this->_addContent($this->getLayout()->createBlock('adminhtml/template')->setTemplate('globallink/adminhtml/attributesets.phtml'));
    $this->_setActiveMenu('globalLinkHeader/globallink_sendfortranslation');
    $this->renderLayout();
  }

  public function loadAction() {
    $params = $this->getRequest()->getParams();
    $attribute_set_dropdown = $params['attribute-set-dropdown'];
    $this->_redirect('*/*', array('selected_attributeset_value' => $attribute_set_dropdown));
  }

  public function postAction() {
    $success = TRUE;
    try {
      $post = $this->getRequest()->getPost();
      $attribute_set_id = $post['attribute_set_id'];
      $attributes = Mage::getModel('catalog/product_attribute_api')->items($attribute_set_id);
      $attr_arr = array();
      foreach ($attributes as $attr) {
        $attribute = Mage::getModel('eav/entity_attribute')->load($attr['attribute_id']);
        if ($attribute->getIsVisible() && !$attribute->getIsGlobal()) {
          if ($attribute->getFrontendInput() == 'text' || $attribute->getFrontendInput() == 'textarea') {
            $attr_arr[$attr['attribute_id']] = array('attribute_set_id' => $attribute_set_id, 'attribute_id' => $attr['attribute_id'],
                'attribute_code' => $attribute->getAttributeCode(), 'attribute_type' => $attribute->getFrontendInput(), 'attribute_name' => $attribute->getFrontendLabel());
          }
        }
      }
      $attribute_ids = $post['attribute_ids'];
      $insert_arr = array();
      foreach ($attribute_ids as $attr_id) {
        // Select All Checkbox is set to on if checked
        if ($attr_id == 'on') {
          continue;
        }
        $insert_arr[$attr_id]['attribute'] = $attr_arr[$attr_id];
        $insert_arr[$attr_id]['translatable'] = 1;
      }
      foreach ($attr_arr as $_att_id => $_attribute) {
        if (!isset($insert_arr[$_att_id]['attribute'])) {
          $insert_arr[$_att_id]['attribute'] = $_attribute;
          $insert_arr[$_att_id]['translatable'] = 0;
        }
      }

      Mage::helper("globallink")->delete_and_update_attribute_set_config($attribute_set_id, $insert_arr);
    }
    catch (Exception $ex) {
      $success = FALSE;
      Mage::helper("globallink")->gl_debug($ex->getFile() . ' - Line ' . $ex->getLine() . ': Globallink Generic Error = ', $ex->getMessage());
      Mage::getSingleton('adminhtml/session')->addError('Globallink Generic Error - ' . $ex->getMessage());
    }

    if ($success) {
      Mage::helper("globallink")->gl_debug("AdaptorController-postAction", "Successfully sent to Global Link Server");
      $message = $this->__('Attribute settings saved successfully.');
      Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }

    $this->_redirect('*/*', array('_query' => array('selected_attributeset_value' => $attribute_set_id)));
  }

}