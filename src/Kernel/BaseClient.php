<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 10:19
 */
namespace Bonza\UniPay\Kernel;

use Bonza\UniPay\Kernel\Http\Client;

class BaseClient
{
    /**
     * @var \Bonza\UniPay\Application
     */
    protected \Bonza\UniPay\Application $app;

    /**
     * @var Client
     */
    protected Client $client;

    /**
     * Client constructor.
     *
     * @param  \Bonza\UniPay\Application  $app
     */
    public function __construct(\Bonza\UniPay\Application $app)
    {
        $this->app = $app;
        /** @var Client $client */
        $client = $this->app['client'];
//        $this->client = $client->withAccessTokenMiddleware()->withRetryMiddleware()->withAddHeaderMiddleware();
        $this->client = $client->withYinFuTongSignMiddleware();
    }
}
