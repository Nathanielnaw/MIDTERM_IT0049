<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('tasks', 'Tasks::index');
$routes->match(['get', 'post'], 'login', 'Auth::login', ['as' => 'login']);
$routes->post('logout', 'Auth::logout', ['filter' => 'auth']);
$routes->get('profile', 'Profile::index');
$routes->get('about', 'About::index');
$routes->setAutoRoute(false);
$routes->group('pos', ['filter' => 'staff'], static function ($routes) {
    $routes->get('/', 'PosSales::index');
    $routes->get('sales', 'PosSales::history');
    $routes->post('sales', 'PosSales::create');
    foreach (['products', 'customers', 'staff'] as $resource) {
        $routes->get($resource, 'PosRecords::index/' . $resource);
        $routes->get($resource . '/new', 'PosRecords::form/' . $resource);
        $routes->post($resource . '/create', 'PosRecords::save/' . $resource);
        $routes->get($resource . '/(:num)/edit', 'PosRecords::form/' . $resource . '/$1');
        $routes->post($resource . '/(:num)/update', 'PosRecords::save/' . $resource . '/$1');
        $routes->post($resource . '/(:num)/archive', 'PosRecords::archive/' . $resource . '/$1');
    }
});
$routes->group('tasks', ['filter' => 'auth'], static function ($routes) {
    $routes->get('new', 'Tasks::new');
    $routes->post('create', 'Tasks::create');
    $routes->get('(:num)/edit', 'Tasks::edit/$1');
    $routes->post('(:num)/update', 'Tasks::update/$1');
    $routes->post('(:num)/archive', 'Tasks::archive/$1');
});
