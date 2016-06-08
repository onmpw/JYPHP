<?php
namespace Onlinebid\Action;
use Common\Action\CommonAction;
use Onlinebid\Model\BidnoticeModel;
use Onlinebid\Model\UserModel;
class BidinfoAction extends CommonAction{
    
    public function bidlist(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $mod = new BidnoticeModel();
        $uid = $_SESSION['userid']; //得到用户id
//         $cid = $_SESSION['cid']; //得到公司id
//         $utype = $_SESSION['utype'];
//         $this->assign('utype',$utype);
        //查找已经报名的招标项
//         $sql = "select id,noticeid from sup_bid where supid=".$cid." and signiseffective='Y'";
        /* $signid = $mod->select_sql($sql);
        if(count($signid)>0){
            $signid = array_map(function($val){return $val['noticeid'];}, $signid);
        } */
        //查找所有有效的招标公告
        $sql = "select bn.id,bidname bname,addtime,bidnoticename nname,signdeadline sline from bidnotice bn left join bids b on b.id=bn.bidid where iseffective='Y'";
//         $sql = "select id, bidname bname,addtime,bidnoticename nname,signdeadline sline from bidnotice where iseffective='Y'";
//         echo $sql;exit;
        $res = $mod->select_sql($sql);
        /* foreach($res as $key=>$val){
            //循环每一项查看 供应商是否对此招标公告报名
            if(in_array($val['id'],$signid)){
                //已经报过名的 则将 issign项置为Y
                $res[$key]['issign'] = 'Y';
            }else{
                //没有报过名的首先查看截止日期是否已经过时
                if($res[$key]['sline'] > time())
                    //没有超时 则记录未报名
                    $res[$key]['issign'] = 'N';
                else
                    //超时 则将此条记录删除
                    unset($res[$key]);
            }
        } */
        $this->assign("bids",$res);
        $this->display();
    }
    
    public function bidinfocon(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        //得到招标公告id
        $noticeid = \Common::get('noticeid');
        //查看是否已经报名
//         $issign = \Common::get('issign');
//         $sql = "select id from sup_bid where supid=".$_SESSION['cid']." and noticeid=".$noticeid;
        $bidmod = new BidnoticeModel();
       /*  $res = $bidmod->select_sql($sql);
        if(count($res)>0){
            $this->assign('issign','Y');
        }elseif(count($res)==0){
            $this->assign('issign','N');
        } */
//         $issign = false === \Common::get('issign')?'':\Common::get('issign');
//         $this->assign('issign',$issign); //是否报名
        if(empty($noticeid)) return false;
        $sql = "select bidnoticename bnname,location loc,content con,type,scale,range,signdeadline sline,contactor,mobile,email,contemplate ctp,typecontent tpt";
        $sql .= " from bidnotice bn left join (requireofbidtype rbt,bidtypecon btc) on (bn.requireofbidtype=rbt.typekey and btc.bidid=bn.id) ";
        $sql .= " where bn.id=".$noticeid; 
        $res = $bidmod->select_sql($sql);
        if(count($res) <= 0) return false;
        $res = $res[0]; //取出第一条;
        $ctp = $res['ctp'];
        //判断资质要求的内容模板和实际内容是否存在
        if(!empty($res['ctp'])&&!empty($res['tpt'])){
            //两项都存在
            $tpt = json_decode($res['tpt'],true); // 将实际内容解析成数组
            if(is_null($tpt)){
                $res['tpt'] = explode(',',trim($res['tpt'],'{'));
                $tpt = array();
                foreach($res['tpt'] as $val){
                    $t = explode(':',$val);
                    $t[0] = trim($t[0],"'");
                    $t[1] = trim($t[1],"'");
                    $tpt[$t[0]]=$t[1];
                }
                $tpt = json_decode(json_encode($tpt),true);
            }
            //替换模板变量为实际的内容
            foreach($tpt as $key=>$val){
                $res['ctp'] = str_replace('{:'.$key.'}', $val, $res['ctp']);
            }
            //查找模板中剩余的未被替换为实际值的变量 替换为空
            $ctp = preg_replace('/\{:\w+\}/', '',$ctp);
            
        }
        //解析替换以后的资质要求成为数组
        $res['ctp'] = json_decode($res['ctp']);
        //删除res中的tpt
        unset($res['tpt']);
        $res['id'] = $noticeid;
        $this->assign('bidcon',$res);
        $this->assign('start',0); //资质要求的循环开始
//         $this->assign('utype',$_SESSION['utype']);
        $this->assign('end',count($res['ctp'])-1); //资质要求的循环结束
        $this->display();
    }
    
    public function signup(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        //首先取得该用户的角色和所属公司的类别
        $uid = $_SESSION['userid'];
        $nid = \Common::post('nid');
        $mod = new UserModel();
        $sql = "select ismanager,companyid cid,mbsuserid mid,usertype utype from user u left join company c";
        $sql .= " on u.companyid=c.id where u.id=".$uid;
        $res = $mod->select_sql($sql)[0];
        if($res['ismanager'] == 'N'){
            echo json_encode(array('code'=>1));   //不是管理员，没有权限报名
            return ;
        }
        if($res['utype'] != 'S'){
            echo json_encode(array('code'=>2)); //不是供应商，不能报名
            return ;
        }
        //然后查找是否已经报名
        $sql = "select id from sup_bid where supid=".$res['cid']." and noticeid=".$nid;
        $issign = $mod->select_sql($sql);
        if(count($issign)>0){
            echo json_encode(array('code'=>3,'type'=>'err'));  //已经报名
            return ;
        }
        $mbsuid = $res['mid'];
        //根据公告id查找mbs中公告id
        $sql = "select mbsnoticeid from bidnotice where id=".$nid;
        $res['mbsnid'] = $mod->select_sql($sql)[0]['mbsnoticeid'];
        $soap = new \SoapClient("http://192.168.18.224:8080/mbs/services/WebSrc?wsdl");
        $re = $soap->__call("supplierSignUp", array(json_encode(array('Y100'=>$mbsuid,'C100'=>$res['mbsnid']))));
        $sql = "insert into sup_bid(noticeid,supid,signiseffective) values ({$nid},'{$res['cid']}','Y')";
        $res = $mod->sql($sql);
        if($re=='OK'){
            echo json_encode(array('code'=>0)); //报名成功
        }else{
            echo json_encode(array('code'=>3,'type'=>'mbs')); //已经报名
        }
        return ;
    }
}