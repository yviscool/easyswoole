<?php


namespace App\HttpController;

use App\Utility\Authorization;
use App\Utility\PageInfo;
use EasySwoole\Validate\Validate;

use Underscore\Types\Arrays;

class User extends Base
{

    function list()
    {
        $req = $this->request();

        $pageNum = $req->getQueryParam('page_num') ?: 1;
        $pageSize = $req->getQueryParam('page_size') ?: 10;
        $sort = $req->getQueryParam('sort') ?: 'id';
        $order = $req->getQueryParam('order') ?: 'DESC';

        $data = new PageInfo($this->db, 'liyang_test.user', $pageNum, $pageSize, $sort, $order);

        $this->writeJson(200, $data, 'success');
    }

//
    function login()
    {

        $req = $this->request();

        $userId = $req->getQueryParam('id');

        $user = $this->getDb()
            ->where('id', $userId)
            ->get("liyang_test.user");

        if (empty($user)) {
            return $this->writeJson(404, "找不到该用户", 'fail');
        }

        $token = Authorization::generateToken($user);
        $user['token'] = $token;

        $this->writeJson(200, $user, 'success');
    }

    function add()
    {

        $params = $this->request()->getQueryParams();

        $validator = new Validate();

        $validator->addColumn('name')->required('姓名必填')->betweenLen(1, 200, '名字长度只能在1-200之间');
        $validator->addColumn('age')->required('年龄必填');

        if (!$this->validate($validator)) {
            return $this->writeJson(400, $validator->getError()->__toString(), 'fail');
        }

        $name = $params['name'];
        $age = $params['age'];

        $this->writeJson(200, $params, 'success');
    }
}