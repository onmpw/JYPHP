<?php
namespace Lib\Webservice;
use Inter\Webservice\I_WS_Bids;
use Onlinebid\Model\BidsModel;
use Onlinebid\Model\BidnoticeModel;
use Onlinebid\Model\BidtypeconModel;
use Onlinebid\Model\ContractsModel;
use Onlinebid\Model\ContracttouserModel;
class WS_Bids implements I_WS_Bids{
    /**
     * ����б���
     * @see \Inter\Webservice\I_WS_Bids::bids()
     */
    public function bids($bids = ''){
        return $bids;
    }
    
    /**
     * ����б깫��
     * @see \Inter\Webservice\I_WS_Bids::bidnotice()
     */
    public function bidnotice($notice = ''){
        $info = array();
        //ʵ����ģ��
        $bidmod = new BidsModel();
        $bidnoticemod = new BidnoticeModel();
        
        if(empty($notice)){
            $info['code'] = 1;
//             $info['con'] = '�б깫��Ϊ��';
            return json_encode($info);
        }
        //����noticeΪ����
        $notice = json_decode($notice,true);
        //ȡ��mbs�е��б���id
        if(!isset($notice['mbsbidid'])){
            $info['code'] = 2;
//             $info['con'] = '�б���idΪ��';
            return json_encode($info);
        }
        $mbid = $notice['mbsbidid'];
        /*
         * ���Ȳ��ҵ�ǰ���б����Ƿ����
         * ������ڣ����Ǹ����б깫�棬ֻ����б깫�棬�����б���ı���в�����
         * �������������һ���µ��б���
         */
        $up = false;
        $sql = "select id from bids where mbsbidid=".$mbid;
        $res = $bidmod->select_sql($sql);
        if(count($res) === 0){
            if(!isset($notice['bidname'])){
                $info['code'] = 3;
//                 $info['con'] = '�б�������Ϊ��';
                return json_encode($info);
            }
            $result = $bidmod->add(array('mbsbidid'=>$mbid,'bidname'=>$notice['bidname']));
            if($result){
                $lastinsid = $bidmod->lastInsId();
            }
        }elseif(count($res)>0){
            $lastinsid = $res[0]['id'];
            //�б�����ڣ�������б깫��id����bidnotice�����Ƿ���ڴ��б깫��
            $sql = "select id from bidnotice where mbsnoticeid=".$notice['mbsnoticeid'];
            $res = $bidnoticemod->select_sql($sql);
            if(count($res) == 1){
                //����б깫����� �򲻽����κβ��� ����ֹͣ����ִ��
                $info['code'] = 5;
                return json_encode($info);
            }
            //�б�����ڣ�����¸��б�����б깫��Ϊ��Ч
            //ѡ����б����������Ч�Ĺ���id
            $sql = "select id from bidnotice where bidid=".$lastinsid." and iseffective='Y'";
            $res = $bidnoticemod->select_sql($sql);
            $noticeid = $res[0]['id'];
            //���Ų���sup_bid ���д��б����Ӧ���б깫���Ƿ��б����Ĺ�Ӧ�� ����б����ģ����Ӧ�ڴ��б깫��ı�����Ϊ��Ч
            $sql = "select id from sup_bid where noticeid=".$noticeid;
            $res = $bidnoticemod->select_sql($sql);
            //���ȿ���������
            $bidnoticemod->startTransaction();
            if(count($res) >0){
                $sql = "update sup_bid set signiseffective='N' where noticeid=".$noticeid;
                $r = $bidnoticemod->sql($sql);
            }
            $sql = "update bidnotice set iseffective='N' where bidid=".$lastinsid;
            $r = $bidnoticemod->sql($sql);
            $up = true;
        }
        //ȥ��notice�����ж������  ��Щ��bidnotice������Ҫ
        unset($notice['bidname']);  //�б�������
        unset($notice['mbsbidid']); //�б�����mbs�е�id
        $typecontent = $notice['typecontent'];
        unset($notice['typecontent']);
        /*
         * �����б깫�����bidnotice����
         */
        $notice['bidid'] = $lastinsid;
        $res = $bidnoticemod->add($notice);
        if($res){
            //�����ӳɹ� �� typecontent ��ͬbidnotice����idһͬ����bidtypecon����
            //�õ�����id 
            $noticeid = $bidnoticemod->lastInsId();
            $typemod = new BidtypeconModel();
            $result = $typemod->add(array('bidid'=>$noticeid,'typecontent'=>$typecontent,'typekey'=>$notice['requireofbidtype']));
            if($result){
                $info['code'] = 0;
//                 $info['con'] = '��ӳɹ�';
                if($up) $bidnoticemod->Commit();
            }else{
                $info['code'] = 4;
                $info['con'] = '1';
                if($up) $bidnoticemod->rollBack();
            }
            $typemod->closeDb();
        }else{
            $info['code'] = 4;
            $info['con'] = '2';
            if($up) $bidnoticemod->rollBack();
        }
        $bidmod->closeDb();
        $bidnoticemod->closeDb();
        return json_encode($info);
        
    }
    public function bidnotice_update($notice = ''){
        $info = array();
        if(empty($notice)){
            $info['code'] = 1;
            return json_encode($info);
        }
        $notice = json_decode($notice,true);
        if(!isset($notice['mbsnoticeid'])){
            $info['code'] = 2;
            return json_encode($info);
        }
        $bidnoticemod = new BidnoticeModel();
        
        $sql = "select id from bidnotice where mbsnoticeid=".$notice['mbsnoticeid'];
        $res = $bidnoticemod->select_sql($sql);
        if(count($res) === 0){
            $info['code'] = 3;
        }elseif(count($res)>0){
            //�����ж��б�����б��������Ƿ���� ���������ôȥ��������
            if(isset($notice['bidid'])) unset($notice['bidid']);
            if(isset($notice['bidname'])) unset($notice['bidname']);
            if(isset($notice['iseffective'])) unset($notice['iseffective']);
            if(isset($notice['mbsbidid'])) unset($notice['mbsbidid']);
            
            //�õ���ǰҪ���µ��б깫���id
            $noticeid = $res[0]['id'];
            //�ж�����Ҫ�������Ƿ����
            if(isset($notice['typecontent']) && isset($notice['requireofbidtype'])){
                $typemode = new BidtypeconModel();
                //����Ҫ�����͸���
                //���ȿ���������
                $typemode->startTransaction();
                $sql = "update bidtypecon set typecontent='".$notice['typecontent']."',typekey='".$notice['requireofbidtype']."'";
                $res = $typemode->sql($sql);
                if(!$res){
                    $info['code'] = 4;
                    return json_encode($info);
                }
                //ɾ������Ҫ������� �Է������bidnotice���е�����
                unset($notice['typecontent']);            }
            $sql = 'update bidnotice set ';
            foreach($notice as $key=>$val){
                $sql .= $key."='".$val."',";
            }
            $sql = rtrim($sql,',')." where id=".$noticeid;
            $res = $bidnoticemod->sql($sql);
            if($res){
                $info['code'] = 0;
                //�������������³ɹ� ���ύ���� ʹǰ�������Ҫ����³ɹ�
                $typemode->Commit();
            }else{
                $info['code'] = 5;
                //��������������ʧ��  ��ع����� ʹǰ��ĸ��µ�����Ҫ��ʧЧ
                $typemode->rollBack();
            }
        }
        return json_encode($info);
        
    }
    
