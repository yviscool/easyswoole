<?php


namespace App\HttpController;

use App\Utility\Authorization;


class User extends Base
{

    function list()
    {
        $page=3;
        $page_size=10;
        $users = $this->db->get("liyang_test.user",[($page-1)*$page_size,$page_size],'*');

        $this->writeJson(200,$users, 'success');
    }
//
    function  login()
    {

        $userId = $this->request()->getQueryParam('id');

        $user = $this->db
            ->where('id',$userId)
            ->get("liyang_test.user");

        if (empty($user)){
            return $this->writeJson(404, "找不到该用户" , 'fail');
        }

        $token = Authorization::generateToken($user);
        $user['token'] = $token;

//        $jwt = Authorization::validateToken($token);

        $this->writeJson(200, $user, 'success');
    }
}