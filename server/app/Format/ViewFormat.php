<?php

namespace App\Format;

class ViewFormat {

    /**
     * カンマ数字をintに変換
     * input
     *  - (string) 10,000
     * return
     *  - (int) 10000
     *
     * @param string $val
     * @return integer
     */
    public static function convertAmount(string $val): int
    {
        return (int) str_replace(',','',$val);
    }

    /**
     * 百万 -> 億
     *
     * @param integer $val
     * @return integer
     */
    public static function convertMillionToBillion(int $val): int
    {
        return floor( $val / 100 );
    }

    /**
     * 千 -> 億
     *
     * @param integer $val
     * @return integer
     */
    public static function convertThousandToBillion(int $val): int
    {
        return floor( $val / 100000 );
    }

    /**
     * １ -> 万
     *
     * @param integer $val
     * @return integer
     */
    public static function convertToTenThousand(int $val): int
    {
        return floor( $val / 10000 );
    }
}