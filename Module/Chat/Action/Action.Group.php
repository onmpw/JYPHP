<?php
namespace Chat\Action;
use Common\Action\CommonAction;
use Lib\DB;
use Onlinebid\Model\ContractsModel;
use Chat\Model\TopicModel;
use Onlinebid\Model\UsertopictimeModel;
use Onlinebid\Model\ContracttouserModel;
use User\Model\UserModel;
class GroupAction extends CommonAction{
    /**
     * 我的群
     */
    public function mygroup(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $mod = new ContractsModel();
        $uid = $_SESSION['userid'];
        //查看该用户是否是管理员
        $sql = "select ismanager from user where id=".$uid;
        $res = $mod->select_sql($sql)[0];
        $this->assign('ismanager',$res['ismanager']);
        //如果该用户是管理员 则查出所有的合同
        $search = \Common::get('search');
        if(false === $search){
            $where = '';
        }else{
            $search = urldecode($search);
            if($res['ismanager'] == 'Y'){
                $where = " where contractname like '%{$search}%'";
            }else{
                $where = " and contractname like '%{$search}%'";
            }
        }
        if($res['ismanager'] == 'Y'){
            $sql = "select id,contractname cname from contracts {$where}";
        }else{
        //查找此用户的有效并且还未过期的合同
            $sql = "select id,contractname cname,deadline dline from contracts where id in ";
            $sql .= "(select contractid from contracttouser where userid=".$uid.") and iseffective=1 {$where}";
        }
        $res = $mod->select_sql($sql);
        for($i = 0; $i<count($res); $i++){
            //取出该合同下面的所有话题
            $sql = "select id from topic where contractid=".$res[$i]['id']." and isresolved='N'";
            $topics = $mod->select_sql($sql);
            if(count($topics) <= 0){
                $res[$i]['nc'] = 0;
                $res[$i]['lastmes']="#@#";
            }else{
                $count = 0;
                //查找该用户打开此话题最近的时间
                for($j = 0; $j<count($topics); $j++){
                    $sql = "select lasttime from usertopictime where topicid=".$topics[$j]['id']." and userid=".$uid;
                    $time = $mod->select_sql($sql);
                    if(count($time) == 1){
                        $time = $time[0]['lasttime'];
                    }else{
                        $time = '852076800';   //1997-01-01的时间戳
                    }
                    //查找当前话题的发布时间大于$time（该用户打开此话题的最近的时间）的消息的数量
                    $sql = "select id from message where topicid=".$topics[$j]['id']." and addtime>".$time;
                    $count += count($mod->select_sql($sql));
                }
                $res[$i]['nc'] = $count;
                //查找该合同对应的话题的最新的消息
                $topics = array_map(function($val){return $val['id'];},$topics);
//                 $sql = "select message,m.addtime,isimage,username uname from message m left join user u on m.userid=u.id  where m.addtime=(select max(addtime) from message where topicid=".$res[$j]['id']." and isimage=0)";
                $sql = "select message,m.addtime,isimage,username uname from message m left join user u on m.userid=u.id where m.addtime=(select max(addtime) from message where topicid in (".implode(',', $topics)."))";
                $mes = $mod->select_sql($sql);
                if(count($mes) >0){ 
                    if($mes[0]['isimage']==0){
                        $mes = $mes[0]['uname'].": ".$mes[0]['message']." &nbsp;&nbsp;".date("Y-m-d H:i:s",$mes[0]['addtime']);
                    }else{
                        $mes = $mes[0]['uname'].": 图片 &nbsp;&nbsp;".date('Y-m-d H:i:s',$mes[0]['addtime']);
                    }
                }else $mes = "#@#";
                $res[$i]['lastmes'] = $mes;
            }
            
        }
        $this->assign('count',count($res));
        $this->assign("cta",$res);
        $this->display();
    }
    
