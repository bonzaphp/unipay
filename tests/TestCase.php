<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/6
 * Time: 14:58
 */

namespace Bonza\UniPay\Tests;

use Bonza\Unipay\Application;
use GuzzleHttp\ClientInterface;
use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * @param \Bonza\UniPay\Kernel\BaseClient $client
     *
     * @return \Bonza\UniPay\Kernel\BaseClient
     */
    protected function make($client)
    {
        $app = $this->newApplication([
            'token' => 'test-token',
            'aes_key' => 'test-aes-key',
            'http' => ['response_type' => 'raw'],
        ]);

        $response = new TestResponse(200, [], '{"mock": "test"}');

        $app['client']->setHttpClient(Mockery::mock(ClientInterface::class, function ($mock) use ($response) {
            $mock->shouldReceive('request')->withArgs($response->setExpectedArguments())->andReturn($response);
        }));

        return new $client($app);
    }

    /**
     * @param array $config
     * @param array $overrides
     *
     * @return \Bonza\UniPay\Application
     */
    protected function newApplication(array $config = [], array $overrides = [])
    {
        return new Application(array_merge(['appkey' => 'mock-appkey', 'appsecret' => 'mock-appsecret', 'agent_id' => 'mock-agent'], $config), $overrides);
    }

    protected function tearDown()
    {
        Mockery::close();
    }
}

