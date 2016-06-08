<?php
namespace News\Action;
use Lib\Action;
use News\Model\NewsModel;
class NewsAction extends Action{
    
    public function newslist(){
        $mod = new NewsModel();
        $sql = "select id,title,description des,thumbimage timg,publictime ptime from news where isshow=0";
        $res = $mod->select_sql($sql);
        $this->assign('news',$res);
        $this->display();
    }
    
    public function newscontent(){
        $nid = \Common::get('nid');
        $mod = new NewsModel();
        $sql = "select title,content,publictime ptime,isshow from news where id={$nid}";
        $res = $mod->select_sql($sql)[0];
        $this->assign('news',$res);
        $this->display();
    }
}