<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 10:19
 */
namespace Bonza\UniPay\Kernel\Concerns;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

trait InteractsWithCache
{
    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    protected $cache;

    /**
     * @return \Psr\SimpleCache\CacheInterface
     */
    public function getCache(): CacheInterface
    {
        if ($this->cache) {
            return $this->cache;
        }

        if (property_exists($this, 'app') && $this->app->offsetExists('cache') && ($this->app['cache'] instanceof CacheInterface)) {
            return $this->cache = $this->app['cache'];
        }

        return $this->cache = $this->createDefaultCache();
    }

    /**
     * @return \Psr\SimpleCache\CacheInterface
     */
    protected function createDefaultCache()
    {
        return new Psr16Cache(new FilesystemAdapter('hfpay'));
    }
}
