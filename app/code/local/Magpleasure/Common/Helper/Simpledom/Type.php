<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Helper_Simpledom_Type
{
    const TYPE_ELEMENT = 1;
    const TYPE_COMMENT = 2;
    const TYPE_TEXT =    3;
    const TYPE_ENDTAG =  4;
    const TYPE_ROOT =    5;
    const TYPE_UNKNOWN = 6;
    const QUOTE_DOUBLE = 0;
    const QUOTE_SINGLE = 1;
    const QUOTE_NO =     3;
    const INFO_BEGIN =   0;
    const INFO_END =     1;
    const INFO_QUOTE =   2;
    const INFO_SPACE =   3;
    const INFO_TEXT =    4;
    const INFO_INNER =   5;
    const INFO_OUTER =   6;
    const INFO_ENDSPACE = 7;
}