    public function topic(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $cid = \Common::get('cid');
        if($cid === false){
            echo "error";
            return;
        }
        $uid = $_SESSION['userid'];
        $mod = new TopicModel();
        $this->assign('contractid',$cid);
        //查找该合同下的所有的话题
        $search = \Common::get('search');
        if(false === $search){
            $where = '';
        }else{
            $search = urldecode($search);
            $where = " and topictitle like '%{$search}%'";
        }
        $sql = "select t.id,topictitle title,username,u.id uid,t.addtime from topic t";
        $sql .= " left join user u on u.id=t.initiator where contractid=".$cid." and isresolved='N' {$where}";
        $res = $mod->select_sql($sql);
        if(count($res) > 0){
            $count = 0;
            //查找该用户打开此话题最近的时间
            for($j = 0; $j<count($res); $j++){
                $sql = "select lasttime from usertopictime where topicid=".$res[$j]['id']." and userid=".$uid;
                $time = $mod->select_sql($sql);
                if(count($time) == 1){
                    $time = $time[0]['lasttime'];
                }else{
                    $time = '852076800';   //1997-01-01的时间戳
                }
                //查找当前话题的发布时间大于$time（该用户打开此话题的最近的时间）的消息的数量
                $sql = "select id from message where topicid=".$res[$j]['id']." and addtime>".$time;
                $count = count($mod->select_sql($sql));
                $res[$j]['nc'] = $count;
//                 $res[$j]['nc'] = $count;
                //查找该话题的最新的消息
                $topics = array_map(function($val){return $val['id'];},$res);
//                 $sql = "select message,m.addtime,isimage,username uname from message m left join user u on m.userid=u.id  where m.addtime=(select max(addtime) from message where topicid=".$res[$j]['id']." and isimage=0)";
                $sql = "select message,m.addtime,isimage,username uname from message m left join user u on m.userid=u.id  where m.addtime=(select max(addtime) from message where topicid=".$res[$j]['id'].")";
                $mes = $mod->select_sql($sql);
                if(count($mes) >0){
                    if($mes[0]['isimage']==0){
                        $mes = $mes[0]['uname'].": ".$mes[0]['message']." &nbsp;&nbsp;".date("Y-m-d H:i:s",$mes[0]['addtime']);
                    }else{
                        $mes = $mes[0]['uname'].": 图片 &nbsp;&nbsp;".date('Y-m-d H:i:s',$mes[0]['addtime']);
                    }
                }else $mes = "#@#";
                $res[$j]['lastmes'] = $mes;
            }
        }
        $this->assign('uid',$uid);
        $this->assign('topics',$res);
        $sql = "select contractname ctname,shortname stname from contracts where id={$cid}";
        $res = $mod->select_sql($sql)[0];
        if(empty($res['stname'])){
            $this->assign('groupname',$res['ctname']);
        }else{
            $this->assign('groupname',$res['stname']);
        }
        $this->display();
    }
    
    public function addtopic(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $cid = \Common::get('contractid');
        $mod = new ContractsModel();
        $sql = "select contractname from contracts where id=".$cid;
        $res = $mod->select_sql($sql);
        $uid = $_SESSION['userid'];
        $this->assign("contractname",$res[0]['contractname']);
        $this->assign("cid",$cid);
        $this->display();
    }
    
    public function addtopic_do(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $cid = \Common::post('cid');
        $topictitle = \Common::post('topictitle');
        $uid = $_SESSION['userid'];
        $mod = new TopicModel();
        $res = $mod->add(array('topictitle'=>$topictitle,'initiator'=>$uid,'addtime'=>time(),'contractid'=>$cid));
        if($res){
            header("Location:/Chat/Group/topic/cid/".$cid);
        }else{
            echo "<script>history.go(-1);</script>";
        }
    }
    
