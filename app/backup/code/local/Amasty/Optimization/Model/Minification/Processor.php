<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


abstract class Amasty_Optimization_Model_Minification_Processor
{
    protected $baseUrl;
    protected $baseDir;

    public function __construct()
    {
        $this->baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $this->baseDir = Mage::getBaseDir();
    }

    /**
     * @return Amasty_Optimization_Model_Minification_Minificator
     */
    abstract public function getMinificator();

    /**
     * @param string $html
     *
     * @return string
     */
    abstract public function process($html);

    public function match($matches)
    {
        $url = $matches['url'];

        if ($this->baseUrl != substr($url, 0, strlen($this->baseUrl)))
            return $matches[0];

        $filePath = substr($url, strlen($this->baseUrl));
        $filePath = str_replace('/', DS, $filePath);

        $urlInfo = explode('?', $filePath);

        $filePath = $urlInfo[0];

        $fullSrcPath = $this->baseDir . DS . $filePath;

        if (!is_file($fullSrcPath))
            return $matches[0];

        $fp = fopen($fullSrcPath, 'r');

        if (flock($fp, LOCK_EX)) {
            $destPath = $this->getMinificator()->minify($filePath);
        }
        else {
            $destPath = $filePath;
        }

        flock($fp, LOCK_UN);

        $minifiedUrl = $this->baseUrl . str_replace(DS, '/', $destPath);

        if (sizeof($urlInfo) > 1) { // Url with get params
            $minifiedUrl .= '?' . $urlInfo[1];
        }

        return $matches['prefix'] . $minifiedUrl . $matches['suffix'];
    }
}
