<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Minification_Processor_Fingerprints extends Amasty_Optimization_Model_Minification_Processor
{
    const MTIME_PARAM = 'v';

    public function process($html)
    {
        $html = preg_replace_callback(
            '#(?P<prefix>(src|href)=")(?P<url>[^"]+?(\.js|\.css)(\?.+?)?)(?P<suffix>")#',
            array($this, 'match'),
            $html
        );

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getMinificator()
    {
        return null;
    }

    public function match($matches)
    {
        $url = $matches['url'];

        $queryParams = array();

        if ($queryString = parse_url($url, PHP_URL_QUERY)) {
            parse_str($queryString, $queryParams);
        }

        if ($this->baseUrl != substr($url, 0, strlen($this->baseUrl)))
            return $matches[0];

        $filePath = substr($url, strlen($this->baseUrl));
        $filePath = str_replace('/', DS, $filePath);

        $urlInfo = explode('?', $filePath);

        $filePath = $urlInfo[0];

        $fullSrcPath = $this->baseDir . DS . $filePath;

        if (!is_file($fullSrcPath))
            return $matches[0];

        $mtime = filemtime($fullSrcPath);
        $queryParams[self::MTIME_PARAM] = $mtime;

        $params = array();
        foreach ($queryParams as $key => $value) {
            $params []= "$key=$value";
        }

        $fingerprintedUrl = strtok($url, '?') . '?' . implode('&', $params);

        return $matches['prefix'] . $fingerprintedUrl . $matches['suffix'];
    }
}