    public function chat(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $tid = \Common::get('tid');
        $uid = $_SESSION['userid'];
        //查找合同id
        $mod = new ContractsModel();
        $sql = "select contractid cid from topic where id=".$tid;
        $res = $mod->select_sql($sql)[0];
        $this->assign('cid',$res['cid']);
        $this->assign('tid',$tid);
        //将该用户打开当前话题的时间存入usertopictime表中
        $utmod = new UsertopictimeModel();
        //首先查找该用户和当前话题是否已经存入usertopictime中 如果在则更新，如果不在则添加
        $sql = "select id from usertopictime where userid=".$uid." and topicid=".$tid;
        $res = $utmod->select_sql($sql);
        if(count($res) == 1){
            //存在 则更新
            $sql = "update usertopictime set lasttime='".time()."' where id=".$res[0]['id'];
            $utmod->sql($sql);
        }elseif(count($res)==0){
            $res = $utmod->add(array('topicid'=>$tid,'userid'=>$uid,'lasttime'=>time()));
        }
        //取出当前话题的所有消息
//         $sql = "select username uname,headimg,companylogo,userid,m.id,message,isimage,m.addtime from message m left join (user u,company c) on (u.id=m.userid and u.companyid=c.id)  where topicid=".$tid;
        $sql = "select username uname,headimg,userid,m.id,message,isimage,m.addtime from message m left join user u on u.id=m.userid where topicid=".$tid." order by addtime desc limit 0,".\Common::C('CHAT_SHOW_NUM');
        $res = $mod->select_sql($sql);
        if(count($res)>=1){
            $res = array_reverse($res);
            for($i = 0; $i<count($res); $i++){
                if($res[$i]['headimg'] == ''){
                    $res[$i]['headimg'] = '/Module/Public/Images/group_tx.png';
                    /* if($res[$i]['companylogo'] == '') $res[$i]['headimg'] = '/Module/Public/Images/group_tx.png';
                    else $res[$i]['headimg'] = $res[$i]['companylogo']; */
                }
                $res[$i]['showtime'] = 0;
            }
//             $c = 1;
            $time = time();
            $tk = array();
            for($i=0;$i<count($res);$i++){
                $t = (strtotime(date("Y-m-d",$time))-strtotime(date("Y-m-d",$res[$i]['addtime'])))/(3600*\Common::C('CHAT_EVERY_TIME'));
                if(!in_array($t, $tk)){
                    array_push($tk, $t);
                    $res[$i]['showtime'] = 1;
                }
            }
//             $res[0]['showtime'] = 1;
            $res[$i-1]['showtime'] = 1;
        }
        $this->assign('num',count($res));
        $this->assign('mess',$res);
        //查找当前登录用户的头像和用户名
//         $sql = "select username uname,headimg,companyid,companylogo from user u left join company c on u.companyid=c.id where u.id=".$uid;
        $sql = "select username uname,headimg,companyid from user u left join company c on u.companyid=c.id where u.id=".$uid;
//         $sql = "select username uname,headimg from user u  where u.id=".$uid;
        $res = $mod->select_sql($sql)[0];
        if($res['headimg'] == ''){
            $res['headimg'] = '/Module/Public/Images/group_tx.png';
            /* if($res['companylogo'] == '') $res['headimg'] = '/Module/Public/Images/group_tx.png';
            else $res['headimg'] = $res['companylogo']; */
        }
        $this->assign('uname',$res['uname']);
        $this->assign('himg',$res['headimg']);
        $this->assign('uid',$uid);
        $this->assign('cmpid',$res['companyid']);
        $sql = "select topictitle title from topic where id={$tid}";
        $res = $mod->select_sql($sql)[0];
        $this->assign('topicname',$res['title']);
        $this->display();
    }
    
   /**
    * 删除群成员
    * @return void|boolean
    */
    public function delmember(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        //取得用户id和合同id
        $uid = intval(\Common::get('uid'));
        $cid = intval(\Common::get('cid'));
        $mod = new ContracttouserModel();
        //删除该合同下的此用户
        $sql = "delete from contracttouser where userid=".$uid." and contractid=".$cid;
        $res = $mod->sql($sql);
        if($res){
            echo "<script>history.go(-1);</script>";
            return true;
        }else{
            echo "<script>history.go(-1);</script>";
            return false;
        }
    }
    
