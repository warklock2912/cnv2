<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */
class Magpleasure_Blog_Block_Adminhtml_Post_Edit_Tab_Media extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    protected $_values;

    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper() {
        return Mage::helper('mpblog');
    }

    protected function _getValues() {
        if (Mage::getSingleton('adminhtml/session')->getPostData()) {
            $this->_values = Mage::getSingleton('adminhtml/session')->getPostData();
            Mage::getSingleton('adminhtml/session')->getPostData(null);
        } elseif (Mage::registry('current_post')) {
            $this->_values = Mage::registry('current_post')->getData();
        }
        if (!isset($this->_values['grid_class'])) {
            $this->_values['grid_class'] = 'w1';
        }
        return $this->_values;
    }

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $values = $this->_getValues();

        $fieldset = $form->addFieldset('thumbnails', array('legend' => $this->_helper()->__('Thumbnails')));

        $fieldset->addType('ajax_image', 'Magpleasure_Common_Block_System_Entity_Form_Element_File_Image');

        $fieldset->addField('list_thumbnail', 'ajax_image', array(
            'label' => $this->_helper()->__('List Image'),
            'required' => false,
            'name' => 'list_thumbnail',
            'dir' => 'magpleasure' . DS . 'mpblog',
            'url' => 'magpleasure/mpblog',
        ));

      $fieldset->addField('image_top_landing', 'ajax_image', array(
        'label' => $this->_helper()->__('Image top Landing'),
        'required' => false,
        'name' => 'image_top_landing',
        'dir' => 'magpleasure' . DS . 'mpblog',
        'url' => 'magpleasure/mpblog',
      ));

//        $fieldset->addField('post_thumbnail', 'ajax_image', array(
//            'label' => $this->_helper()->__('Post Image'),
//            'required' => false,
//            'name' => 'post_thumbnail',
//            'dir' => 'magpleasure' . DS . 'mpblog',
//            'url' => 'magpleasure/mpblog',
//        ));

        $a = 0;
        $data['post_id'] = $this->getRequest()->getParam('id');
        if (isset($data['post_id'])) {
            $datas = Mage::getModel('mpblog/blogimages')->getCollection()->addFieldToFilter('post_id', array('finset' => $data['post_id']));
            $imagess = array();
            foreach ($datas as $datass) {
                $imagess[] = $datass->getImages();
            }

            if (count($imagess)) {
                $a = 1;
            } else {
                $a = 0;
            }

            $imagess = implode('","', $imagess);
            $imagess = '["' . $imagess . '"]';
        }

        $script = ($a) ? 'setImage(' . $imagess . ',"' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'magebuzz/");' : '';
        $uploadimage = $this->__('Post Image');

        $fieldset->addField('images', 'hidden', array('name' => 'images',
            'label' => Mage::helper('mpblog')->__('Images'),
            'title' => Mage::helper('mpblog')->__('Images'),
            'after_element_html' => "
                 <label> 
            <?php echo $this->__('Or upload your photo') ?>

                $uploadimage

        </label>
                <div> 
                    <div>
                        <input type='file' name='magebuzz_input2' id='magebuzz_input2' > 
                    </div>
                <div>
                <div class='jFiler-items jFiler-row'>
                    <ul class='jFiler-item-list' id='jFiler_item_list_images'>
                    </ul>
                </div>                
                </div>
            </div>
    <script type='text/javascript'>
        " . $script . "       
    </script>
        "
        ));


        $fieldset->addField('thumbnail_url', 'text', array(
            'label' => $this->_helper()->__('Image Link'),
            'required' => false,
            'name' => 'thumbnail_url',
            'note' => $this->_helper()->__("<i>{{store url=''}}</i> or <i>http://www.store.com/</i> variants will work"),
        ));

        $fieldset = $form->addFieldset('display', array('legend' => $this->_helper()->__('Display Settings')));

        $fieldset->addType('grid_class', 'Magpleasure_Blog_Block_System_Entity_Form_Element_Grid_Class');
        $fieldset->addField('grid_class', 'grid_class', array(
            'label' => $this->_helper()->__('Grid Width'),
            'required' => false,
            'name' => 'grid_class',
        ));

        $form->setValues($values);
        return parent::_prepareForm();
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel() {
        return $this->_helper()->__("Thumbnail");
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle() {
        return $this->_helper()->__("Thumbnail");
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab() {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden() {
        return false;
    }

}
