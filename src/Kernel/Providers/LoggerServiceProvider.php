<?php

/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 10:45
 */

namespace Bonza\UniPay\Kernel\Providers;

use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class LoggerServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param \Pimple\Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        isset($pimple['logger']) || $pimple['logger'] = function ($app) {
            return new Logger('Bonza\UniPay');
        };
    }
}
