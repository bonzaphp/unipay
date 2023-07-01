<?php

/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 10:44
 */

namespace Bonza\Hfpay\Kernel\Providers;

use Bonza\Hfpay\Kernel\Encryption\Encryptor;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * 加解密服务提供者
 * Class EncryptionServiceProvider
 * @author bonzaphp@gmail.com
 * @Date 2023/7/1 10:44
 * @package Bonza\Hfpay\Kernel\Providers
 */
class EncryptionServiceProvider implements ServiceProviderInterface
{
    /**
     * 注册服务到指定容器
     * 配置服务和参数
     * 但不提供具体服务
     *
     * @param \Pimple\Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple['encryptor'] = static function ($app) {
            return new Encryptor(
                $app['config']->get('app_key'),
                $app['config']->get('token'),
                $app['config']->get('aes_key')
            );
        };
    }
}
