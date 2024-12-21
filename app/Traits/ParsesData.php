<?php

namespace App\Traits;

trait ParsesData
{
    private function getFloatValue($text)
    {
        return (float) filter_var($text, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
}
