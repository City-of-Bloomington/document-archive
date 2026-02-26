<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Role\GenericRole as Role;
use Laminas\Permissions\Acl\Resource\GenericResource as Resource;

$ACL = new Acl();
$ACL->addRole(new Role('Anonymous'))
    ->addRole(new Role('Staff'))
    ->addRole(new Role('Administrator'));

/**
 * Create resources for all the routes
 */
foreach ($ROUTES->getMap()->getRoutes() as $r) {
    $p = pathinfo($r->name);
    $resource = $p['filename'];
    if (!$ACL->hasResource($resource)) {
         $ACL->addResource(new Resource($resource));
    }
}

/**
 * Assign permissions to the resources
 */
// Permissions for unauthenticated browsing
$ACL->allow(null,  'home');

$ACL->allow('Staff');

// Administrator is allowed access to everything
$ACL->allow('Administrator');
