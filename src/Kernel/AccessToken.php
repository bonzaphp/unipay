<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 10:19
 */
namespace Bonza\UniPay\Kernel;

use Bonza\UniPay\Kernel\Exceptions\InvalidCredentialsException;
use Bonza\UniPay\Kernel\Http\Client;
use function Bonza\UniPay\tap;
use Overtrue\Http\Traits\ResponseCastable;

class AccessToken
{
    use Concerns\InteractsWithCache, ResponseCastable;

    /**
     * @var \Bonza\UniPay\Application
     */
    protected $app;

    /**
     * AccessToken constructor.
     *
     * @param \Bonza\UniPay\Application
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 获取钉钉 AccessToken
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get()
    {
        if ($value = $this->getCache()->get($this->cacheFor())) {
            return $value;
        }

        return $this->refresh();
    }

    /**
     * 获取 AccessToken
     *
     * @return string
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getToken()
    {
        return $this->get()['access_token'];
    }

    /**
     * 刷新钉钉 AccessToken
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function refresh()
    {
        $response = (new Client($this->app))->requestRaw('gettoken', 'GET', ['query' => [
            'appkey' => $this->app['config']->get('app_key'),
            'appsecret' => $this->app['config']->get('app_secret'),
        ]]);

        return tap($this->castResponseToType($response, 'array'), function ($value) {
            if (0 !== $value['errcode']) {
                throw new InvalidCredentialsException(json_encode($value));
            }
            $this->getCache()->set($this->cacheFor(), $value, $value['expires_in']);
        });
    }

    /**
     * 缓存 Key
     *
     * @return string
     */
    protected function cacheFor()
    {
        return sprintf('access_token.%s', $this->app['config']->get('app_key'));
    }
}
