<?php

namespace Logotel\LogobotWp\Helper;
use Logotel\Logobot\Manager;

class LogobotHelper {
    public static function generateJWT($private_key, $license) {
        $jwt = Manager::jwt()
        ->setKey($private_key)
        ->setLicense($license)
        ->setEmail('r.desilva@logotel.it')
        ->setIdentifier('riccardodesilva')
        ->setPermissions(['all'])
        ->generate();
        return $jwt;
    }
}