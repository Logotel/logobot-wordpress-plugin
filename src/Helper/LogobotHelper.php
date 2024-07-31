<?php

namespace Logotel\LogobotWp\Helper;

use Exception;
use Logotel\Logobot\Manager;

class LogobotHelper {
    public static function generateJWT($private_key, $license, $sessionId) {
        try {
            $jwt = Manager::jwt()
            ->setKey($private_key)
            ->setLicense($license)
            ->setEmail($sessionId . '@logotel.it')
            ->setIdentifier($sessionId)
            ->setPermissions(['all'])
            ->generate();
            return $jwt;
        } catch (Exception $e) {
            return '';
        }
        
    }
}