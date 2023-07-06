<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 10:25
 */

namespace Bonza\UniPay;

/**
 * 委托助手函数
 * @param mixed    $value
 * @param callable $callback
 *
 * @return mixed
 */
function tap($value, $callback)
{
    $callback($value);

    return $value;
}

/**
 * 生成一个更“随机”的字母数字字符串
 *
 * @param  int  $length
 *
 * @return string
 * @throws \Exception
 */
function str_random(int $length = 16): string
{
    $string = '';

    while (($len = strlen($string)) < $length) {
        $size = $length - $len;
        $bytes = random_bytes($size);
        $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
    }

    return $string;
}
