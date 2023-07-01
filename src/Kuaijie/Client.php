<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 11:31
 */
namespace Bonza\Hfpay\Kuaijie;

use Bonza\Hfpay\Kernel\BaseClient;

/**
 * 网管快捷支付
 * Class Client
 * @author bonzaphp@gmail.com
 * @Date 2023/7/1 11:32
 * @package Bonza\Hfpay\KjGateWay
 */
class Client extends BaseClient
{
    /**
     * 网关签约
     *
     */
    public function gatewaySign()
    {
        $content = [];
//        return $this->client->post('https://Pay.Heepay.com/API/PageSign/Index.aspx', $content);
        return "123";
    }

    /**
     * 发送支付短信
     *
     * @param $message
     * @return array|object|\Overtrue\Http\Support\Collection|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendPayMessage($message)
    {
        return $this->client->post('Https://Pay.Heepay.com/WithholdAuthPay/SendPaySMS.aspx', $message);
    }

}
