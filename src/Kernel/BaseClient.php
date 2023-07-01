<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 10:19
 */
namespace Bonza\Hfpay\Kernel;

class BaseClient
{
    /**
     * @var \Bonza\Hfpay\Application
     */
    protected \Bonza\Hfpay\Application $app;

    /**
     * @var \Bonza\Hfpay\Kernel\Http\Client
     */
    protected $client;

    /**
     * Client constructor.
     *
     * @param  \Bonza\Hfpay\Application  $app
     */
    public function __construct(\Bonza\Hfpay\Application $app)
    {
        $this->app = $app;
        $this->client = $this->app['client']->withAccessTokenMiddleware()->withRetryMiddleware()->withAddHeaderMiddleware();
    }
}
