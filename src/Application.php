<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 10:19
 */
namespace Bonza\UniPay;

use Overtrue\Http\Support\Collection;
use Pimple\Container;

/**
 * @property \Bonza\UniPay\Heepay\PayApi\Client $hee_pay
 * @property \Bonza\UniPay\CtfPay\Wechat\H5\Client $ctf_wechat_h5
 * @property \Monolog\Logger $logger
 * @property \Bonza\UniPay\Kernel\Server $server
 * @property \Symfony\Component\HttpFoundation\Request $request
 * @property \Bonza\UniPay\Kernel\Encryption\Encryptor $encryptor
 * @property \Bonza\UniPay\Kernel\AccessToken $access_token
 */
class Application extends Container
{
    /**
     * 应用默认服务提供者
     * @var array
     */
    protected array $providers = [
        \Bonza\UniPay\Heepay\PayApi\ServiceProvider::class,
        \Bonza\UniPay\CtfPay\Wechat\H5\ServiceProvider::class,
        Kernel\Providers\ClientServiceProvider::class,
        Kernel\Providers\LoggerServiceProvider::class,
        Kernel\Providers\ServerServiceProvider::class,
        Kernel\Providers\RequestServiceProvider::class,
        Kernel\Providers\EncryptionServiceProvider::class,
        Kernel\Providers\AccessTokenServiceProvider::class,
    ];

    /**
     * 应用初始化
     *
     * @param  array  $config
     * @param  array  $values
     */
    public function __construct(array $config = [], array $values = [])
    {
        parent::__construct($values);
        $this['config'] = static function () use ($config) {
            return new Collection($config);
        };
        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this[$name];
    }
}