    /**
     * 添加群成员
     * @return void|boolean
     */
    public function addmember(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        //取得用户id和合同id
        $uid = intval(\Common::get('uid'));
        $cid = intval(\Common::get('cid'));
        $mod = new ContracttouserModel();
        $res = $mod->add(array('userid'=>$uid,'contractid'=>$cid));
        if($res){
            echo "<script>location.href='/Chat/Member/groupmem/cid/{$cid}'</script>";
            return true;
        }else{
            echo "<script>history.go(-1)</script>";
            return false;
        }
    }
    
    /**
     * 授权页面 显示该用户的合同
     */
    public function grant(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $uid = $_SESSION['userid'];
        $mod = new ContractsModel();
        /* $sql = "select id,contractname cname,deadline dline from contracts where id in ";
        $sql .= "(select contractid from contracttouser where userid=".$uid.") and iseffective=1"; */
        $sql = "select id,contractname cname from contracts where iseffective=1";
        $res = $mod->select_sql($sql);
        $this->assign('cta',$res);
        $this->display();
    }
    
    public function grant_user(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $cid = \Common::get('cid');  //取得合同id
        $this->assign('cid',$cid);
        $uid = $_SESSION['userid'];
        $mod = new ContractsModel();
        //取得该合同下的项目公司和供应商
        $sql = "select companyname cname from contracts where id={$cid}";
        $res = $mod->select_sql($sql)[0];
        $res['cname'] = explode(',', $res['cname']);
        $this->assign('cname',$res['cname']);
        $this->assign('enter',\Common::C('ENTER_NAME'));
        /* print_r($res);
        exit; */
        /* $sql = "select companyid from user where id=".$uid;
        $res = $mod->select_sql($sql)[0];
        $this->assign('comid',$res['companyid']); */
        //取得合同名称
        $sql = "select contractname cname from contracts where id=".$cid;
        $res = $mod->select_sql($sql)[0];
        $this->assign('contractname',$res['cname']);
        $this->display();        
    }
    
    public function grant_do(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $uname = \Common::post('username');
        $mobile = \Common::post('mobilenum');
        $cid = \Common::post('contractid');
        $comid = \Common::post('companyid');
        $mod = new ContracttouserModel();
        $umod = new UserModel();
        /*
         * 首先查找该手机号的用户是否存在
         */
        $sql = "select id from user where contactmethod=".$mobile;
        $res = $umod->select_sql($sql);
        if(count($res)==0){
            $r = $umod->add(array('username'=>$uname,'password'=>md5('000000'),'contactmethod'=>$mobile,'companyid'=>$comid,'addtime'=>time(),'ismanager'=>'N'));
            if($r){
                $uid = $umod->lastInsId();
            }
        }elseif(count($res)==1){
            $uid = $res[0]['id'];
        }
        //查找该用户是否已经在此合同下
        $sql = "select id from contracttouser where contractid=".$cid." and userid=".$uid;
        $res = $mod->select_sql($sql);
        if(count($res) == 0){
            $r = $mod->add(array('contractid'=>$cid,'userid'=>$uid));
            if($r){
                echo json_encode(array("code"=>0,"info"=>'SUC0'));
                exit;
            }else{
                echo json_encode(array("code"=>1,"info"=>'FAIL1'));
                exit;
            }
        }elseif(count($res) == 1){
            echo json_encode(array("code"=>2,"info"=>"HAD2"));
            exit;
        }
    }
    
