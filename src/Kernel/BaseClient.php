<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 10:19
 */
namespace Bonza\UniPay\Kernel;

class BaseClient
{
    /**
     * @var \Bonza\UniPay\Application
     */
    protected \Bonza\UniPay\Application $app;

    /**
     * @var \Bonza\UniPay\Kernel\Http\Client
     */
    protected $client;

    /**
     * Client constructor.
     *
     * @param  \Bonza\UniPay\Application  $app
     */
    public function __construct(\Bonza\UniPay\Application $app)
    {
        $this->app = $app;
        $this->client = $this->app['client']->withAccessTokenMiddleware()->withRetryMiddleware()->withAddHeaderMiddleware();
    }
}