    /**
     * ��ͬ�ӿ�
     * @see \Inter\Webservice\I_WS_Bids::contracts()
     */
    public function contracts($contract = ''){
        $info  = array();
        if(empty($contract)){
            $info['code'] = 2;
            return json_encode($info);
        }
        $contract = json_decode($contract,true);
        //�жϹ�Ӧ�̻�����Ŀ��˾�Ƿ����
        /* if(!isset($contract['supplierid'])|| !isset($contract['itemcompanyid'])){
            $info['code'] = 3;
            return json_encode($info);
        } */
        $sup_id = $contract['supplierid'];
        $itemcomp_id = $contract['itemcompanyid'];
        unset($contract['supplierid']);
        unset($contract['itemcompanyid']);
        $contractmod = new ContractsModel();
        
        //���Ȳ��Ҵ˺�ͬ�Ƿ��Ѿ������ڱ���
        $sql = "select id from contracts where contractid=".$contract['contractid'];
        $res = $contractmod->select_sql($sql);
        if(count($res) == 1){
            //���Ҫ��ӵĺ�ͬ�Ѿ�������contracts���� ˵��֮ǰ�Ѿ���ӹ���������ӳ���������ִ��
            $info['code'] = 3;
            return json_encode($info);
        }
//         $contractmod->startTransaction();
        /*
         * ��Ӻ�ͬ
         */
        $res = $contractmod->add($contract);
        if($res){
            /* $contractid = $contractmod->lastInsId(); //�õ�����id
            //���Ƚ����Ÿ����˼���
            $sql = "select id from company where usertype='E'";
            $res = $contractmod->select_sql($sql);
            if(count($res) == 0){
                //������в����ڼ��Ÿ����� ����ӵĺ�ͬ��Ϊ��Ч ���ҷ��ش�����Ϣ ����������ִ��
                $info['code'] = 3;
                $sql = 'update contracts set iseffective=0 where id='.$contractid;
                $res = $contractmod->sql($sql);
                return json_encode($info);
                
            }
            //���д��ڼ��Ÿ����� �����user���ж�Ӧ���û������ǹ���Ա
            $Eusers = array();
            for($i=0;$i<count($res);$i++){
                $sql = "select id from user where companyid=".$res[$i]['id']." and ismanager='Y'";
                $user = $contractmod->select_sql($sql);
                $Eusers = array_merge(array_map(function($val){return $val['id'];},$user));
            }
            //������ڼ��Ÿ����˶�Ӧ��user�� ��ʼ��ӵ�contracttouser����
            $contousermod = new ContracttouserModel();
            for($i = 0;$i<count($Eusers); $i++){
                $contousermod->add(array('userid'=>$Eusers[$i],'contractid'=>$contractid));
            }
            //��ͬ�ͼ��Ÿ����˶���ӳɹ� ����ҹ�Ӧ��id����Ŀ��˾id�Ƿ���company���д���
            
            //���Ȳ��ҹ�Ӧ�̶�Ӧ���Ƿ����
            $sql = "select id from company where mbsuserid='".$sup_id."' and usertype='S'";
            $res = $contractmod->select_sql($sql);
            $Eusers = array();  //���³�ʼ��Eusers���� �������Ĺ�Ӧ�̺���Ŀ��˾��Ӧ��userid
            $compid = array();
            if(count($res) == 1){
                //��Ӧ�̴��� ����sup_id ��mbsid ��Ϊcompanyid Ȼ�������Ŀ��˾�Ƿ����
                $compid[] = $sup_id = $res[0]['id']; //�õ���Ӧ��id
                $sql = "select id from company where mbsuserid='".$itemcomp_id."' and usertype='P'";
                $r = $contractmod->select_sql($sql);
                if(count($r) == 1){
                    //��Ŀ��˾���� ����itemcomp_id ��mbsid ��Ϊcompanyid
                    $compid[] = $itemcomp_id = $r[0]['id'];
                }else{
                    $info['code'] = 4; //��Ŀ��˾������  ͬ������ӵĺ�ͬ��Ϊ��Ч ���ҷ��ش�����Ϣ ����������ִ��
                    $sql = 'update contracts set iseffective=0 where id='.$contractid;
                    $res = $contractmod->sql($sql);
                    return json_encode($info);
                }
                
            }else{
                //��Ӧ�̲����� ͬ������ӵĺ�ͬ��Ϊ��Ч ���ҷ��ش�����Ϣ ����������ִ��
                $sql = 'update contracts set iseffective=0 where id='.$contractid;
                $res = $contractmod->sql($sql);
                return json_encode($info);
            }
            //��user���в��ҹ�Ӧ�̺���Ŀ��˾��Ӧ���û� �����ǹ���Ա
            for($i=0;$i<count($compid);$i++){
                $sql = "select id from user where companyid=".$compid[$i]." and ismanager='Y'";
                $user = $contractmod->select_sql($sql);
                $Eusers = array_merge($Eusers,array_map(function($val){return $val['id'];},$user));
            }
            //Ȼ������ӵ�contracttouser����
            for($i = 0;$i<count($Eusers); $i++){
                $contousermod->add(array('userid'=>$Eusers[$i],'contractid'=>$contractid));
            }*/
            $info['code'] = 0;
//             $contractmod->Commit();
            return json_encode($info); 
        }
        $info['code'] = 1;
//         $contractmod->rollBack();
        return json_encode($info);
    }
}