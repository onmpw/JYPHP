<?php
namespace Chat\Action;
use User\Model\UserModel;
use Common\Action\CommonAction;
class MemberAction extends CommonAction{
    public function groupmem(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $cid = \Common::get('cid'); //得到合同id
        $uid = $_SESSION['userid'];
        //首先查看该登录用户是否是管理员
        $sql = "select id,ismanager from user where id={$uid}";
        $mod = new UserModel();
        $res = $mod->select_sql($sql)[0];
        $this->assign('ismanager',$res['ismanager']);
        //查找会员
        /* $sql = "select u.id,username uname,companyname cname,headimg,ismanager,usertype from user u left join company c on c.id=u.companyid ";
        $sql .= " where u.id in (select userid from contracttouser where contractid=".$cid.")";
        $res = $mod->select_sql($sql);
        $this->assign("memlist",$res);
        
        //查找当前用户属于哪一种用户
        $sql = "select usertype utype from company where id=(select companyid from user where id=".$uid.")";
        $r = $mod->select_sql($sql)[0];
        $this->assign('cid',$cid);
        $this->assign('uid',$uid);
        $this->assign('manage',$r['utype']=='E'?'Y':'N'); */
        $this->assign('cid',$cid);
        $sql = "select id,username uname,headimg,ismanager,companyname cname,contactmethod mobile from user where id in(select userid from contracttouser where contractid={$cid})";
        $res = $mod->select_sql($sql);
        $this->assign('memlist',$res);
        $this->display();
    }
    
    public function memberlist(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $cid = \Common::get('cid');
        $uid = $_SESSION['userid'];
        //首先判断当前登录的人是否是集团的负责人
        $mod = new UserModel();
        $sql = "select id from company where id=(select companyid from user where id=".$uid.") and usertype='E'";
        $res = $mod->select_sql($sql);
        if(count($res) != 1){
            $this->display();
            return ;
        }
        //查找此合同下的所有用户
        $sql = "select userid from contracttouser where contractid=".$cid;
        $r = $mod->select_sql($sql);
        $r = array_map(function($val){return $val['userid'];},$r);
        //查找此合同下的每个用户所属的公司 找出公司id
        $sql = "select u.id,username uname, companyname cname,headimg from user u ";
        $sql .= " left join company c on u.companyid=c.id";
        $res = $mod->select_sql($sql);
        if(count($res)<=0){
            $this->display();
            return ;
        }
        foreach($res as $key=>$val){
            if(in_array($val['id'],$r)){
                $res[$key]['isadded'] = 'Y';
            }else{
                $res[$key]['isadded'] = 'N';
            }
        }
        $this->assign('cid',$cid);
        $this->assign('memlist',$res);
        $this->display();
    }
}