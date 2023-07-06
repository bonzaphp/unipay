<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/6
 * Time: 14:15
 */

namespace Bonza\UniPay\Tests;

use Bonza\UniPay\Application;
use Bonza\UniPay\HeePay\PayApi\Client;
use PHPUnit\Framework\TestCase as BaseTestCase;

class ApplicationTest extends BaseTestCase
{

    /** @test */
    public function services()
    {
        $app = new Application();

        $services = [
            'kjpay'=>Client::class,
            'logger' => \Monolog\Logger::class,
            'config' => \Overtrue\Http\Support\Collection::class,
            'client' => \Bonza\Unipay\Kernel\Http\Client::class,
            'access_token' => \Bonza\Unipay\Kernel\AccessToken::class,
            'request' => \Symfony\Component\HttpFoundation\Request::class,
            'encryptor' => \Bonza\UniPay\Kernel\Encryption\Encryptor::class
        ];

        $this->assertCount(count($services), $app->keys());
        foreach ($services as $name => $service) {
            $this->assertInstanceof($service, $app->{$name});
            $this->assertInstanceof($service, $app[$name]);
        }
    }
}
