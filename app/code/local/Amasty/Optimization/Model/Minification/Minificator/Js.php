<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Minification_Minificator_Js extends Amasty_Optimization_Model_Minification_Minificator
{
    const MINIFICATOR_URL = 'closure-compiler.appspot.com/compile';

    public function isDeferred()
    {
        return true;
    }

    public function getCode()
    {
        return 'js';
    }

    public function minify($path)
    {
        $ignoreMin = Mage::getStoreConfigFlag('amoptimization/js/ignore_min');

        if ($ignoreMin && substr($path, -7) == '.min.js')
            return $path;

        return parent::minify($path);
    }

    protected function minifyFile($path)
    {
        if (!function_exists('curl_init')) {
            if (Mage::getStoreConfigFlag('amoptimization/debug/log_minification_errors')) {
                Mage::log(
                    "Minification failed. Curl module is not installed'.",
                    Zend_Log::WARN,
                    '',
                    true
                );
            }

            return false;
        }

        $level = Mage::getStoreConfig('amoptimization/js/level');

        if (!$level) {
            $level = 'WHITESPACE_ONLY';
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::MINIFICATOR_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch, CURLOPT_POSTFIELDS,
            http_build_query(array(
                'js_code' => file_get_contents($path),
                'compilation_level' => $level,
                'language' => 'ECMASCRIPT5',
                'output_format' => 'text',
                'output_info' => 'compiled_code',
            ))
        );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $serverOutput = curl_exec($ch);

        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($responseCode == 200 && substr($serverOutput, 0, 5) != 'Error') {
            file_put_contents($path, $serverOutput, LOCK_EX);

            return true;
        }
        else {
            if ($responseCode != 200) {
                $message = "Minification failed. Minification service returned code $responseCode'.";
            }
            else {
                $serverOutput = trim($serverOutput);
                $message = "Minification failed. Server response contains the following message: '$serverOutput'.";
            }

            if (Mage::getStoreConfigFlag('amoptimization/debug/log_minification_errors')) {
                Mage::log($message, Zend_Log::WARN, '', true);
            }

            return false;
        }
    }
}
