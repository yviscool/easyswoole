<?php


namespace App\HttpController;

use App\Utility\Authorization;
use App\Utility\PageInfo;

use Underscore\Types\Arrays;

class User extends Base
{

    function list()
    {
        $pageNum = 1;
        $pageSize = 10;
        $sort = 'id';
        $order = 'DESC';

        $data = new PageInfo($this->getDbConnection(),'liyang_test.user',$pageNum, $pageSize, $sort, $order);

        $this->writeJson(200, $data, 'success');
    }

//
    function login()
    {

        $userId = $this->request()->getQueryParam('id');

        $user = $this->db
            ->where('id', $userId)
            ->get("liyang_test.user");

        if (empty($user)) {
            return $this->writeJson(404, "找不到该用户", 'fail');
        }

        $token = Authorization::generateToken($user);
        $user['token'] = $token;

//        $jwt = Authorization::validateToken($token);

        $this->writeJson(200, $user, 'success');
    }
}