    public function add_do(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $uname = \Common::post('username');
        $mobile = \Common::post('mobilenum');
        $cid = \Common::post('contractid');
        $compname = \Common::post('companyname');
        $mod = new ContracttouserModel();
        $umod = new UserModel();
        /*
         * 首先查找此用户是否已经添加
         */
        $sql = "select id from user where contactmethod={$mobile}";
        $r = $umod->select_sql($sql);
        if(count($r)>0){
            //已经查到该用户已经开通了手机app 那么查找是否添加进该合同
            $uid = $r[0]['id'];
            $sql = "select id from contracttouser where userid={$uid} and contractid={$cid}";
            $res = $mod->select_sql($sql);
            if(count($res) > 0){
                //如果已经关联了合同 则不再关联 程序停止执行
                echo json_encode(array('info'=>'HAD2'));
                return ;
            }
        }else{
            //该用户是第一次添加
            $res = $umod->add(array('username'=>$uname,'password'=>md5(\Common::C('DEFAULT_PASS')),'contactmethod'=>$mobile,'companyid'=>\Common::C('COMPANY_ID'),'addtime'=>time(),'ismanager'=>'N','companyname'=>$compname));
            if($res){
                //添加成功，则取出该用户id
                $uid = $umod->lastInsId();
            }else{
                //添加失败 则程序停止执行
                echo json_encode(array('info'=>'FAIL1'));
                return ;
            }
        }
        //关联用户和合同
        $res = $mod->add(array('contractid'=>$cid,'userid'=>$uid));
        if($res){
            //关联成功
            echo json_encode(array('info'=>'SUC0'));
            return ;
        }
        //关联失败
        echo json_encode(array('info'=>'FAIL2'));
        return ;
        /* if($res){
            $uid = $umod->lastInsId();
            $res = $mod->add(array('contractid'=>$cid,'userid'=>$uid));
            if($res){
                echo json_encode(array('info'=>'SUC0'));
            }else{
                echo json_encode(array('info'=>'FAIL1'));
            }
        }else{
                echo json_encode(array('info'=>'FAIL1'));
        } */
    }
    
    public function close_topic(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $tid = \Common::post('topicid');
        $mod = new TopicModel();
        $sql = "update topic set isresolved='Y' where id=".$tid;
        $res = $mod->sql($sql);
        if($res){
            echo json_encode(array('code'=>0));
            return ;
        }else{
            echo json_encode(array('code'=>1));
            return ;
        }
    }
    
    public function chat_load(){
        $tid = \Common::post('tid');
        $num = \Common::post('num');
        $uid = $_SESSION['userid'];
        $mod = new ContractsModel();
        $sql = "select username uname,headimg,userid,m.id,message,isimage,m.addtime from message m left join user u on u.id=m.userid where topicid=".$tid." order by addtime desc limit {$num},".\Common::C('CHAT_SHOW_NUM');
        $res = $mod->select_sql($sql);
        if(count($res)>=1){
            for($i = 0; $i<count($res); $i++){
                if($res[$i]['headimg'] == ''){
                    $res[$i]['headimg'] = '/Module/Public/Images/group_tx.png';
                    /* if($res[$i]['companylogo'] == '') $res[$i]['headimg'] = '/Module/Public/Images/group_tx.png';
                     else $res[$i]['headimg'] = $res[$i]['companylogo']; */
                }
                $res[$i]['showtime'] = 0;
            }
            $time = time();
            $tk = array();
            for($i=count($res)-1;$i>=0;$i--){
                $t = (strtotime(date("Y-m-d",$time))-strtotime(date("Y-m-d",$res[$i]['addtime'])))/(3600*\Common::C('CHAT_EVERY_TIME'));
                if(!in_array($t, $tk)){
                    array_push($tk, $t);
                    $res[$i]['showtime'] = 1;
                    $res[$i]['addtime'] = date("Y-m-d H:i",$res[$i]['addtime']);
                }
            }
            /* $res[0]['showtime'] = 1;
            $res[0]['addtime'] = date("Y-m-d H:i:s",$res[0]['addtime']); */
        }
        if(count($res)>=1){
            echo json_encode($res);
        }else{
            echo json_encode(array('code'=>0));
        }
    }
    
}