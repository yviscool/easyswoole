<?php


namespace App\Utility;

use EasySwoole\EasySwoole\Config;
use \Firebase\JWT\JWT;

class Authorization
{

    public static function validateToken($token)
    {
        $config = Config::getInstance();
        $jwtConfig = $config->getConf("JWT");
        $key = $jwtConfig['jwt_key'];
        $algorithm = $jwtConfig['jwt_algorithm'];
        return JWT::decode($token, $key, array($algorithm));
    }

    public static function generateToken($data)
    {
        $config = Config::getInstance();
        $jwtConfig = $config->getConf("JWT");
        $key = $jwtConfig['jwt_key'];
        return JWT::encode($data, $key);
    }

}