<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Content_Archive_Grid extends Magpleasure_Blog_Block_Content_Archive
{

    protected function _getCacheParams()
    {
        $params = parent::_getCacheParams();
        $params[] = 'grid';
        return $params;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->wantAsserts()){
            $this->getExtraHead()->addSafeJs(
                "jQuery.fn.masonry",
                "mpblog/vendor/masonry/masonry.pkgd.min.js"
            );
        }

        return $this;
    }
}
