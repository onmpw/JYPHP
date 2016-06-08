<?php
namespace Admin\Action;
use Onlinebid\Model\ContractsModel;
use Common\Action\CommonAction;
class ContractAction extends CommonAction{
    public function contractlist(){
        $currpage = \Common::get('p') === false ? 1 : \Common::get('p');
        $where = '';
        $this->assign('search','');
        if(\Common::get('search') !== false){
            $this->assign('search','/search/'.\Common::get('search'));
            $where = "where contractname like '%".urldecode(\Common::get('search'))."%'";
        }
        $sql = "select count(*) from contracts ".$where;
        $mod = new ContractsModel();
        $res = $mod->select_sql($sql)[0];
        $totalnum = $res['count(*)'];
        $everypnum = 20;
        $totalpage = ceil($totalnum/$everypnum);
        if($currpage <= 0) $currpage = 1;
        elseif($currpage > $totalpage) $currpage = $totalpage;
        $this->assign('totalpage',$totalpage);
        $this->assign('currpage',$currpage);
        $sql = "select id,contractname ctname,validtime vtime,iseffective isef,shortname stname,item from contracts ".$where;
        $sql .= " order by id desc limit ".($currpage-1)*$everypnum.",".$everypnum;
        $res = $mod->select_sql($sql);
        $this->assign("ctlist",$res);
        $this->display();
    }
    
    
    public function editcontract(){
        $ctid = \Common::get('ctid');
        $mod = new ContractsModel();
        $sql = "select contractname cname,shortname sname,item from contracts where id={$ctid}";
        $res = $mod->select_sql($sql)[0];
        $this->assign('ct',$res);
        $this->assign('ctid',$ctid);
        $this->display();
    }
    
    public function editcontract_do(){
        $ctid = \Common::post('ctid');
        $shortname = \Common::post('shortname');
        $item = \Common::post('item');
        $mod = new ContractsModel();
        $sql = "update contracts set shortname='{$shortname}',item='{$item}' where id={$ctid}";
        $res = $mod->sql($sql);
        if($res){
//             header("Location:/Admin/Contract/contractlist");
            echo 1;
        }else{
//             echo "<script>history.go(-1);</script>";
            echo 0;
        }
    }
    
    public function delcontract(){
        $ctid = \Common::post('ctid');
        if($ctid === false){
            echo json_encode(array('code'=>2));
            return ;
        }
        $mod = new ContractsModel();
        $sql = "delete from contracts where id=".$ctid;
        $res = $mod->sql($sql);
        if($res){
            echo json_encode(array('code'=>0));
            return ;
        }
        echo json_encode(array('code'=>1));
        return ;
    }
    
    public function topiclist(){
        $currpage = \Common::get('p') === false ? 1 : \Common::get('p');
        $where = '';
        $this->assign('search','');
        if(\Common::get('search') !== false){
            $this->assign('search','/search/'.\Common::get('search'));
            $where = "where topictitle like '%".urldecode(\Common::get('search'))."%'";
        }
        $sql = "select count(*) from topic ".$where;
        $mod = new ContractsModel();
        $res = $mod->select_sql($sql)[0];
        $totalnum = $res['count(*)'];
        $everypnum = 20;
        $totalpage = ceil($totalnum/$everypnum);
        if($currpage <= 0) $currpage = 1;
        elseif($currpage > $totalpage) $currpage = $totalpage;
        $this->assign('totalpage',$totalpage);
        $this->assign('currpage',$currpage);
        $sql = "select t.id,topictitle title,contractname cname,username uname,isresolved isres from topic t";
        $sql .= " left join (contracts c,user u) on (t.contractid=c.id and t.initiator=u.id) ".$where;
        $sql .= " order by t.id ASC limit ".($currpage-1)*$everypnum.",".$everypnum;
        $res = $mod->select_sql($sql);
        $this->assign("tplist",$res);
        $this->display();
    }
    
    public function deltopic(){
        $tid = \Common::post('tid');
        if($tid === false){
            echo json_encode(array('code'=>2));
            return ;
        }
        $mod = new ContractsModel();
        $sql = "delete from topic where id=".$tid;
        $res = $mod->sql($sql);
        if($res){
            echo json_encode(array('code'=>0));
            return ;
        }
        echo json_encode(array('code'=>1));
        return ;
    }
    
    public function contractmem(){
        
    }
    
    public function addmem(){
        
    }
}