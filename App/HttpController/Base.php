<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2019/3/18 0018
 * Time: 10:45
 */

namespace App\HttpController;


use App\Utility\Authorization;
use App\Utility\Pool\MysqlObject;
use App\Utility\Pool\MysqlPool;
use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Http\Message\Status;
use Exception;

abstract class Base extends Controller
{

    // 数据库链接
    protected $db;

    // 保护的路由
    protected $protectedRoutes = [];


    function index()
    {
        $this->actionNotFound('index');
    }

    protected function onRequest(?string $action): ?bool
    {
        // 读取配置文件里面，受保护的路由
        $this->protectedRoutes = Config::getInstance()->getConf('TOKEN_PROTECT_ROUTES');
        // 初始化数据库链接
        $this->initialPool();
        // 权限验证
        return $this->validateToken();
    }

    // 初始化 数据库连接池
    protected function initialPool()
    {
        $db = PoolManager::getInstance()->getPool(MysqlPool::class)->getObj(Config::getInstance()->getConf('MYSQL.POOL_TIME_OUT'));
        if ($db) {
            $this->db = $db;
        } else {
            //直接抛给异常处理，不往下
            throw new \Exception('url :' . $this->request()->getUri()->getPath() . ' error,Mysql Pool is Empty');
        }
        return true;
    }

    // 验证 token
    protected function validateToken()
    {
//        Logger::getInstance()->log("validate 执行了");
//        Logger::getInstance()->log($this->protectedRoutes[0]);
        // 当前请求路径
        $currentUri = $this->request()->getUri()->getPath();

        $routes = array_filter($this->protectedRoutes, function ($route) use ($currentUri) {
            return preg_match($route, $currentUri);
        });


        if (sizeof($routes) == 0) {
            $headers = $this->request()->getHeaders();
            if ($this->tokenIsExist($headers) === true) {
                $jwt = $this->jwtIsExist($headers);
                // 验证 token
                if ($jwt) {
                    try {
                        $token = Authorization::validateToken($jwt);
                    } catch (Exception $ex) {
                        $this->writeJson(401, "验证token失败", 'the token is unauthorized');
                        return false;
                    }
                    return true;
                }
                $this->writeJson(400, "Bearer 缺少 token", 'the token is unauthorized');
                return false;
            }

            $this->writeJson(400, "缺少token", 'the token is unauthorized');
            return false;
        }

        return true;

    }

    // 验证 header 是否含有 Authorization
    protected function tokenIsExist($headers = array())
    {
        // 这里居然不是大写的. !!!!  太傻逼了
        return (
            array_key_exists('authorization', $headers) &&
            !empty($headers['authorization'])
        );
    }

    // 取出 Bearer 的 token 值
    protected function jwtIsExist($headers)
    {
        // 这里居然是个数组, !!! 傻逼
        list($jwt) = sscanf($headers['authorization'][0], 'Bearer %s');
        return $jwt;
    }

    // 数据库连接回收
    protected function gc()
    {
        PoolManager::getInstance()->getPool(MysqlPool::class)->recycleObj($this->db);
        parent::gc();
    }

    // 获取数据库链接
    protected function getDb(): MysqlObject
    {
        return $this->db;
    }

    protected function onException(\Throwable $throwable): void
    {
        //拦截错误进日志,使控制器继续运行
        Trigger::getInstance()->throwable($throwable);
        $this->writeJson(Status::CODE_INTERNAL_SERVER_ERROR, null, $throwable->getMessage());
    }

}