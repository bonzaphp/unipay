<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 10:19
 */
namespace Bonzaphp\Hfpay\Kernel;

class BaseClient
{
    /**
     * @var \Bonzaphp\Hfpay\Application
     */
    protected \Bonzaphp\Hfpay\Application $app;

    /**
     * @var \Bonzaphp\Hfpay\Kernel\Http\Client
     */
    protected $client;

    /**
     * Client constructor.
     *
     * @param  \Bonzaphp\Hfpay\Application  $app
     */
    public function __construct(\Bonzaphp\Hfpay\Application $app)
    {
        $this->app = $app;
        $this->client = $this->app['client']->withAccessTokenMiddleware()->withRetryMiddleware()->withAddHeaderMiddleware();
    }
}
