<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Helper_Js extends Mage_Core_Helper_Abstract
{
    const REGEX_JS            = '#(<!--\[if[^\n]*>\s*(<script.*</script>)+\s*<!\[endif\]-->)|(<script.*</script>)#isU';
    const REGEX_DOCUMENT_END  = '#</body>\s*</html>#isU';

    public function isFooterJsEnabled()
    {
        return Mage::getStoreConfigFlag("amoptimization/footerjs/enabled");
    }

    public function replaceCallback($a)
    {
        if ($this->isIgnored($a[0]))
            return $a[0];
        else
            return '';
    }

    public function isIgnored($html)
    {
        $ignore = Mage::getStoreConfig("amoptimization/footerjs/ignore_list");
        $ignoreList = preg_split('|[\r\n]+|', $ignore, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($ignoreList as $ignoreItem){
            if (false !== strpos($html, $ignoreItem))
                return true;
        }
        return false;
    }

    public function removeJs($html)
    {
        $html = preg_replace_callback(
            self::REGEX_JS, array($this, 'replaceCallback'), $html
        );

        return $html;
    }

    public function moveJsToFooter($html)
    {
        if (preg_match_all(self::REGEX_JS, $html, $matches)) {
            $result = $this->removeJs($html);

            $scripts = '';

            foreach ($matches[0] as $match) {
                if (!$this->isIgnored($match)) {
                    $scripts .= $match;
                }
            }
            $scripts = str_replace('$', '\$', $scripts);

            $result = preg_replace(
                self::REGEX_DOCUMENT_END, "$scripts\\0", $result, -1, $count
            );

            if ($count == 1) {
                return $result;
            }
        }

        return $html;
    }
}
