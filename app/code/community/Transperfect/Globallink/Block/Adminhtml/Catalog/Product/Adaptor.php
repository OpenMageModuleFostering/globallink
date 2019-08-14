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

class Transperfect_Globallink_Block_Adminhtml_Catalog_Product_Adaptor extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface {

  /**
   * Set the template for the block
   *
   */
  public function _construct() {
    parent::_construct();

    $product = $this->getProduct();

    $this->setTemplate('globallink/catalog/product/adaptor.phtml');
    $this->setProduct($product);
  }

  /**
   * Retrieve the label used for the tab relating to this block
   *
   * @return string
   */
  public function getTabLabel() {
    return $this->__('GlobalLink Translation');
  }

  /**
   * Retrieve the title used by this tab
   *
   * @return string
   */
  public function getTabTitle() {
    return $this->__('Click here for GlobalLink Translation');
  }

  /**
   * Retrieve edited product model instance
   *
   * @return Mage_Catalog_Model_Product
   */
  public function getProduct() {
    return Mage::registry('product');
  }

  public function getStoreId() {
    return $this->getProduct()->getStoreId();
  }

  public function getProductId() {
    return $this->getProduct()->getId();
  }

  /**
   * Determines whether to display the tab
   * Add logic here to decide whether you want the tab to display
   *
   * @return bool
   */
  public function canShowTab() {
    return true;
  }

  /**
   * Stops the tab being hidden
   *
   * @return bool
   */
  public function isHidden() {
    return false;
  }

  /**
   * AJAX TAB's
   * If you want to use an AJAX tab, uncomment the following functions
   * Please note that you will need to setup a controller to recieve
   * the tab content request
   *
   */
  /**
   * Retrieve the class name of the tab
   * Return 'ajax' here if you want the tab to be loaded via Ajax
   *
   * return string
   */
  #   public function getTabClass()
  #   {
  #       return 'my-custom-tab';
  #   }

  /**
   * Determine whether to generate content on load or via AJAX
   * If true, the tab's content won't be loaded until the tab is clicked
   * You will need to setup a controller to handle the tab request
   *
   * @return bool
   */
  #   public function getSkipGenerateContent()
  #   {
  #       return false;
  #   }

  /**
   * Retrieve the URL used to load the tab content
   * Return the URL here used to load the content by Ajax
   * see self::getSkipGenerateContent & self::getTabClass
   *
   * @return string
   */
  #   public function getTabUrl()
  #   {
  #       return null;
  #   }
}

?>