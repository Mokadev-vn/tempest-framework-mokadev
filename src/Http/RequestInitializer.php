<?php

namespace Tempest\Http;

use Tempest\Interfaces\Container;
use Tempest\Interfaces\MatchingInitializer;
use Tempest\Interfaces\Request;
use Tempest\Interfaces\Server;

final readonly class RequestInitializer implements MatchingInitializer
{
    public function canInitialize(string $className): bool
    {
        $interface = class_implements($className)[Request::class] ?? null;

        return $interface !== null;
    }

    public function initialize(string $className, Container $container): Request
    {
        $server = $container->get(Server::class);

        /** @var Request $request */
        $request = call_user_func_array("{$className}::new", [
            'method' => $server->getMethod(),
            'uri' => $server->getUri(),
            'body' => $server->getBody(),
        ]);

        foreach ($request->getBody() as $key => $value) {
            if (property_exists($request, $key)) {
                $request->$key = $value;
            }
        }

        $container->singleton($className, fn () => $request);

        return $request;
    }
}
