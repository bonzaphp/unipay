<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 10:19
 */

namespace Bonza\UniPay\Kernel;

use Bonza\UniPay\Kernel\Exceptions\InvalidArgumentException;
use function Bonza\UniPay\tap;
use Symfony\Component\HttpFoundation\Response;

class Server
{
    /**
     * @var \Bonza\UniPay\Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $handlers = [];

    /**
     * @param \Bonza\UniPay\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Handle the request.
     *
     * @return Response
     */
    public function serve(): Response
    {
        foreach ($this->handlers as $handler) {
            $handler->__invoke($this->getPayload());
        }

        $this->app['logger']->debug('Request received: ', [
            'method' => $this->app['request']->getMethod(),
            'uri' => $this->app['request']->getUri(),
            'content' => $this->app['request']->getContent(),
        ]);

        return tap(new Response(
            $this->app['encryptor']->encrypt('success'), 200, ['Content-Type' => 'application/json']
        ), function ($response) {
            $this->app['logger']->debug('Response created:', ['content' => $response->getContent()]);
        });
    }

    /**
     * Push handler.
     *
     * @param \Closure|string|object $handler
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function push($handler): void
    {
        if (is_string($handler)) {
            $handler = function ($payload) use ($handler) {
                return (new $handler($this->app))->__invoke($payload);
            };
        }

        if (!is_callable($handler)) {
            throw new InvalidArgumentException('Invalid handler');
        }

        $this->handlers[] = $handler;
    }

    /**
     * Get request payload.
     *
     * @return array
     */
    public function getPayload(): array
    {
        $content = $this->app['request']->getContent();
        try {
            $payload = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            $result = $this->app['encryptor']->decrypt(
                $payload['encrypt'], $this->app['request']->get('signature'), $this->app['request']->get('nonce'), $this->app['request']->get('timestamp')
            );

            return json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }
}
