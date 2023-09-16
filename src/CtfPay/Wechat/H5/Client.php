<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 11:31
 */

namespace Bonza\UniPay\CtfPay\Wechat\H5;

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
     * 统一下单
     * @param $data
     * @return array
     * @throws RuntimeException
     * @author bonzaphp@gmail.com
     */
    public function uniCreateOrder($data)
    {
        $res = $this->client->post('api/pay/create_order', $data);
        if (empty($res)) {
            throw new RuntimeException('Unable to create order');
        }
        if (!is_array($res)) {
            throw new RuntimeException('响应数据格式错误');
        }
        if ($res['retCode'] == 'SUCCESS') {
            return $res;
        }
        throw new RuntimeException('创建统一支付订单失败');
    }


}
