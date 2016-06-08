<?php
/**
 * ============
 * 自定义分页类
 * ============
 */
namespace Think;

class MyPage{
    
    public $startRow;   //取数据的时候的起始行数
    public $totalRows;   //总记录数
    public $everyPageNum = 6;  //每一次显示的页码的数量
    public $everyPageRows;  //每页显示的行数
    public $totalPageNum;  //总页码数
    public $parameter;   //分页跳转时候带的参数
    
    private $pageStyle;  //分页样式
    private $url;   //链接
    private $currPage = 1; //当前页 默认为第一页
    private $p = 'p';   //分页参数名称
    
    public function __construct($totalRows,$everyPageRows=10,$parameter=array()){
        \Common::C('VAR_PAGE')  &&  $this->p =   \Common::C('VAR_PAGE');  //设置分页参数名称
        $this->totalRows    =   $totalRows;  //设置总记录数
        $this->everyPageRows =   $everyPageRows;  //设置每页显示的行数
        $this->parameter    =   empty($parameter) ? $_GET : $parameter;
        $this->totalPageNum =   ceil($this->totalRows / $this->everyPageRows);  //计算总页码数
        $this->everyPageNum =   $this->everyPageNum < $this->totalPageNum ? $this->everyPageNum : $this->totalPageNum;
        //判断是否有当前页传过来 如果没有 则当前页设置为第一页
        $this->currPage     =   empty($_GET[$this->p]) ? 1 : intval($_GET[$this->p]);
        //判断传过来的当前页的值是否比0小或者等于0 如果是的话 则当前页设置为1
        $this->currPage     =   $this->currPage > 0 ? $this->currPage : 1;
        $this->currPage     =   $this->currPage <= $this->totalPageNum ? $this->currPage : $this->totalPageNum;
        $this->startRow     =   $this->everyPageRows * ($this->currPage - 1);
    }
    
    /**
     * 生成真实的url链接
     * @param unknown $pagenum
     * @return mixed
     */
    public function url($pagenum){
        //将url中的临时变量替换为真实值
        return str_replace(urlencode("[CURPAGE]"),$pagenum,$this->url);
        
    }
    
    /**
     * 常规分页 页码列表形式
     * @return string
     */
    private function pageStyle_1(){
        $drannum = ceil($this->everyPageNum / 2 );
        $start = ($this->currPage-$drannum) <= 0 ? 1 : ($this->currPage-$drannum+1);
        $end = (($start+$this->everyPageNum)-1) > $this->totalPageNum ? $this->totalPageNum : ($start+$this->everyPageNum)-1;
        
        //$start = $end > $this->totalPageNum ? $end-$this->everyPageNum+1 : $start;
        $start = $end - $this->everyPageNum + 1;
        $page="<ul>";
        /*
         * 上一页
         */
        $pre = $this->currPage-1 > 0 ? "<li><a href='".$this->url($this->currPage-1)."'><</a></li>" : '';
        /*
         * 下一页
         */
        $next = $this->currPage+1 <= $this->totalPageNum ? "<li><a href='".$this->url($this->currPage+1)."'>></a></li>" : '';
        /*
         * 首页
         */
        $first = $this->currPage-1 > 0 ? "<li><a href='".$this->url(1)."'><<</a></li>" : '';
        /*
         * 尾页
         */
        $last = $this->currPage+1 <= $this->totalPageNum ? "<li><a href='".$this->url($this->totalPageNum)."'>>></a></li>" : '';
        
        $list='';
        for($i = $start;$i <= $end;$i++){
            if($i == $this->currPage){
                $list .= "<li><a class='active'>".$i."</a></li>";
            }else{
                $list .= "<li><a href='".$this->url($i)."'>{$i}</a></li>";
            }
        }
        $page .= $first.$pre.$list.$next.$last;
        $page.="</ul>";
        return $page;
    }
    
    private function pageStyle_2(){
        $page = "<ul>";
        /*
         * 上一页
         */
        $pre = $this->currPage-1 > 0 ? "<li><a href='".$this->url($this->currPage-1)."'><</a></li>" : "<li><a class='active'><</a></li>";
        /*
         * 下一页
         */
        $next = $this->currPage+1 <= $this->totalPageNum ? "<li><a href='".$this->url($this->currPage+1)."'>></a></li>" : "<li><a class='active'>></a></li>";
        
        $info = "<li><span> {$this->currPage} / {$this->totalPageNum} 页</span></li>";
        
        $page .= $pre.$info.$next;
        $page .= "</ul>";
        
        return $page;
    }
    
    private function pageStyle_3(){
        $page = "<ul>";
        /*
         * 上一页
         */
        $pre = $this->currPage-1 > 0 ? "<li><a href='".$this->url($this->currPage-1)."'><</a></li>" : "<li><a class='active'><</a></li>";
        /*
         * 下一页
         */
        $next = $this->currPage+1 <= $this->totalPageNum ? "<li><a href='".$this->url($this->currPage+1)."'>></a></li>" : "<li><a class='active'>></a></li>";
        
        $info = "<li><span> {$this->currPage} / {$this->totalPageNum} 页</span></li>";
        
        $input = "<li><span>跳转到</span><input type='text' name='pagenum' id='pagenum' onkeydown='javascript:if(event.keyCode==13){var num=document.getElementById(\"pagenum\").value;window.location.href=\"".$this->url("\"+num+\"")."\"}' /><span>/ {$this->totalPageNum} 页</span></li>";
        
        $page .= $pre.$info.$next.$input;
        $page .= "</ul>";
        
        return $page;
        
    }
    
    /**
     * 显示分页样式
     * @param number $pagestyle
     * @return string|mixed
     */
    public function show_page($pagestyle=1){
        
        if($this->totalRows == 0) return '';
        
        $func="pageStyle";
        
        $this->parameter[$this->p] = '[CURPAGE]';
        
        $this->url = U(ACTION_NAME,$this->parameter);
        
        return call_user_func(array($this,$func."_".$pagestyle));
    }
    
}