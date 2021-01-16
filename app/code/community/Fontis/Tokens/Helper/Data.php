<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Fontis Software License that is available in
 * the FONTIS-LICENSE.txt file included with this extension. This file is located
 * by default in the root directory of your Magento installation. If you are unable
 * to obtain the license from the file, please contact us via our website and you
 * will be sent a copy.
 *
 * @category   Fontis
 * @copyright  Copyright (c) 2015 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    Fontis Software License
 */

class Fontis_Tokens_Helper_Data extends Mage_Core_Helper_Abstract
{
    const LOG_FILE = 'fontis_tokens.log';

    const MODULE_FONTIS_CANONICALCATEGORY = 'Fontis_CanonicalCategory';

    /**
     * @var Fontis_Tokens_Helper_Provider_Abstract[]
     */
    protected $_providers = array();

    /**
     * Tokenise a string using tokens for the current page
     *
     * Currently, a token must be surrounded by [ and ] to be recognised and replaced
     *
     * @todo make token start and end characters/strings be configurable through system config
     *
     * @param string $text
     * @return string
     */
    public function tokenise($text)
    {
        // these will be configurable at some point
        $start = '\[';
        $end = '\]';

        $regex = '/' . $start . '[^\s' . $start . $end . ']+?' . $end . '/';

        preg_match_all($regex, $text, $matches);

        // We want a list of the unique tags we found - no point fetching values and replacing them more than once for
        // a single token, especially since str_replace() makes it easy to perform the same replacement multiple times.
        $tokens = array_unique($matches[0]);

        foreach ($tokens as $token) {
            // Need to make the lengths in this call be calculated when making the start/end strings configurable
            $value = $this->getTokenValue(substr($token, 1, -1));

            // getTokenValue() will return null if it can't find a value for the token.
            // We therefore need to check for this as we don't want to replace the token with null.
            if ($value !== null) {
                $text = str_replace($token, $value, $text);
            } else {
                Mage::log("Unable to find a token for {$token}", null, self::LOG_FILE);
                $text = str_replace($token, "", $text);
            }
        }

        return $text;
    }

    /**
     * Tokenise the values in an array
     * The values in the array must be in the same format as needed for tokenise and be surrounded by []
     *
     * @param $array array Key value pairs of descriptions and token strings wrapped in [].
     * @return array Key value pairs with descriptions and values from the page it is run on.
     */
    public function tokeniseArray($array)
    {
        $tokenisedArray = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $tokenisedArray[$key] = $this->tokeniseArray($value);
            } else {
                $tokenisedArray[$key] = $this->tokenise($value);
            }
        }
        return $tokenisedArray;
    }

    /**
     * Get the value of a token for the current page
     *
     * The provider used for the current page is stored across calls of this method. This means that if you need tokens
     * for different page types, you need to have a separate instance of this class for each page type.
     *
     * @todo add support for global tokens, such as system configuration values, that won't match the pattern
     *
     * @param string $key
     * @return string|null
     * @throws Mage_Core_Exception
     */
    public function getTokenValue($key)
    {
        $parts = explode(':', $key);

        if (count($parts) != 2) {
            // Not throwing an exception, because this is admin content, and we don't want to let them cause exceptions
            // if we can possibly avoid it. Instead, log the error and ignore the token. The admin should be inspecting
            // the output anyway, and so should see the token as not fixed.
            Mage::log("Invalid token specifier: $key - required format is entity:key", null, self::LOG_FILE);
            return;
        }

        $provider = $this->getProvider($parts[0]);

        if (!$provider) {
            // If we failed at getting the provider, do the same as above - log and return, don't throw an exception.
            Mage::log("Unable to find token provider: $parts[0]", null, self::LOG_FILE);
            return;
        }

        return $provider->getTokenValue($parts[1]);
    }

    /**
     * Looks for the relevant provider in the config xml and if found instantiates and returns it.
     *
     * @param string $entity
     * @return Fontis_Tokens_Helper_Provider_Abstract
     */
    public function getProvider($entity)
    {
        if (!isset($this->_providers[$entity])) {
            $providers = $this->getAllProviders();
            $this->_providers[$entity] = isset($providers[$entity]) ? Mage::helper($providers[$entity]) : null;
        }
        return $this->_providers[$entity];
    }

    /**
     * @return array
     */
    public function getAllProviders()
    {
        return Mage::getConfig()->getNode('frontend/tokens/providers')->asArray();
    }
}
