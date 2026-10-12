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
$routes->group('tasks', ['filter' => 'auth'], static function ($routes) {
    $routes->get('new', 'Tasks::new');
    $routes->post('create', 'Tasks::create');
    $routes->get('(:num)/edit', 'Tasks::edit/$1');
    $routes->post('(:num)/update', 'Tasks::update/$1');
    $routes->post('(:num)/archive', 'Tasks::archive/$1');
});
