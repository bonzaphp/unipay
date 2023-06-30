<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/6/29
 * Time: 19:26
 */

namespace Bonzaphp\Hfpay;
/**
 * 汇付宝接口公共参数封装
 * Class TradeRequest
 * @author bonzaphp@gmail.com
 * @Date 2023/6/29 19:27
 * @package Bonzaphp\Hfpay
 */
class TradeRequest
{
    private $method;
    private $version;
    private $merchId;
    private $timestamp;
    private $bizContent;
    private $apiParas = [];


    public function setBizContent($bizContent)
    {
        $this->bizContent = $bizContent;
        $this->apiParas["biz_content"] = $bizContent;
    }

    public function getBizContent()
    {
        return $this->bizContent;
    }

    public function setApiMethodName($method)
    {
        $this->method = $method;
    }

    public function getApiMethodName()
    {
        return $this->method;
    }

    public function setApiVersion($version)
    {
        $this->version = $version;
    }

    public function getApiVersion()
    {
        return $this->version;
    }

    public function setMerchId($merchId)
    {
        $this->merchId = $merchId;
    }

    public function getMerchId()
    {
        return $this->merchId;
    }

    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getApiParas()
    {
        return $this->apiParas;
    }
}
