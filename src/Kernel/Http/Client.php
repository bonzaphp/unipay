<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 10:20
 */
namespace Bonzaphp\Hfpay\Kernel\Http;

use GuzzleHttp\Middleware;
use Overtrue\Http\Client as BaseClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 *
 * Class Client
 * @author bonzaphp@gmail.com
 * @Date 2023/7/1 10:20
 * @package Bonzaphp\Hfpay\Kernel\Http
 */
class Client extends BaseClient
{
    /**替换
     * @var \Bonzaphp\Hfpay\Application
     */
    protected $app;

    /**
     * @var array
     */
    protected static $httpConfig = [
        'base_uri' => 'https://oapi.dingtalk.com',
    ];

    /**
     * @param \Bonzaphp\Hfpay\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;

        parent::__construct(array_merge(static::$httpConfig, $this->app['config']->get('http', [])));
    }

    /**
     * @param array $config
     */
    public function setHttpConfig(array $config)
    {
        static::$httpConfig = array_merge(static::$httpConfig, $config);
    }

    /**
     * @return $this
     */
    public function withAccessTokenMiddleware()
    {
        if (isset($this->getMiddlewares()['access_token'])) {
            return $this;
        }

        $middleware = function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if ($this->app['access_token']) {
                    parse_str($request->getUri()->getQuery(), $query);

                    $request = $request->withUri(
                        $request->getUri()->withQuery(http_build_query(['access_token' => $this->app['access_token']->getToken()] + $query))
                    );
                    $request = $request->withHeader('Authorization', 'Bearer ' . $this->app['access_token']->getToken());
                }

                return $handler($request, $options);
            };
        };

        $this->pushMiddleware($middleware, 'access_token');

        return $this;
    }

    /**
     * 添加新的header头
     * @return $this
     */
    public function withAddHeaderMiddleware()
    {
        if (isset($this->getMiddlewares()['add_header'])) {
            return $this;
        }

        $middleware = function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if ($this->app['access_token']) {
                    $request = $request->withHeader('x-acs-dingtalk-access-token', $this->app['access_token']->getToken());
                }

                return $handler($request, $options);
            };
        };

        $this->pushMiddleware($middleware, 'add_header');

        return $this;
    }

    /**
     * @return $this
     */
    public function withRetryMiddleware()
    {
        if (isset($this->getMiddlewares()['retry'])) {
            return $this;
        }

        $middleware = Middleware::retry(function ($retries, RequestInterface $request, ResponseInterface $response = null) {
            if (is_null($response) || $retries < 1) {
                return false;
            }

            if (in_array(json_decode($response->getBody(), true)['errcode'] ?? null, [40001])) {
                $this->app['access_token']->refresh();

                return true;
            }
        });

        $this->pushMiddleware($middleware, 'retry');

        return $this;
    }
}
