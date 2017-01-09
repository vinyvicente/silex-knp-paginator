<?php

namespace Silex\Knp;

use Knp\Bundle\PaginatorBundle\Helper\Processor;
use Knp\Bundle\PaginatorBundle\Subscriber\SlidingPaginationSubscriber;
use Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension;
use Knp\Component\Pager\Event\Subscriber\Filtration\FiltrationSubscriber;
use Knp\Component\Pager\Event\Subscriber\Paginate\PaginationSubscriber;
use Knp\Component\Pager\Event\Subscriber\Sortable\SortableSubscriber;
use Knp\Component\Pager\Paginator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Bridge\Twig\Extension\RoutingExtension;

/**
 * Class PaginatorProvider
 * @package Silex\Knp
 */
class PaginatorProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function register(Container $app)
    {
        $app['locale'] = 'en';

        if (!isset($app['twig'])) {
            $app->register(new TwigServiceProvider());
        }

        if (!isset($app['translator'])) {
            $app->register(new TranslationServiceProvider());
        }

        $app->extend('twig', function($twig, $app) {
            $processor = new Processor($app['url_generator'], $app['translator']);

            $twig->addExtension(new PaginationExtension($processor));
            $twig->addExtension(new RoutingExtension($app['url_generator']));
            return $twig;
        });

        $app['knp_paginator.path']          = __DIR__ . '/../../vendor/knplabs/knp-paginator-bundle';
        $app['knp_paginator.limits']        = [2, 5, 10, 25, 50, 100, 200, 500];
        $app['knp_paginator.options']       = [];
        $app['knp_paginator.options_fixer'] = function () use ($app) {
            $app['knp_paginator.options'] = array_replace_recursive(
                [
                    'default_options' => [
                        'sort_field_name' => 'sort',
                        'sort_direction_name' => 'direction',
                        'filter_field_name' => 'filterField',
                        'filter_value_name' => 'filterValue',
                        'page_name' => 'page',
                        'distinct' => true,
                    ],
                    'template' => [
                        'pagination' => '@knp_paginator_bundle/sliding.html.twig',
                        'filtration' => '@knp_paginator_bundle/filtration.html.twig',
                        'sortable' => '@knp_paginator_bundle/sortable_link.html.twig',
                    ],
                    'page_range' => 5,
                ],
                $app['knp_paginator.options']
            );
        };

        $app['knp_paginator'] = function () use ($app) {
            $views = rtrim($app['knp_paginator.path'], '/').'/Resources/views/Pagination';

            /** @var \Twig_Loader_Chain $loader */
            $loader = $app['twig.loader'];
            $loader->addLoader(new \Twig_Loader_Filesystem($views, 'knp_paginator_bundle'));

            // Fix options
            $app['knp_paginator.options_fixer'];

            // Create paginator
            $paginator = new Paginator($app['dispatcher']);

            $options = [
                    'pageParameterName' => $app['knp_paginator.options']['default_options']['page_name'],
                    'sortFieldParameterName' => $app['knp_paginator.options']['default_options']['sort_field_name'],
                    'sortDirectionParameterName' => $app['knp_paginator.options']['default_options']['sort_direction_name'],
                    'filterFieldParameterName' => $app['knp_paginator.options']['default_options']['filter_field_name'],
                    'filterValueParameterName' => $app['knp_paginator.options']['default_options']['filter_value_name'],
                    'distinct' => $app['knp_paginator.options']['default_options']['distinct'],
                ];

            $paginator->setDefaultPaginatorOptions($options);

            return $paginator;
        };

        $app['knp_paginator.pagination_subscriber'] = function () {
            return new PaginationSubscriber();
        };

        $app['knp_paginator.sortable_subscriber'] = function () {
            return new SortableSubscriber();
        };

        $app['knp_paginator.filtration_subscriber'] = function () {
            return new FiltrationSubscriber();
        };

        $app['knp_paginator.sliding_pagination_subscriber'] = function () use ($app) {
            $app['knp_paginator.options_fixer'];

            return new SlidingPaginationSubscriber(
                [
                    'defaultPaginationTemplate' => $app['knp_paginator.options']['template']['pagination'],
                    'defaultSortableTemplate' => $app['knp_paginator.options']['template']['sortable'],
                    'defaultFiltrationTemplate' => $app['knp_paginator.options']['template']['filtration'],
                    'defaultPageRange' => $app['knp_paginator.options']['page_range'],
                ]
            );
        };
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber($app['knp_paginator.pagination_subscriber']);
        $app['dispatcher']->addSubscriber($app['knp_paginator.sortable_subscriber']);
        $app['dispatcher']->addSubscriber($app['knp_paginator.filtration_subscriber']);
        $app['dispatcher']->addSubscriber($app['knp_paginator.sliding_pagination_subscriber']);
        $app['dispatcher']->addListener(
            KernelEvents::REQUEST,
            [$app['knp_paginator.sliding_pagination_subscriber'], 'onKernelRequest']
        );
    }
}