<?php

/**
 * 評価に関するユーティリティクラス
 *
 * @author wataru
 */
class ValidUtil {

    /**
     * ISBNが正しいかどうかの判断メソッド
     * https://memo.dogmap.jp/2013/02/20/php-valid-isbn/
     * @param type $isbn
     * @return boolean
     */
    public static function validISBN($isbn) {
        if (strlen($isbn) < 12){
            return 'ISBN too short. length : ' . strlen($isbn);
        }
        if (strlen($isbn) > 13){
            return 'ISBN too long. length : ' . strlen($isbn);
        }
        $runningTotal = 0;
        $r = 1;
        $multiplier = 1;
        for ($i = 0; $i < 13 ; $i++){
            $nums[$r] = substr($isbn, $i, 1);
            $r++;
        }
        $inputChecksum = array_pop($nums);
        foreach($nums as $key => $value){
            $runningTotal += $value * $multiplier;
            $multiplier = $multiplier == 3 ? 1 : 3;
        }
        $div = $runningTotal / 10;
        $remainder = $runningTotal % 10;

        $checksum = $remainder == 0 ? 0 : 10 - substr($div, -1);
        if (is_numeric($inputChecksum) && $inputChecksum != $checksum){
            return 'Input checksum digit incorrect: ISBN not valid';
        }

        return TRUE;
    }
}
