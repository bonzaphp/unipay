<?php

/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 10:45
 */

namespace Bonzaphp\Hfpay\Kernel\Providers;

use Bonzaphp\Hfpay\Kernel\AccessToken;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AccessTokenServiceProvider implements ServiceProviderInterface
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
        isset($pimple['access_token']) || $pimple['access_token'] = function ($app) {
            return new AccessToken($app);
        };
    }
}
