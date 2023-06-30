<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/6/29
 * Time: 19:07
 */

namespace Bonzaphp\Hfpay;
/**
 * 参数构建类
 * Class ParamBuilder
 * @author bonzaphp@gmail.com
 * @Date 2023/6/29 19:07
 * @package Bonzaphp\Hfpay
 */
class ParamBuilder
{
    // 商户订单号.
    private $outTradeNo;

    // 商户订单时间
    private $outTradeTime;

    // 商户平台用户唯一标识
    private $merchUserId;


    //同步地址
    private $returnUrl;
    //异步地址
    private $notifyUrl;

    private $bizContentarr = [];

    private $bizContent = NULL;

    public function getBizContent()
    {
        if (!empty($this->bizContentarr)) {
            $this->bizContent = json_encode($this->bizContentarr, JSON_UNESCAPED_UNICODE);
        }
        return $this->bizContent;
    }

    public function setOutTradeNo($outTradeNo)
    {
        $this->outTradeNo = $outTradeNo;
        $this->bizContentarr['out_trade_no'] = $outTradeNo;
    }

    public function getOutTradeNo()
    {
        return $this->outTradeNo;
    }

    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
        $this->bizContentarr['return_url'] = $returnUrl;
    }

    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
        $this->bizContentarr['notify_url'] = $notifyUrl;
    }

    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    public function setOutTradeTime($outTradeTime)
    {
        $this->outTradeTime = $outTradeTime;
        $this->bizContentarr['out_trade_time'] = $outTradeTime;
    }

    public function getOutTradeTime()
    {
        return $this->outTradeTime;
    }

    public function setMerchUserId($merchUserId)
    {
        $this->merchUserId = $merchUserId;
        $this->bizContentarr['merch_user_id'] = $merchUserId;
    }

    public function getMerchUserId()
    {
        return $this->merchUserId;
    }

}

