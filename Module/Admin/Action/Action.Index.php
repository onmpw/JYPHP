<?php
namespace Admin\Action;
use Common\Action\CommonAction;
use News\Model\NewsModel;
use Onlinebid\Model\BidsModel;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Exceptions\ConnectException;

class IndexAction extends CommonAction{
    public function index(){
        $sql = "select * from users";
        $mod = new NewsModel();
        $res = $mod->select_sql($sql);
        $logger = new Logger('my_logger');
        var_dump($logger);
//        throw new ConnectException("lianjiecuowu");
        return "test";

    }
    public function login(){
        $this->display();
    }
    
    public function addnews(){
        $this->display();
    }
    
    public function addarticle(){
        $this->display();
    }
    public function articlelist(){
        $currpage = \Common::get('p') === false ? 1 : \Common::get('p');
        $where = '';
        $this->assign('search','');
        if(\Common::get('search') !== false){
            $this->assign('search','/search/'.\Common::get('search'));
            $where = "where title like '%".urldecode(\Common::get('search'))."%'";
        }
        $sql = "select count(*) from news ".$where;
        $mod = new NewsModel();
        $res = $mod->select_sql($sql)[0];
        $totalnum = $res['count(*)'];
        $everypnum = 20;
        $totalpage = ceil($totalnum/$everypnum);
        if($currpage <= 0) $currpage = 1;
        elseif($currpage > $totalpage) $currpage = $totalpage;
        $this->assign('totalpage',$totalpage);
        $this->assign('currpage',$currpage);
        $sql = "select id,title,publictime from news ".$where;
        $sql .= " order by id desc limit ".($currpage-1)*$everypnum.",".$everypnum;
        $res = $mod->select_sql($sql);
        $this->assign("artlist",$res);
        $this->display();
    }
    public function notice(){
        $currpage = \Common::get('p') === false ? 1 : \Common::get('p');
        $where = 'where n.iseffective="Y" ';
        $this->assign('search','');
        if(\Common::get('search') !== false){
            $this->assign('search','/search/'.\Common::get('search'));
            $where .= " and bidname like '%".urldecode(\Common::get('search'))."%'";
        }
        $sql = "select count(*) from bids b left join bidnotice n on b.id=n.bidid ".$where;
        $mod = new BidsModel();
        $res = $mod->select_sql($sql)[0];
        $totalnum = $res['count(*)'];
        $everypnum = 20;
        $totalpage = ceil($totalnum/$everypnum);
        if($currpage <= 0) $currpage = 1;
        elseif($currpage > $totalpage) $currpage = $totalpage;
        $this->assign('totalpage',$totalpage);
        $this->assign('currpage',$currpage);
        $sql = "select b.id,bidname,addtime,bidnoticename from bids b left join bidnotice n on n.bidid=b.id ".$where;
        $sql .= " order by b.id desc limit ".($currpage-1)*$everypnum.",".$everypnum;
        $res = $mod->select_sql($sql);
        $this->assign("bidlist",$res);
        $this->display();
    }
    public function addarticle_do(){
        $con = \Common::post('content');
        $mod = new NewsModel();
        $title = \Common::post('title');
        $description = \Common::post('description');
        $thumbimage = \Common::post('thumbimage');
        $isshow = \Common::post('isshow');
        $content = $con;
        $publictime = time();
        $sql = "insert into news (title,description,content,publictime,thumbimage,isshow) values('{$title}','{$description}','{$content}','".time()."','{$thumbimage}',{$isshow})";
        $res = $mod->sql($sql);
        if($res){
            $id = $mod->lastInsId();
            /* $par = array(
                'message'=>$title,
                'address'=>"/News/News/newscontent/nid/{$id}"
            );
            $str = json_encode($par);
            $ch=curl_init();
            curl_setopt($ch,CURLOPT_URL,\Common::C('PUSH_URL'));
            curl_setopt($ch,CURLOPT_POST,1);
            //         curl_setopt($ch,CURLOPT_POSTFIELDS,"msg={$res['cname']}群:有新消息&token=".implode(',',$mtokens));
            curl_setopt($ch,CURLOPT_POSTFIELDS,"msg=".$str);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data=curl_exec($ch);
            curl_close($ch); */
//             if($data == 's'){
                header("Location:/Admin/Index/articlelist");
//             }
        }
           header("Location:/Admin/Index/articlelist");
//         var_dump($data);
//         var_dump($mod->lastInsId());
    }
    
    public function push(){
        $par = array(
            'message'=>"test",
            'add'=>"/Chat/Group/chat/tid/3",
        );
        $str = json_encode($par);
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,\Common::C('PUSH_URL')."ByToken");
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,"msg=".$str."&token=supplier_F0:25:B7:8F:F9:A4,supplier_58:1f:28:3d:07:56");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $con = curl_exec($ch);
        curl_close($ch);
        var_dump($con);
    }
    
    public function editarticle(){
        $id = intval(\Common::get('aid'));
        $mod = new NewsModel();
        $sql = "select id,title,description des,content,thumbimage,isshow from news where id={$id}";
        $res = $mod->select_sql($sql)[0];
        $this->assign('article',$res);
        $this->display();
    }
    
    public function editarticle_do(){
        $id = intval(\Common::post('aid'));
        $content = "content='".\Common::post('content')."' ";
        $isshow = "isshow=".\Common::post('isshow');
        $mod = new NewsModel();
        $title = "title='".\Common::post('title')."' ";
        $description = "description='".\Common::post('description')."' ";
        $thumbimage = !empty(\Common::post('thumbimage'))?" ,thumbimage='".\Common::post('thumbimage')."' ":'';
        $sql = "update news set {$title},{$isshow},{$description},{$content}{$thumbimage} where id={$id}";
        $res = $mod->sql($sql);
        if($res){
            header("Location:/Admin/Index/articlelist");
        }else{
            echo "<script>history.go(-1)</script>";
        }
        
    }
    
    public function delarticle(){
        $id = intval(\Common::get('aid'));
        $mod = new NewsModel();
        $sql = "delete from news where id={$id}";
        $res = $mod->sql($sql);
        if($res){
            header("Location:/Admin/Index/articlelist");
        }else{
            header("Location:/Admin/Index/articlelist");
        }
    }
    
    public function delbids(){
        $id = intval(\Common::get('bid'));
        $mod = new BidsModel();
        $sql = "delete from bids where id={$id}";
        $res = $mod->sql($sql);
        if ($res) {
            header("Location:/Admin/Index/notice");
        } else {
            header("Location:/Admin/Index/notice");
        }
    } 
    
    public function shownews(){
        $mod = new NewsModel();
        $sql = "select content from news";
        $res = $mod->select_sql($sql);
        echo "<html><head></head><body>".stripcslashes($res[0]['content'])."</body></html>";
    }
    
    public function handle_img(){
       $file = $this->UploadImg();
       file_put_contents('/tmp/test.txt', $file);
       echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction(".\Common::get('CKEditorFuncNum').",'".$file."')</script>";
    }
}