<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);

$ROUTES = new \Aura\Router\RouterContainer(BASE_URI);
$map    = $ROUTES->getMap();
$map->tokens(['id' => '\d+']);

$map->attach('home.', '/', function ($r) {
    $r->get('login',    'login',         Web\Auth\Login\Controller::class);
    $r->get('logout',   'logout',        Web\Auth\Logout\Controller::class);
    $r->get('download', '{id}/download', Web\Files\Download\Controller::class);
    $r->get ('info',    '{id}',          Web\Files\Info\Controller::class);
    $r->get ('index',   '',              Web\Files\List\Controller::class);
});

$map->attach('files.', '/files', function ($r) {
    $r->get('add',    '/add',         Web\Files\Add\Controller::class   )->allows(['POST']);
    $r->get('update', '/{id}/update', Web\Files\Update\Controller::class)->allows(['POST']);
    $r->get('delete', '/{id}/delete', Web\Files\Delete\Controller::class);
});
