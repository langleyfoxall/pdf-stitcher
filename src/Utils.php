<?php

namespace LangleyFoxall\PdfStitcher;

/**
 * Class Utils
 * @package LangleyFoxall\PdfStitcher
 */
abstract class Utils
{
    /**
     * Wraps a specified string in double quotes and returns it.
     *
     * @param string $string
     * @return string
     */
    public static function quote(string $string)
    {
        return '"'.$string.'"';
    }
}