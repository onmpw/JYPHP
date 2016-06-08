<?php
namespace Onlinebid\Action;
use Common\Action\CommonAction;
use Onlinebid\Model\BidnoticeModel;
use Onlinebid\Model\UserModel;
class BidinfoAction extends CommonAction{
    
    public function bidlist(){
        //�����ж��Ƿ��Ѿ���¼
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $mod = new BidnoticeModel();
        $uid = $_SESSION['userid']; //�õ��û�id
//         $cid = $_SESSION['cid']; //�õ���˾id
//         $utype = $_SESSION['utype'];
//         $this->assign('utype',$utype);
        //�����Ѿ��������б���
//         $sql = "select id,noticeid from sup_bid where supid=".$cid." and signiseffective='Y'";
        /* $signid = $mod->select_sql($sql);
        if(count($signid)>0){
            $signid = array_map(function($val){return $val['noticeid'];}, $signid);
        } */
        //����������Ч���б깫��
        $sql = "select bn.id,bidname bname,addtime,bidnoticename nname,signdeadline sline from bidnotice bn left join bids b on b.id=bn.bidid where iseffective='Y'";
//         $sql = "select id, bidname bname,addtime,bidnoticename nname,signdeadline sline from bidnotice where iseffective='Y'";
//         echo $sql;exit;
        $res = $mod->select_sql($sql);
        /* foreach($res as $key=>$val){
            //ѭ��ÿһ��鿴 ��Ӧ���Ƿ�Դ��б깫�汨��
            if(in_array($val['id'],$signid)){
                //�Ѿ��������� �� issign����ΪY
                $res[$key]['issign'] = 'Y';
            }else{
                //û�б����������Ȳ鿴��ֹ�����Ƿ��Ѿ���ʱ
                if($res[$key]['sline'] > time())
                    //û�г�ʱ ���¼δ����
                    $res[$key]['issign'] = 'N';
                else
                    //��ʱ �򽫴�����¼ɾ��
                    unset($res[$key]);
            }
        } */
        $this->assign("bids",$res);
        $this->display();
    }
    
    public function bidinfocon(){
        //�����ж��Ƿ��Ѿ���¼
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        //�õ��б깫��id
        $noticeid = \Common::get('noticeid');
        //�鿴�Ƿ��Ѿ�����
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
//         $this->assign('issign',$issign); //�Ƿ���
        if(empty($noticeid)) return false;
        $sql = "select bidnoticename bnname,location loc,content con,type,scale,range,signdeadline sline,contactor,mobile,email,contemplate ctp,typecontent tpt";
        $sql .= " from bidnotice bn left join (requireofbidtype rbt,bidtypecon btc) on (bn.requireofbidtype=rbt.typekey and btc.bidid=bn.id) ";
        $sql .= " where bn.id=".$noticeid; 
        $res = $bidmod->select_sql($sql);
        if(count($res) <= 0) return false;
        $res = $res[0]; //ȡ����һ��;
        $ctp = $res['ctp'];
        //�ж�����Ҫ�������ģ���ʵ�������Ƿ����
        if(!empty($res['ctp'])&&!empty($res['tpt'])){
            //�������
            $tpt = json_decode($res['tpt'],true); // ��ʵ�����ݽ���������
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
            //�滻ģ�����Ϊʵ�ʵ�����
            foreach($tpt as $key=>$val){
                $res['ctp'] = str_replace('{:'.$key.'}', $val, $res['ctp']);
            }
            //����ģ����ʣ���δ���滻Ϊʵ��ֵ�ı��� �滻Ϊ��
            $ctp = preg_replace('/\{:\w+\}/', '',$ctp);
            
        }
        //�����滻�Ժ������Ҫ���Ϊ����
        $res['ctp'] = json_decode($res['ctp']);
        //ɾ��res�е�tpt
        unset($res['tpt']);
        $res['id'] = $noticeid;
        $this->assign('bidcon',$res);
        $this->assign('start',0); //����Ҫ���ѭ����ʼ
//         $this->assign('utype',$_SESSION['utype']);
        $this->assign('end',count($res['ctp'])-1); //����Ҫ���ѭ������
        $this->display();
    }
    
    public function signup(){
        //�����ж��Ƿ��Ѿ���¼
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        //����ȡ�ø��û��Ľ�ɫ��������˾�����
        $uid = $_SESSION['userid'];
        $nid = \Common::post('nid');
        $mod = new UserModel();
        $sql = "select ismanager,companyid cid,mbsuserid mid,usertype utype from user u left join company c";
        $sql .= " on u.companyid=c.id where u.id=".$uid;
        $res = $mod->select_sql($sql)[0];
        if($res['ismanager'] == 'N'){
            echo json_encode(array('code'=>1));   //���ǹ���Ա��û��Ȩ�ޱ���
            return ;
        }
        if($res['utype'] != 'S'){
            echo json_encode(array('code'=>2)); //���ǹ�Ӧ�̣����ܱ���
            return ;
        }
        //Ȼ������Ƿ��Ѿ�����
        $sql = "select id from sup_bid where supid=".$res['cid']." and noticeid=".$nid;
        $issign = $mod->select_sql($sql);
        if(count($issign)>0){
            echo json_encode(array('code'=>3,'type'=>'err'));  //�Ѿ�����
            return ;
        }
        $mbsuid = $res['mid'];
        //���ݹ���id����mbs�й���id
        $sql = "select mbsnoticeid from bidnotice where id=".$nid;
        $res['mbsnid'] = $mod->select_sql($sql)[0]['mbsnoticeid'];
        $soap = new \SoapClient("http://192.168.18.224:8080/mbs/services/WebSrc?wsdl");
        $re = $soap->__call("supplierSignUp", array(json_encode(array('Y100'=>$mbsuid,'C100'=>$res['mbsnid']))));
        $sql = "insert into sup_bid(noticeid,supid,signiseffective) values ({$nid},'{$res['cid']}','Y')";
        $res = $mod->sql($sql);
        if($re=='OK'){
            echo json_encode(array('code'=>0)); //�����ɹ�
        }else{
            echo json_encode(array('code'=>3,'type'=>'mbs')); //�Ѿ�����
        }
        return ;
    }
}