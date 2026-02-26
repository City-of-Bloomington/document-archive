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
    $r->get ('info',  '{id}', Web\Files\Info\Controller::class);
    $r->get ('index', '',     Web\Files\List\Controller::class);
});
