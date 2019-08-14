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

class Transperfect_Globallink_Block_Adminhtml_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {

  public function render(Varien_Object $row) {
    $object_type = Mage::getSingleton('core/session')->getObjectType();
    
    if ($object_type == Mage::helper("globallink")->_get_object_type_block()) {
      $object_id = $row['block_id'];
    }
    elseif ($object_type == Mage::helper("globallink")->_get_object_type_page()) {
      $object_id = $row['page_id'];
    }
    elseif ($object_type == Mage::helper("globallink")->_get_object_type_product_attributes()) {
      $object_id = $row['attribute_id'];
    }
    else {
      $object_id = $row['entity_id'];
    }
    
    $what = "object_id, submission_name, submission_id";
    $where = "WHERE object_id IN ('" . $object_id . "') AND object_type = '" . $object_type . "' AND status IN ('"
            . Mage::helper("globallink")->_get_submission_status_sent() . "','" . Mage::helper("globallink")->_get_submission_status_ready() . "','"
            . Mage::helper("globallink")->_get_submission_status_error() . "')";
    $status_objects = Mage::helper("globallink")->get_status_objects($what, $where, FALSE);
    $submission_arr = array();
    $submission_link_arr = array();
    foreach ($status_objects as $submission) {
      $object_id = $submission['object_id'];
      $submission_name = $submission['submission_name'];
      $submission_id = $submission['submission_id'];

      if (!array_key_exists($submission_name, $submission_arr)) {
        $what = "gl_target_locale";
        $where = "WHERE object_id = '" . $object_id . "' AND submission_id = '" . $submission_id . "' AND status IN ('"
                . Mage::helper("globallink")->_get_submission_status_sent() . "','" . Mage::helper("globallink")->_get_submission_status_ready() . "','"
                . Mage::helper("globallink")->_get_submission_status_error() . "')";
        $target_locales = Mage::helper("globallink")->get_status_objects($what, $where, FALSE);
        foreach ($target_locales as $target) {
          $gl_locale_desc = Mage::helper("globallink")->get_gl_locale_desc($target['gl_target_locale']);

          if (array_key_exists($submission_name, $submission_arr)) {
            $submission_arr[$submission_name] .= '; ' . $gl_locale_desc;
          }
          else {
            $submission_arr[$submission_name] = $gl_locale_desc;
          }
        }

        $url = Mage::helper("adminhtml")->getUrl("globallink/adminhtml_glactivesubmissions/index", array("selected_submission_name" => urlencode($submission_name)));
        $submission_link_arr[] = '<p><a href="' . $url . '">' . $submission_name . '</a> - ' . $submission_arr[$submission_name] . '</p>';
      }
    }

    return implode("", $submission_link_arr);
  }

}