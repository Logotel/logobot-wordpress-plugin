<?php

namespace Logotel\LogobotWp\Helper;

use Exception;
use Logotel\Logobot\Manager;

class LogobotHelper {
    public static function generateJWT($private_key_path, $license, $sessionId) {
        try {
            $jwt = Manager::jwt()
            ->setKeyFromFile($private_key_path)
            ->setLicense($license)
            ->setEmail($sessionId . '@logotel.it')
            ->setIdentifier($sessionId)
            ->setPermissions(['public'])
            ->generate();
            return $jwt;
        } catch (Exception $e) {
            return '';
        }
        
    }
}