<?php
namespace Admin\Action;
use Common\Action\CommonAction;
use User\Model\UserModel;
use Onlinebid\Model\ContracttouserModel;
class UserAction extends CommonAction{
    public function addeuser(){
        $this->display();
    }
    
    public function addeuser_do(){
        $mod = new UserModel();
        $ctumod = new ContracttouserModel();
        $uname = \Common::post('uname');
        $mobile = \Common::post('mobile');
        //首先在company表中查找集团的id
        $sql = "select id from company where usertype='E'";
        $res = $mod->select_sql($sql)[0];
        $cmpid = $res['id'];
        $data = array(
            'username'=>$uname,
            'password'=>md5(\Common::post('DEFAULT_PASS')),
            'contactmethod'=>$mobile,
            'companyid'=>$cmpid,
            'addtime'=>time(),
            'ismanager'=>'Y'
        );
        $res = $mod->add($data);
        if($res){
            $uid = $mod->lastInsId();
            //将此管理员和所有的合同关联起来
            //查找所有有效的合同id
            $sql = "select id from contracts where iseffective=1";
            $res = $mod->select_sql($sql);
            if(count($res)>0){
                //批量添加
                $sql = 'insert into contracttouser(contractid,userid) values ';
                foreach($res as $key=>$val){
                    $sql .= '('.$val['id'].','.$uid.'),';
                }
                $sql = rtrim($sql,',');
            }
            $res = $ctumod->sql($sql);
            if($res){
                echo json_encode(array('code'=>0));
                return ;
            }
            echo json_encode(array('code'=>2));
            return ;
            
        }
        echo json_encode(array('code'=>1));
        return ;
    }
    
    public function userlist(){
        $currpage = \Common::get('p') === false ? 1 : \Common::get('p');
        $where = '';
        $this->assign('search','');
        if(\Common::get('search') !== false){
            $this->assign('search','/search/'.\Common::get('search'));
            $where = "where username like '%".urldecode(\Common::get('search'))."%'";
        }
        $sql = "select count(*) from user ".$where;
        $mod = new UserModel();
        $res = $mod->select_sql($sql)[0];
        $totalnum = $res['count(*)'];
        $everypnum = 20;
        $totalpage = ceil($totalnum/$everypnum);
        if($currpage <= 0) $currpage = 1;
        elseif($currpage > $totalpage) $currpage = $totalpage;
        $this->assign('totalpage',$totalpage);
        $this->assign('currpage',$currpage);
        $sql = "select u.id,username uname,companyname cname,contactmethod mobile from user u left join company c";
        $sql .= " on c.id=u.companyid ".$where." order by u.id desc limit ".($currpage-1)*$everypnum.",".$everypnum;
        $res = $mod->select_sql($sql);
        $this->assign('memlist',$res);
        $this->display();
    }
    public function deluser(){
        $uid = \Common::post('uid');
        if($uid === false){
            echo json_encode(array('code'=>2));
            return ;
        }
        $mod = new UserModel();
        $sql = "delete from user where id=".$uid;
        $res = $mod->sql($sql);
        if($res){
            echo json_encode(array('code'=>0));
            return ;
        }
        echo json_encode(array('code'=>1));
        return ;
    }
}