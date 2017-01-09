<?php

require 'vendor/autoload.php';

use Silex\Application;
use Silex\Knp\PaginatorProvider;
use Symfony\Component\HttpFoundation\Request;

$app = new Application();
$app['debug'] = true;
$app->register(new PaginatorProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views',
));

$app['knp_paginator.options'] = array(
    'default_options' => array(
        'sort_field_name' => 'sort',
        'sort_direction_name' => 'direction',
        'filter_field_name' => 'filterField',
        'filter_value_name' => 'filterValue',
        'page_name' => 'page',
        'distinct' => true,
    ),
    'template' => array(
        'pagination' => '@knp_paginator_bundle/sliding.html.twig',
        'filtration' => '@knp_paginator_bundle/filtration.html.twig',
        'sortable' => '@knp_paginator_bundle/sortable_link.html.twig',
    ),
    'page_range' => 5,
);

$app->get('/', function(Request $request) use ($app) {

    $array = [
        'foo' => ['id' => 'foo'],
        'bar' => ['id' => 'bar'],
        'baz' => ['id' => 'baz'],
        'echo' => ['id' => 'echo'],
        'delta' => ['id' => 'delta'],
    ];

    $currentPage = (!empty($request->get('page'))) ? $request->get('page') : 1;
    $limitPage = 3;

    $sort = $request->get('sort');
    $direction = $request->get('direction', 'asc');

    $knp = $app['knp_paginator'];
    $paginator = $knp->paginate($array, $currentPage, $limitPage);
    $paginator->setCurrentPageNumber($currentPage);
    $paginator->setItemNumberPerPage($limitPage);

    return $app['twig']->render('example.html.twig', ['pagination' => $paginator]);
});

$app->run();
