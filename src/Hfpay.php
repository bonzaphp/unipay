<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/6/29
 * Time: 19:13
 */
namespace Bonza\Hfpay;
/**
 * 汇元支付类
 * Class Hfpay
 * @author bonzaphp@gmail.com
 * @Date 2023/6/29 19:14
 * @package Bonza\Hfpay
 */
class Hfpay
{
    //汇付宝商户号
    public $merch_id;
    //汇付宝网关地址
    public $gateway_url;
    //汇付宝公钥
    public $heepay_public_key;
    //商户私钥
    public $merchant_private_key;
    //商户公钥
    public $merchant_public_key;
    //签名方式
    public $sign_type;
    //日志路径
    public $log_path;

    function __construct($Heepay_config)
    {
        $this->merch_id = $Heepay_config['merch_id'];
        $this->gateway_url = $Heepay_config['gateway_Url'];
        $this->heepay_public_key = $Heepay_config['heepay_public_key'];
        $this->merchant_private_key = $Heepay_config['merchant_private_key'];
        $this->merchant_public_key = $Heepay_config['merchant_public_key'];
        $this->sign_type = $Heepay_config['sign_type'];
        $this->log_path = $Heepay_config['log_path'];
        if (empty($this->gateway_url) || trim($this->gateway_url) == "") {
            throw new Exception("gateway_url should not be NULL!");
        }
        if (empty($this->heepay_public_key) || trim($this->heepay_public_key) == "") {
            throw new Exception("heepay_public_key should not be NULL!");
        }
        if (empty($this->merchant_private_key) || trim($this->merchant_private_key) == "") {
            throw new Exception("merchant_private_key should not be NULL!");
        }
        if (empty($this->sign_type) || trim($this->sign_type) == "") {
            throw new Exception("sign_type should not be NULL!");
        }
        if (empty($this->log_path) || trim($this->log_path) == "") {
            throw new Exception("log_path should not be NULL!");
        }

    }

    function aopclientRequestExecute($request, $ispage = false)
    {
        $apiClient = new ApiClient();
        $apiClient->gatewayUrl = $this->gateway_url;
        $apiClient->appId = $this->merch_id;
        $apiClient->rsaPrivateKey = $this->merchant_private_key;
        $apiClient->rsaPublicKey = $this->merchant_public_key;
        $apiClient->heepayPublicKey = $this->heepay_public_key;
        $apiClient->apiVersion = "1.0";
        $apiClient->postCharset = "utf-8";
        $apiClient->format = "json";
        $apiClient->encryptType = $this->sign_type;
        $apiClient->signType = $this->sign_type;
        // 开启页面信息输出
        $apiClient->debugInfo = true;
        $this->writeLog("request:" . var_export($request, true));
        if ($ispage) {
            $result = $apiClient->pageExecute($request, "post");//已注销
            echo $result;
        } else {
            $result = $apiClient->Execute($request);
        }
        //打开后，将报文写入log文件
        $this->writeLog("response: " . var_export($result, true));
        return $result;
    }

    /**
     * 签名提交
     * @param ParamBuilder $builder 业务参数，使用buildmodel中的对象生成。
     * @return  $response 支付宝返回的信息
     */
    function SignPageSubmit(ParamBuilder $builder)
    {
        $biz_content = $builder->getBizContent();
        //打印业务参数
        $this->writeLog($biz_content);
        $request = new TradeRequest();
        $request->setApiMethodName("heepay.agreement.bank.sign.page");
        $request->setApiVersion("1.0");
        $request->setMerchId($this->merch_id);
        $request->setBizContent($biz_content);
        $request->setTimestamp(date("Y-m-d H:i:s"));
        // 统一API入口
        $response = $this->aopclientRequestExecute($request, false);
        return $response;
    }

    /**
     * alipay.trade.query (统一收单线下交易查询)
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @return $response 支付宝返回的信息
     */
    function Query($builder)
    {
        $biz_content = $builder->getBizContent();
        //打印业务参数
        $this->writeLog($biz_content);
        $request = new AlipayTradeQueryRequest();
        $request->setBizContent($biz_content);
        // 首先调用支付api
        $response = $this->aopclientRequestExecute($request);
        $response = $response->alipay_trade_query_response;
        var_dump($response);
        return $response;
    }

    /**
     * 验签方法
     * @param $arr 验签支付宝返回的信息，使用支付宝公钥。
     * @return boolean
     */
    function check($arr)
    {
        $aop = new AopClient();
        $aop->alipayrsaPublicKey = $this->alipay_public_key;
        $result = $aop->rsaCheckV1($arr, $this->alipay_public_key, $this->signtype);
        return $result;
    }

    //请确保项目文件有可写权限，不然打印不了日志。
    function writeLog($text)
    {
        // $text=iconv("GBK", "UTF-8//IGNORE", $text);
        //$text = characet ( $text );
        if (!empty($this->log_path) && trim($this->log_path) != "") {
            file_put_contents($this->log_path, date("Y-m-d H:i:s") . "  " . $text . "\r\n", FILE_APPEND);
        }
    }


    /** *利用google api生成二维码图片
     * $content：二维码内容参数
     * $size：生成二维码的尺寸，宽度和高度的值
     * $lev：可选参数，纠错等级
     * $margin：生成的二维码离边框的距离
     */
    function create_erweima($content, $size = '200', $lev = 'L', $margin = '0')
    {
        $content = urlencode($content);
        $image = '<img src="http://chart.apis.google.com/chart?chs=' . $size . 'x' . $size . '&amp;cht=qr&chld=' . $lev . '|' . $margin . '&amp;chl=' . $content . '"  widht="' . $size . '" height="' . $size . '" />';
        return $image;
    }
}

