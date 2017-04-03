# silex-knp-paginator
Plugin KNP Paginator to Silex 2.0

[![Latest Stable Version](https://poser.pugx.org/vinyvicente/silex-knp-paginator/v/stable)](https://packagist.org/packages/vinyvicente/silex-knp-paginator)
[![Total Downloads](https://poser.pugx.org/vinyvicente/silex-knp-paginator/downloads)](https://packagist.org/packages/vinyvicente/silex-knp-paginator)
[![Latest Unstable Version](https://poser.pugx.org/vinyvicente/silex-knp-paginator/v/unstable)](https://packagist.org/packages/vinyvicente/silex-knp-paginator)
[![License](https://poser.pugx.org/vinyvicente/silex-knp-paginator/license)](https://packagist.org/packages/vinyvicente/silex-knp-paginator)
[![Monthly Downloads](https://poser.pugx.org/vinyvicente/silex-knp-paginator/d/monthly)](https://packagist.org/packages/vinyvicente/silex-knp-paginator)
[![composer.lock](https://poser.pugx.org/vinyvicente/silex-knp-paginator/composerlock)](https://packagist.org/packages/vinyvicente/silex-knp-paginator)

## Dependencies

* PHP 7+
* Silex 2.0+
* Twig 2.0+

## How to Use

#### Based on Knp Pagination Bundle

See more: [Docs](https://github.com/KnpLabs/KnpPaginatorBundle)

### Integrating with Silex below

```php
require 'vendor/autoload.php';

use Silex\Application;
use Silex\Knp\PaginatorProvider;
use Symfony\Component\HttpFoundation\Request;

$app = new Application();
$app['debug'] = true;
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views',
));

// needs be after twig register :)
$app->register(new PaginatorProvider());

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
```

### Twig

[Read the Docs](https://github.com/KnpLabs/KnpPaginatorBundle#view)
