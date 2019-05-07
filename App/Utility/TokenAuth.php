<?php


namespace App\Utility;


use Exception;

class TokenAuth
{

    static  function validate($routes, $headers, &$controller)
    {

        var_dump($controller);
        if (sizeof($routes) == 0) {
            if (TokenAuth::tokenIsExist($headers) === true) {
                $jwt = TokenAuth::jwtIsExist($headers);
                // 验证 token
                if ($jwt) {
                    try {
                        $token = Authorization::validateToken($jwt);
                    } catch (Exception $ex) {
                        $controller->writeJson(401, "验证token失败", 'the token is unauthorized');
                    }
                } else {
                    $controller->writeJson(400, "Bearer 缺少 token", 'the token is unauthorized');
                }
            } else {
                $controller->writeJson(400, "缺少token", 'the token is unauthorized');
            }
        }
    }

    // 验证 header 是否含有 Authorization
    static  function tokenIsExist($headers = array())
    {
        return (
            array_key_exists('Authorization', $headers) &&
            !empty($headers['Authorization'])
        );
    }

    // 取出 Bearer 的 token 值
    static  function jwtIsExist($headers)
    {
        list($jwt) = sscanf($headers['Authorization'], 'Bearer %s');
        return $jwt;
    }

}