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
     * @return array
     * @throws RuntimeException
     */
    public function gatewaySign(array $actual_params, array $common_params): array
    {
        /* 公共请求参数 */
        $biz_content = json_encode($actual_params, JSON_UNESCAPED_UNICODE);
        $common_params['biz_content'] = $biz_content;
        $sign_str = $this->build_params($common_params);
        $common_params['sign'] = $this->signByPrivateKey($sign_str, $this->merchant_private_key);
        $common_params['biz_content'] = $this->encrypt($this->heepay_public_key, $biz_content);
        $data = $common_params;
        $res = $this->client->request(
            'API/PageSign/Index.aspx',
            'POST',
            ['query' => [], 'json' => $data],
        );
        if ($res['code'] !== 10000) {
            throw new RuntimeException($res['sub_msg']);
        }
        // 商家私钥解密,得到业务参数
        $actual_response = $this->ssl->decodeByPrivateKey($this->merchant_private_key, $res['data']);
        $res['biz_content'] = json_decode($actual_response, true);
        return $res;
    }

    /**
     * 签约查询
     * @param array $actual_params 业务参数
     * @param array $common_params 公共参数
     * @return string
     * @throws RuntimeException
     * @author bonzaphp@gmail.com
     */
    public function querySign(array $actual_params, array $common_params): string
    {
        $biz_content = json_encode(['out_trade_no' => $actual_params['out_trade_no']], JSON_UNESCAPED_UNICODE);
        $common_params['biz_content'] = $biz_content;
        $common_params['method'] = 'heepay.agreement.bank.sign.query';
        $sign_str = $this->build_params($common_params);
        $res = openssl_sign($sign_str, $sign, $this->merchant_private_key);
        if (!$res) throw new RuntimeException('私钥签名错误');
        $sign = base64_encode($sign);
        $common_params['sign'] = $sign;
        $data = $common_params;
        $response = $this->client->request(
            'API/PageSign/Index.aspx',
            'POST',
            ['query' => [], 'json' => $data],
        );
        $json_str = $this->ssl->decodeByPrivateKey($this->merchant_private_key, $response['data']);
        $json_obj = json_decode($json_str);
        return $json_obj->sign_no;
    }

    /**
     * 验签
     * @param string $actual_response 返回的业务参数
     * @param array $res
     * @param $common_params
     * @return bool
     * @throws RuntimeException
     * @author bonzaphp@gmail.com
     */
    private function signVerify(string $actual_response, array $res, $common_params): bool
    {
        $common_params['biz_content'] = $actual_response;
        $sign_str = $this->build_params($common_params);
        return $this->checkSign($actual_response, $res['sign'], $this->heepay_public_key);
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
    private function checkSign(string $data, $signature, string $public_key): bool
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
     * @param string $sign_no
     * @param $out_trade_no
     * @return array|object|\Overtrue\Http\Support\Collection|\Psr\Http\Message\ResponseInterface|string
     * @throws RuntimeException
     */
    public function sendPayMessage(string $sign_no, $out_trade_no)
    {
        $sms_params = [
            "version"         => 1,
            "agent_bill_id"   => $out_trade_no,
            "agent_bill_time" => date('YmdHis'),
            "pay_amt"         => 0.01,
            "goods_name"      => 'ceshi 009',
            "hy_auth_uid"     => $sign_no,
            "user_ip"         => '0.0.0.0',
            "notify_url"      => 'http://5164efd8.r2.cpolar.top/notify.php',
            "return_url"      => '',
            "expire_time"     => 600,
        ];
        ksort($sms_params);
        $encrypt_data_str = http_build_query($sms_params);
        //因为 注意：(encrypt_data和sign开发平台默认URLEncode处理了不用再编码提交)，所以对URL编码，进行解码
        $encrypt_data_str = urldecode($encrypt_data_str);
        //商家私钥签名
        openssl_sign($encrypt_data_str, $sign, $this->merchant_private_key);
        $sms_common_params = [
            'agent_id'     => 1664502,
            'encrypt_data' => ($this->ssl->encodeByPublicKey($this->heepay_public_key, $encrypt_data_str)),
            'sign'         => base64_encode($sign),
        ];
        $data = $sms_common_params;
        $response = $this->client->post('WithholdAuthPay/SendPaySMS.aspx', $data);
        if ($response['ret_code'] === "0000") {
            $json_str = $this->ssl->decodeByPrivateKey($this->merchant_private_key, $response['encrypt_data']);
            echo $json_str;
            $json_obj = json_decode($json_str);
            return $json_obj->hy_token_id;
        }
        if ($response['ret_code'] === "E104") {
            throw new RuntimeException($response['ret_msg']);
        }
        $json_str = $this->ssl->decodeByPrivateKey($this->merchant_private_key, $response['data']);
        $json_obj = json_decode($json_str);
        return $json_obj->hy_token_id;
    }

    /**
     * 确认支付接口
     * @param string $hy_token_id
     * @param $sms_code
     * @return array|object|\Overtrue\Http\Support\Collection|\Psr\Http\Message\ResponseInterface|string
     * @throws RuntimeException
     * @author bonzaphp@gmail.com
     */
    public function confirmPay(string $hy_token_id, string $sms_code)
    {
        $actual_params = [
            'version'     => 1,
            'hy_token_id' => $hy_token_id,
            'verify_code' => $sms_code,
        ];
        $common_params = [
            'agent_id' => $this->app['config']->get('merch_id'),
        ];
        $sign_str = $this->build_params($actual_params);
        $common_params['sign'] = $this->signByPrivateKey($sign_str, $this->merchant_private_key);
        $common_params['encrypt_data'] = $this->encrypt($this->heepay_public_key, $sign_str);
//        $response = $this->client->post('WithholdAuthPay/ConfirmPay.aspx', $common_params);
//        模拟数据
        $response = [
            "ret_code"     => "0000",
            "ret_msg"      => "提交成功，等待银行处理结果",
            "encrypt_data" => "dPbaS6AUO+PSB9MLtRQM8qo23CzYbMTRfBwCY+4UmzeNGIkFvqRlS5rk/Dx+94Be5uLvuI6x9JAl43ONDfgmu1aQNW/awfQhJaMt0gZY0Z5tbQZJqldTGAg+25rTxU7oSOD1MLt1LZJRKrDO4CTvrONNkEF3kxZeqfOGmYTtFezq1wMi8rXX4wCfbkNxXBFyp807A9vhQwDefGU9jAlg9r1kpZXCT+VyVXu/urjGn7o9bUaTsKQzf7aDWeW4w0jweurvMWbnhrQO2/lcfzgO+0IG2/CzwG8yZJqWJ46CxgAWKLp7vde6lfxAkCCWX6w+lOf4HbO4JssP5fHwfjUi9Q==",
            "sign"         => "yX1PM33k9EFMV69kCdVq/3vhkmCY1UC9cKfPbkrSXt4kO5sBcWxYI3VXuMlOICL32NW8vh+6y9e3E976IFX/VuMgDFC/4ITCejxA+A/UDfsdQdh10NHs1zDNcSYDBa0aOJwB+/U6NSvGQ/bw+DA75PvmZvDFH4dVuh4bmxFGrRMhRPCxaDZa1dwebB1ZRQvpM53w5vsakSC7a8jaIt+RNYFF29yQ2XLWghXSWhQQKHle8Vuj4Je7sjB8g4HZtS0UJ3k7FTxHrTvlg0Qee7Ndwi4B/8vryhBvvuhS08XUw6SB9rG0c1XxHUTKJwIAFIaZi+i62joMxMwb4ghkyE4VLQ==",
        ];
        if ($response['ret_code'] === '0000') {
            return $this->ssl->decodeByPrivateKey($this->merchant_private_key, $response['encrypt_data']);
        }
        throw new RuntimeException($response['ret_msg']);
    }

    /**
     * 解约接口
     * @param string $hy_auth_uid 授权码
     * @return array|object|\Overtrue\Http\Support\Collection|\Psr\Http\Message\ResponseInterface|string
     * @throws RuntimeException
     * @author bonzaphp@gmail.com
     */
    public function cancel(string $hy_auth_uid)
    {
        $actual_params = [
            'version'     => 1,
            'hy_auth_uid' => $hy_auth_uid,
        ];
        $common_params = [
            'agent_id' => $this->app['config']->get('merch_id'),
        ];
        $common_params['sign'] = $this->build_params($actual_params);
        $common_params['encrypt_data'] = $this->encrypt($this->heepay_public_key, $common_params['sign']);
        $response =  $this->client->post('WithholdAuthPay/CancelAuth.aspx', $common_params);
        if ($response['ret_code'] === '0000') {
            return $this->ssl->decodeByPrivateKey($this->merchant_private_key, $response['encrypt_data']);
        }
        throw new RuntimeException($response['ret_msg']);
    }

}
