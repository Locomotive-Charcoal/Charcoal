<?php

namespace Charcoal\App\Services;

// From PSR-7
use Charcoal\Factory\GenericResolver;
use Psr\Http\Message\UriInterface;
// From Pimple
use Pimple\ServiceProviderInterface;
use Pimple\Container;
// From Slim
use Slim\Http\Uri;
// From 'league/climate'
use League\CLImate\CLImate;
// From Mustache
use Mustache_LambdaHelper as LambdaHelper;
use Charcoal\Factory\GenericFactory as Factory;
use Charcoal\Cache\ServiceProvider\CacheServiceProvider;
use Charcoal\Translator\ServiceProvider\TranslatorServiceProvider;
use Charcoal\App\AppConfig;
use Charcoal\App\Action\ActionInterface;
use Charcoal\App\Route\ActionRoute;
use Charcoal\App\Route\RouteInterface;
use Charcoal\App\Route\TemplateRoute;
use Charcoal\App\Script\ScriptInterface;
use Charcoal\App\Template\TemplateInterface;
use Charcoal\App\Template\TemplateBuilder;
use Charcoal\App\Template\WidgetInterface;
use Charcoal\App\Template\WidgetBuilder;
use Charcoal\View\Twig\DebugHelpers as TwigDebugHelpers;
use Charcoal\View\Twig\HelpersInterface as TwigHelpersInterface;
use Charcoal\View\Twig\UrlHelpers as TwigUrlHelpers;
use Charcoal\View\ViewServiceProvider;
use Slim\Psr7\Factory\UriFactory;

/**
 * Application Service Provider
 *
 * Configures Charcoal and Slim and provides various Charcoal services to a container.
 *
 * ## Services
 * - `logger` `\Psr\Log\Logger`
 *
 * ## Helpers
 * - `logger/config` `\Charcoal\App\Config\LoggerConfig`
 *
 * ## Requirements / Dependencies
 * - `config` A `ConfigInterface` must have been previously registered on the container.
 */
class AppServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param  Container $container A service container.
     * @return void
     */
    public function register(Container $container)
    {
        $container->register(new HandlersProvider());
        $container->register(new ControllersProvider());
        $container->register(new CacheServiceProvider());
        $container->register(new DatabaseServiceProvider());
        $container->register(new FilesystemServiceProvider());
        $container->register(new LoggerServiceProvider());
        $container->register(new ScriptServiceProvider());
        $container->register(new TranslatorServiceProvider());
        $container->register(new ViewServiceProvider());

        $this->registerKernelServices($container);
        $this->registerMiddlewareServices($container);
        $this->registerRequestControllerServices($container);
        $this->registerViewServices($container);
    }

    /**
     * @param  Container $container A service container.
     * @return void
     */
    protected function registerKernelServices(Container $container)
    {
        if (!isset($container['config'])) {
            $container['config'] = new AppConfig();
        }

        if (!isset($container['debug'])) {
            /**
             * Application Debug Mode
             *
             * @param  Container $container A service container.
             * @return boolean
             */
            $container['debug'] = function (Container $container) {
                if (isset($container['config']['debug'])) {
                    return !!$container['config']['debug'];
                }

                return false;
            };
        }

        if (!isset($container['base-url'])) {
            /**
             * Base URL as a PSR-7 UriInterface object for the current request
             * or the Charcoal application.
             *
             * @param  Container $container A service container.
             * @return \Psr\Http\Message\UriInterface
             */
            $container['base-url'] = function (Container $container) {
                if (isset($container['config']['base_url'])) {
                    $baseUrl = $container['config']['base_url'];
                } else {
                    $baseUrl = $container['request']->getUri()->getBaseUrl();
                }

                $baseUrl = (new UriFactory())->createUri($baseUrl)->withUserInfo('');

                /** Fix the base path */
                $path = $baseUrl->getPath();
                if ($path) {
                    //$baseUrl = $baseUrl->setBasePath($path)->withPath('');
                }

                return $baseUrl;
            };
        }
    }

    /**
     * @param  Container $container A service container.
     * @return void
     */
    protected function registerMiddlewareServices(Container $container)
    {
        /**
         * @param  Container $container A service container.
         * @return IpMiddleware
         */
        $container['middlewares/charcoal/app/middleware/ip'] = function (container $container) {
            $wareConfig = $container['config']['middlewares']['charcoal/app/middleware/ip'];
            return new IpMiddleware($wareConfig);
        };
    }

    /**
     * @param  Container $container A service container.
     * @return void
     */
    protected function registerRequestControllerServices(Container $container)
    {
        /**
         * The Action Factory service is used to instanciate new actions.
         *
         * - Actions are `ActionInterface` and must be suffixed with `Action`.
         * - The container is passed to the created action constructor, which will call `setDependencies()`.
         *
         * @param  Container $container A service container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['action/factory'] = function (Container $container) {
            return new Factory([
                'base_class'       => ActionInterface::class,
                'resolver_options' => [
                    'suffix' => 'Action',
                ],
                'arguments' => [
                    [
                        'container' => $container,
                        'logger'    => $container['logger'],
                    ],
                ],
            ]);
        };

        /**
         * The Template Factory service is used to instanciate new templates.
         *
         * - Templates are `TemplateInterface` and must be suffixed with `Template`.
         * - The container is passed to the created template constructor, which will call `setDependencies()`.
         *
         * @param  Container $container A service container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['template/factory'] = function (Container $container) {
            return new Factory([
                'base_class'       => TemplateInterface::class,
                'resolver_options' => [
                    'suffix' => 'Template',
                ],
                'arguments' => [
                    [
                        'container' => $container,
                        'logger'    => $container['logger'],
                    ],
                ],
            ]);
        };

        /**
         * The Widget Factory service is used to instanciate new widgets.
         *
         * - Widgets are `WidgetInterface` and must be suffixed with `Widget`.
         * - The container is passed to the created widget constructor, which will call `setDependencies()`.
         *
         * @param  Container $container A service container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['widget/factory'] = function (Container $container) {
            return new Factory([
                'base_class'       => WidgetInterface::class,
                'resolver_options' => [
                    'suffix' => 'Widget',
                ],
                'arguments' => [
                    [
                        'container' => $container,
                        'logger'    => $container['logger'],
                    ],
                ],
            ]);
        };

        /**
         * @param  Container $container A service container.
         * @return WidgetBuilder
         */
        $container['widget/builder'] = function (Container $container) {
            return new WidgetBuilder($container['widget/factory'], $container);
        };
    }
    /**
     * Add helpers to the view services.
     *
     * @param  Container $container A service container.
     * @return void
     */
    protected function registerViewServices(Container $container)
    {
        $this->registerMustacheHelpersServices($container);
        $this->registerTwigHelpersServices($container);
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerMustacheHelpersServices(Container $container): void
    {
        if (!isset($container['view/mustache/helpers'])) {
            $container['view/mustache/helpers'] = function () {
                return [];
            };
        }

        /**
         * Extend helpers for the Mustache Engine
         *
         * @return array
         */
        $container->extend('view/mustache/helpers', function (array $helpers, Container $container) {
            $baseUrl = $container['base-url'];
            $urls = [
                /**
                 * Application debug mode.
                 *
                 * @return boolean
                 */
                'debug' => ($container['config']['debug'] ?? false),
                /**
                 * Retrieve the base URI of the project.
                 *
                 * @return UriInterface|null
                 */
                'siteUrl' => $baseUrl,
                /**
                 * Alias of "siteUrl"
                 *
                 * @return UriInterface|null
                 */
                'baseUrl' => $baseUrl,
                /**
                 * Prepend the base URI to the given path.
                 *
                 * @param  string $uri A URI path to wrap.
                 * @return UriInterface|null
                 */
                'withBaseUrl' => function ($uri, LambdaHelper $helper = null) use ($baseUrl) {
                    if ($helper) {
                        $uri = $helper->render($uri);
                    }

                    $uri = strval($uri);
                    if ($uri === '') {
                        $uri = $baseUrl->withPath('');
                    } else {
                        $parts = parse_url($uri);
                        if (!isset($parts['scheme'])) {
                            if (!in_array($uri[0], [ '/', '#', '?' ])) {
                                $path  = isset($parts['path']) ? $parts['path'] : '';
                                $query = isset($parts['query']) ? $parts['query'] : '';
                                $hash  = isset($parts['fragment']) ? $parts['fragment'] : '';

                                $uri = $baseUrl->withPath($path)
                                               ->withQuery($query)
                                               ->withFragment($hash);
                            }
                        }
                    }

                    return $uri;
                },
                'renderContext' => function ($text, LambdaHelper $helper = null) {
                    return $helper->render('{{>' . $helper->render($text) . '}}');
                },
            ];

            return array_merge($helpers, $urls);
        });
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerTwigHelpersServices(Container $container): void
    {
        if (!isset($container['view/twig/helpers'])) {
            $container['view/twig/helpers'] = function () {
                return [];
            };
        }

        /**
         * Url helpers for Twig.
         *
         * @return TwigUrlHelpers
         */
        $container['view/twig/helpers/url'] = function (Container $container): TwigHelpersInterface {
            return new TwigUrlHelpers([
                'baseUrl' => $container['base-url'],
            ]);
        };

        /**
         * Debug helpers for Twig.
         *
         * @return TwigDebugHelpers
         */
        $container['view/twig/helpers/debug'] = function (Container $container): TwigHelpersInterface {
            return new TwigDebugHelpers([
                'debug'  => $container['debug'],
            ]);
        };

        /**
         * Extend global helpers for the Twig Engine.
         *
         * @param  array     $helpers   The Mustache helper collection.
         * @param  Container $container A container instance.
         * @return array
         */
        $container->extend('view/twig/helpers', function (array $helpers, Container $container): array {
            return array_merge(
                $helpers,
                $container['view/twig/helpers/url']->toArray(),
                $container['view/twig/helpers/debug']->toArray(),
            );
        });
    }
}