<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 11:31
 */

namespace Bonza\UniPay\HeePay\PayApi;

use bonza\ssl\OpenSsl;
use Bonza\UniPay\Kernel\BaseClient;
use Bonza\UniPay\Kernel\Exceptions\RuntimeException;

/**
 * 网管快捷支付
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
     * 商户私钥
     * @var string
     */
    private $merchant_private_key;
    /**
     * 商户公钥
     * @var string
     */
    private $merchant_public_key;
    /**
     * 汇付宝公钥
     * @var string
     */
    private $heepay_public_key;

    public function __construct(\Bonza\UniPay\Application $app)
    {
        parent::__construct($app);
        // 商户公钥加密
        $this->ssl = new OpenSsl(
            [
                'digest_alg'       => 'sha512',
                'private_key_bits' => 4096,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]
        );
        $key = $this->conf();
        $this->merchant_private_key = $key['merchant_private_key'];
        $this->heepay_public_key = $key['heepay_public_key'];
        $this->merchant_public_key = $key['merchant_public_key'];
    }

    /**
     * 网关签约
     * @param array $actual_params 业务参数
     * @param array $common_params 公共参数
     * @return string
     * @throws RuntimeException|\GuzzleHttp\Exception\GuzzleException
     */
    public function gatewaySign(array $actual_params, array $common_params)
    {
        /* 公共请求参数 */
        $biz_content = json_encode($actual_params, JSON_UNESCAPED_UNICODE);
        $common_params['biz_content'] = $biz_content;
        $sign_str = $this->build_params($common_params);
        $common_params['sign'] = $this->signByPrivateKey($sign_str, $this->merchant_private_key);
        $common_params['biz_content'] = $this->encrypt($this->heepay_public_key, $biz_content);
        $data = $common_params;
        $res = $this->client->postJson('API/PageSign/Index.aspx', $data);
        // 商家私钥解密,得到业务参数
        $actual_response = $this->ssl->decodeByPrivateKey($this->merchant_private_key, $res['data']);
        $check = $this->signVerify($actual_response, $res, $common_params);
        if (!$check) {
            //TODO: 验签需要完善
//            throw new RuntimeException('验签失败');
        }
        $res['biz_content'] = json_decode($actual_response, true);
        return $res;
    }

    /**
     * 签约查询
     * @param array $actual_params 业务参数
     * @param array $common_params 公共参数
     * @throws RuntimeException
     * @author bonzaphp@gmail.com
     */
    public function querySign(array $actual_params, array $common_params)
    {
        /* 签约查询接口start   */
        $biz_content = json_encode(['out_trade_no' => $actual_params['out_trade_no']], JSON_UNESCAPED_UNICODE);
        $common_params['biz_content'] = $biz_content;
        $common_params['method'] = 'heepay.agreement.bank.sign.query';
        $sign_str = $this->build_params($common_params);
        $res = openssl_sign($sign_str, $sign, $this->merchant_private_key);
        if (!$res) throw new RuntimeException('私钥签名错误');
        $sign = base64_encode($sign);
        $common_params['sign'] = $sign;
        try {
            $data = $common_params;
            $response = $this->client->postJson('API/PageSign/Index.aspx', $data);
            $json_str = $this->ssl->decodeByPrivateKey($this->merchant_private_key, $data->data);
            $json_obj = json_decode($json_str);
            $sign_no = $json_obj->sign_no;
            echo $sign_no;
            echo "</br>";
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            exit($e->getMessage());
        }
        /* 签约查询接口end   */
    }

    /**
     * 解约接口
     * @param array $data
     * @return array|object|\Overtrue\Http\Support\Collection|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws RuntimeException
     * @author bonzaphp@gmail.com
     */
    public function cancel(array $data)
    {
        $actual_params = [
            'version'     => 1,
            'hy_auth_uid' => '',
        ];
        $common_params = [
            'agent_id'     => $this->app['config']->get('merch_id'),
//            'encrypt_data' => '',
//            'sign'         => '',
        ];
        $common_params['sign'] = $this->build_params($actual_params);
        $common_params['encrypt_data'] = $this->encrypt($this->heepay_public_key,$common_params['sign']);
        return $this->client->post('WithholdAuthPay/CancelAuth.aspx', $data);
    }

    /**
     * 验签
     * @param string $actual_response 返回的业务参数
     * @param $res
     * @return bool
     * @throws RuntimeException
     * @author bonzaphp@gmail.com
     */
    private function signVerify(string $actual_response, array $res, $common_params): bool
    {
        $common_params['biz_content'] = $actual_response;
        $sign_str = $this->build_params($common_params);
        return $this->signByPublicKeyVerify($sign_str, $res['sign'], $this->heepay_public_key);
    }

    /**
     * 解密业务参数,商家私钥解密
     * @param $data
     * @author bonzaphp@gmail.com
     */
    private function decrypt(string $data)
    {
        //解密出业务参数
        $actual_response = $this->ssl->decodeByPrivateKey($this->merchant_private_key, $data);
//        echo ($actual_response);
        $json_obj = json_decode($actual_response);
        echo "</br>";
        die;
        $signUrl = $json_obj->sign_url;
        echo $signUrl;
        echo "<script language='javascript'>location.href='$signUrl';</script>";
        exit();
        return;
        echo "</br>";
    }

    /**
     * 加密业务参数
     * @param $heepay_public_key
     * @param $biz_content
     * @return mixed
     * @author bonzaphp@gmail.com
     */
    private function encrypt($heepay_public_key, $biz_content)
    {
        $biz_content = str_replace('\/', '/', $biz_content);
        return $this->ssl->encodeByPublicKey($heepay_public_key, $biz_content);
    }

    /**
     * 返回pem格式的密钥
     * @return array
     * @author bonzaphp@gmail.com
     */
    private function conf()
    {
        //转换汇元公钥为pem格式
        $heepay_public_key = $this->ssl->pkcsToPemPublicKey($this->app['config']->get('heepay_public_key'));
        // 商户公钥pem格式
        $merchant_public_key = $this->ssl->pkcsToPemPublicKey($this->app['config']->get('merchant_public_key'));
        // 商家私钥pem格式
        $merchant_private_key = $this->ssl->pkcsToPemPrivateKey($this->app['config']->get('merchant_private_key'));
        return [
            'merchant_private_key' => $merchant_private_key,
            'heepay_public_key'    => $heepay_public_key,
            'merchant_public_key'  => $merchant_public_key,
        ];
    }

    /**
     * 通过私钥签名
     * @param string $sign_str
     * @param string $private_key
     * @return string
     * @throws RuntimeException
     * @author bonzaphp@gmail.com
     */
    private function signByPrivateKey(string $sign_str, string $private_key)
    {
        $res = openssl_sign($sign_str, $sign, $private_key);
        if (!$res) throw new RuntimeException('私钥签名错误');
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * 通过公钥验签
     * @param string $data 待验签数据
     * @param string $signature 签名字符串
     * @param string $public_key pem格式公钥
     * @return bool
     * @author bonzaphp@gmail.com
     */
    private function signByPublicKeyVerify(string $data, $signature, string $public_key): bool
    {
        $res = openssl_verify($data, base64_decode($signature), $public_key);
        return $res === 1;
    }

    /**
     * 创建待签名字符串
     * @param array $common_params 公共参数
     * @return string
     * @throws RuntimeException
     * @author bonzaphp@gmail.com
     */
    private function build_params(array $common_params)
    {
        unset($common_params['sign']);
        $sign = ksort($common_params);
        if (!$sign) {
            throw new RuntimeException('公共参数排序失败');
        }
        $sign_str = http_build_query($common_params);//对URL编码，进行解码
        $sign_str = urldecode($sign_str);
        return $sign_str;
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
        return $this->client->post('WithholdAuthPay/SendPaySMS.aspx', $message);
    }

}
