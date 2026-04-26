<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/login', 'Auth::index');
$routes->post('/login', 'Auth::attemptLogin');
$routes->get('/logout', 'Auth::logout');

$routes->get('/dashboard', 'Dashboard::index');
$routes->get('/dashboard/admin', 'Dashboard::admin');
$routes->get('/dashboard/admin/users', 'Dashboard::adminUsers');
$routes->post('/dashboard/admin/users', 'Dashboard::createUser');
$routes->post('/dashboard/admin/users/(:num)', 'Dashboard::updateUser/$1');
$routes->delete('/dashboard/admin/users/(:num)', 'Dashboard::deleteUser/$1');
$routes->get('/dashboard/admin/audit-logs', 'Dashboard::adminAuditLogs');
$routes->get('/dashboard/user', 'Dashboard::user');
$routes->get('/dashboard/user/data', 'Dashboard::userDashboardData');
$routes->post('/dashboard/user/bills', 'Dashboard::createBill');
