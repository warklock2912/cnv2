<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */
class Magpleasure_Common_Helper_Transliteration_Variant extends Mage_Core_Helper_Abstract
{
    /**
     * Transliteration Variant
     *
     * @return array
     */
    public function getVariant()
    {
        $variant = array();

        # Overrides for Danish input.
        $variant['x00']['da'] = array(
            0xC5 => 'Aa',
            0xD8 => 'Oe',
            0xE5 => 'aa',
            0xF8 => 'oe',
        );

        # Overrides for German input.
        $variant['x00']['de'] = array(
            0xC4 => 'Ae',
            0xD6 => 'Oe',
            0xDC => 'Ue',
            0xE4 => 'ae',
            0xF6 => 'oe',
            0xFC => 'ue',
            0xDF => 'ss',
        );

        # Overrides for Spanish input.
        $variant['x00']['es'] = array(
            0xE1 => 'a',
            0xE9 => 'e',
            0xED => 'i',
            0xF3 => 'o',
            0xFA => 'u',
            0xF1 => 'n',
        );

        # Overrides for Esperanto input.
        $variant['x01']['eo'] = array(
            0x08 => 'Cx',
            0x09 => 'cx',
            0x1C => 'Gx',
            0x1D => 'gx',
            0x24 => 'Hx',
            0x25 => 'hx',
            0x34 => 'Jx',
            0x35 => 'jx',
            0x5C => 'Sx',
            0x5D => 'sx',
            0x6C => 'Ux',
            0x6D => 'ux',
        );

        # Overrides for Kirghiz input.
        $variant['x04']['kg'] = array(
            0x01 => 'E',
            0x16 => 'C',
            0x19 => 'J',
            0x25 => 'X',
            0x26 => 'TS',
            0x29 => 'SCH',
            0x2E => 'JU',
            0x2F => 'JA',
            0x36 => 'c',
            0x39 => 'j',
            0x45 => 'x',
            0x46 => 'ts',
            0x49 => 'sch',
            0x4E => 'ju',
            0x4F => 'ja',
            0x51 => 'e',
            0xA2 => 'H',
            0xA3 => 'h',
            0xAE => 'W',
            0xAF => 'w',
            0xE8 => 'Q',
            0xE9 => 'q',
        );

        ///TODO Add more locales


        return $variant;
    }
}