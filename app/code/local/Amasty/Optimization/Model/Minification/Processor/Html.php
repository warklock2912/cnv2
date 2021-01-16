<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Minification_Processor_Html extends Amasty_Optimization_Model_Minification_Processor
{
    /**
     * @param string $html
     *
     * @return string|null
     */
    public function process($html)
    {
        return $this->minify($html);
    }

    /**
     * @param $content
     *
     * @return string|null
     */
    protected function minify($content)
    {
        $search = array(
            '/[\r\n]+/s',
            '/[ \t]+/s',
            '/[ \t]*\n[ \t]*/s',
            '/\n+/s',
        );

        $replace = array(
            "\n",
            " ",
            "\n",
            "\n"
        );

        return preg_replace($search, $replace, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function getMinificator()
    {
        return null;
    }
}
