<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('tasks', 'Tasks::index');
$routes->match(['get', 'post'], 'login', 'Auth::login', ['as' => 'login']);
$routes->post('logout', 'Auth::logout', ['filter' => 'auth']);
$routes->get('profile', 'Profile::index', ['filter' => 'auth']);
$routes->get('about', 'About::index');
