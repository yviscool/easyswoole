<?php


namespace App\Utility;

class PageInfo
{
    //当前页
    public  $pageNum;
    //每页的数量
    public $pageSize;
    //当前页的数量
    public $size;
    //排序
    public $orderBy;

    //由于startRow和endRow不常用，这里说个具体的用法
    //可以在页面中"显示startRow到endRow 共size条数据"

    //当前页面第一个元素在数据库中的行号
    public $startRow;
    //当前页面最后一个元素在数据库中的行号
    public $endRow;
    //总记录数
    public $total;
    //总页数
    public $pages;
    //结果集
    public $list;

    //第一页
    public $firstPage;
    //前一页
    public $prePage;
    //下一页
    public $nextPage;
    //最后一页
    public $lastPage;

    //是否为第一页
    public $isFirstPage = false;
    //是否为最后一页
    public $isLastPage = false;
    //是否有前一页
    public $hasPreviousPage = false;
    //是否有下一页
    public $hasNextPage = false;
    //导航页码数
    public $navigatePages;
    //所有导航页号
    public $navigatepageNums;

    public function __construct()
    {
        $args = func_get_args(); //获取构造函数中的参数
        $i = count($args);
        if (method_exists($this, $f = '__construct' . $i)) {
            call_user_func_array(array($this, $f), $args);
        }
    }

    // 构造函数重载 　数据量多大后　加快性能的　分页
    function __construct6($db, $dbname, $pageNum, $pageSize, $sort, $order)
    {

        // 计算总共数据量
        $list = $db->withTotalCount()->orderBy($sort, $order)->get($dbname, [($pageNum - 1) * $pageSize, $pageSize], '*');

        $total  = $db->getTotalCount();

        $this->pageNum = $pageNum;
        $this->pageSize = $pageSize;
        $this->orderBy = $sort . " " . $order;

        // 总数据量
        $this->total = $total;

        // 总共分页　页数
        $this->pages = (int)($total / $pageSize + (($total % $pageSize == 0) ? 0 : 1));

        $this->list = $list;

        // 当前页的数量
        $this->size = count($list);

        $this->startRow = 0;
        $this->endRow = count($list) > 0 ? count($list) - 1 : 0;

        $this->navigatePages = 8;
        //计算导航页
        $this->calcNavigatepageNums();
        //计算前后页，第一页，最后一页
        $this->calcPage();
        //判断页面边界
        $this->judgePageBoudary();
        $this->navigatePages = sizeof($this->navigatepageNums);
    }

    private function calcNavigatepageNums()
    {

        //当总页数小于或等于导航页码数时
        if ($this->pages <= $this->navigatePages) {
            $this->navigatepageNums = array();
            for ($i = 0; $i < $this->pages; $i++) {
                $this->navigatepageNums[$i] = $i + 1;
            }
        } else { // 当总页数大于导航页码数时
            $this->navigatepageNums = array();
            $startNum = $this->pageNum - $this->navigatePages / 2;
            $endNum = $this->pageNum + $this->navigatePages / 2;

            if ($startNum < 1) {
                $startNum = 1;
                // 最前面 navigatepages 页
                for ($i = 0; $i < $this->navigatePages; $i++) {
                    $this->navigatepageNums[$i] = $startNum++;
                }
            } else if ($endNum > $this->pages) {
                $endNum = $this->pages;
                // 最后 navigatepages 页
                for ($i = 0; $i < $this->navigatePages; $i++) {
                    $this->navigatepageNums[$i] = $endNum--;
                }
            } else {
                // 所有中间页
                for ($i = 0; $i < $this->navigatePages; $i++) {
                    $this->navigatepageNums[$i] = $startNum++;
                }
            }
        }
    }

    // 计算前后面, 第一页，最后一页
    private function calcPage()
    {

        if (!empty($this->navigatepageNums) and count($this->navigatepageNums) > 0) {
            $this->firstPage = $this->navigatepageNums[0];
            $this->lastPage = $this->navigatepageNums[count($this->navigatepageNums) - 1];

            if ($this->pageNum > 1) {
                $this->prePage = $this->pageNum - 1;
            }
            if ($this->pageNum < $this->pages) {
                $this->nextPage = $this->pageNum + 1;
            }
        }

    }

    /**
     * 判定页面边界
     */
    private function judgePageBoudary()
    {
        $this->isFirstPage = $this->pageNum == 1;
        $this->isLastPage = $this->pageNum == $this->pages;
        $this->hasPreviousPage = $this->pageNum > 1;
        $this->hasNextPage = $this->pageNum < $this->pages;
    }

    public  function  getList(){
        return $this->list;
    }


    public  function  setList($list){
        return $this->list=$list;
    }
}
