<?php

namespace App\Helpers;

class MaskHelper
{
    public static function remove($value)
    {
        return preg_replace('/[^0-9]/', '', $value);
    }
}