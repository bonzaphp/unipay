<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 11:31
 */

namespace Bonza\UniPay\CtfPay\Wechat\H5;

use bonza\ssl\OpenSsl;
use Bonza\UniPay\Kernel\BaseClient;
use Bonza\UniPay\Kernel\Exceptions\RuntimeException;

/**
 * 微信H5支付
 * Class Client
 * @author bonzaphp@gmail.com
 * @Date 2023/7/1 11:32
 * @package Bonza\UniPay\KjGateWay
 */
class Client extends BaseClient
{
    /**
     * @var OpenSsl
     */
    private OpenSsl $ssl;

    /**
     * 统一下单
     * @param $data
     * @author bonzaphp@gmail.com
     */
    public function uniCreateOrder($data)
    {
        echo 'success';
    }


}
