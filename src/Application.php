<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 10:19
 */
namespace Bonzaphp\Hfpay;

use Overtrue\Http\Support\Collection;
use Pimple\Container;

/**
 * @property \Bonzaphp\Hfpay\Kuaijie\Client $kjpay
 * @property \Monolog\Logger $logger
 * @property \Bonzaphp\Hfpay\Kernel\Server $server
 * @property \Symfony\Component\HttpFoundation\Request $request
 * @property \Bonzaphp\Hfpay\Kernel\Encryption\Encryptor $encryptor
 * @property \Bonzaphp\Hfpay\Kernel\AccessToken $access_token
 */
class Application extends Container
{
    /**
     * 应用默认服务提供者
     * @var array
     */
    protected array $providers = [
        Kuaijie\ServiceProvider::class,
        Kernel\Providers\ClientServiceProvider::class,
        Kernel\Providers\LoggerServiceProvider::class,
        Kernel\Providers\ServerServiceProvider::class,
        Kernel\Providers\RequestServiceProvider::class,
        Kernel\Providers\EncryptionServiceProvider::class,
        Kernel\Providers\AccessTokenServiceProvider::class,
    ];

    /**
     * 应用出事化
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
