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
     * 添加招标项
     * @see \Inter\Webservice\I_WS_Bids::bids()
     */
    public function bids($bids = ''){
        return $bids;
    }
    
    /**
     * 添加招标公告
     * @see \Inter\Webservice\I_WS_Bids::bidnotice()
     */
    public function bidnotice($notice = ''){
        $info = array();
        //实例化模型
        $bidmod = new BidsModel();
        $bidnoticemod = new BidnoticeModel();
        
        if(empty($notice)){
            $info['code'] = 1;
//             $info['con'] = '招标公告为空';
            return json_encode($info);
        }
        //解析notice为数组
        $notice = json_decode($notice,true);
        //取出mbs中的招标项id
        if(!isset($notice['mbsbidid'])){
            $info['code'] = 2;
//             $info['con'] = '招标项id为空';
            return json_encode($info);
        }
        $mbid = $notice['mbsbidid'];
        /*
         * 首先查找当前的招标项是否存在
         * 如果存在，则是更新招标公告，只添加招标公告，不对招标项的表进行操作。
         * 如果不存在则是一个新的招标项
         */
        $up = false;
        $sql = "select id from bids where mbsbidid=".$mbid;
        $res = $bidmod->select_sql($sql);
        if(count($res) === 0){
            if(!isset($notice['bidname'])){
                $info['code'] = 3;
//                 $info['con'] = '招标项名称为空';
                return json_encode($info);
            }
            $result = $bidmod->add(array('mbsbidid'=>$mbid,'bidname'=>$notice['bidname']));
            if($result){
                $lastinsid = $bidmod->lastInsId();
            }
        }elseif(count($res)>0){
            $lastinsid = $res[0]['id'];
            //招标项存在，则根据招标公告id查找bidnotice表中是否存在此招标公告
            $sql = "select id from bidnotice where mbsnoticeid=".$notice['mbsnoticeid'];
            $res = $bidnoticemod->select_sql($sql);
            if(count($res) == 1){
                //如果招标公告存在 则不进行任何操作 程序停止向下执行
                $info['code'] = 5;
                return json_encode($info);
            }
            //招标项存在，则更新该招标项的招标公告为无效
            //选择该招标项下面的有效的公告id
            $sql = "select id from bidnotice where bidid=".$lastinsid." and iseffective='Y'";
            $res = $bidnoticemod->select_sql($sql);
            $noticeid = $res[0]['id'];
            //接着查找sup_bid 表中此招标项对应的招标公告是否有报名的供应商 如果有报名的，则对应于此招标公告的报名置为无效
            $sql = "select id from sup_bid where noticeid=".$noticeid;
            $res = $bidnoticemod->select_sql($sql);
            //首先开启事务处理
            $bidnoticemod->startTransaction();
            if(count($res) >0){
                $sql = "update sup_bid set signiseffective='N' where noticeid=".$noticeid;
                $r = $bidnoticemod->sql($sql);
            }
            $sql = "update bidnotice set iseffective='N' where bidid=".$lastinsid;
            $r = $bidnoticemod->sql($sql);
            $up = true;
        }
        //去除notice数组中多余的项  这些项bidnotice表并不需要
        unset($notice['bidname']);  //招标项名称
        unset($notice['mbsbidid']); //招标项在mbs中的id
        $typecontent = $notice['typecontent'];
        unset($notice['typecontent']);
        /*
         * 将此招标公告加入bidnotice表中
         */
        $notice['bidid'] = $lastinsid;
        $res = $bidnoticemod->add($notice);
        if($res){
            //如果添加成功 则将 typecontent 连同bidnotice插入id一同存入bidtypecon表中
            //得到插入id 
            $noticeid = $bidnoticemod->lastInsId();
            $typemod = new BidtypeconModel();
            $result = $typemod->add(array('bidid'=>$noticeid,'typecontent'=>$typecontent,'typekey'=>$notice['requireofbidtype']));
            if($result){
                $info['code'] = 0;
//                 $info['con'] = '添加成功';
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
            //首先判断招标项和招标项名称是否存在 如果存在那么去掉这两项
            if(isset($notice['bidid'])) unset($notice['bidid']);
            if(isset($notice['bidname'])) unset($notice['bidname']);
            if(isset($notice['iseffective'])) unset($notice['iseffective']);
            if(isset($notice['mbsbidid'])) unset($notice['mbsbidid']);
            
            //得到当前要更新的招标公告的id
            $noticeid = $res[0]['id'];
            //判断资质要求类型是否更新
            if(isset($notice['typecontent']) && isset($notice['requireofbidtype'])){
                $typemode = new BidtypeconModel();
                //资质要求类型更新
                //首先开启事务处理
                $typemode->startTransaction();
                $sql = "update bidtypecon set typecontent='".$notice['typecontent']."',typekey='".$notice['requireofbidtype']."'";
                $res = $typemode->sql($sql);
                if(!$res){
                    $info['code'] = 4;
                    return json_encode($info);
                }
                //删除资质要求的内容 以方便更新bidnotice表中的内容
                unset($notice['typecontent']);
            }
            $sql = 'update bidnotice set ';
            foreach($notice as $key=>$val){
                $sql .= $key."='".$val."',";
            }
            $sql = rtrim($sql,',')." where id=".$noticeid;
            $res = $bidnoticemod->sql($sql);
            if($res){
                $info['code'] = 0;
                //如果整个公告更新成功 则提交事务 使前面的资质要求更新成功
                $typemode->Commit();
            }else{
                $info['code'] = 5;
                //如果整个公告更新失败  则回滚事务 使前面的更新的资质要求失效
                $typemode->rollBack();
            }
        }
        return json_encode($info);
        
    }
    
    /**
     * 合同接口
     * @see \Inter\Webservice\I_WS_Bids::contracts()
     */
    public function contracts($contract = ''){
        $info  = array();
        if(empty($contract)){
            $info['code'] = 2;
            return json_encode($info);
        }
        $contract = json_decode($contract,true);
        //判断供应商或者项目公司是否存在
        /* if(!isset($contract['supplierid'])|| !isset($contract['itemcompanyid'])){
            $info['code'] = 3;
            return json_encode($info);
        } */
        $sup_id = $contract['supplierid'];
        $itemcomp_id = $contract['itemcompanyid'];
        unset($contract['supplierid']);
        unset($contract['itemcompanyid']);
        $contractmod = new ContractsModel();
        
        //首先查找此合同是否已经存在于表中
        $sql = "select id from contracts where contractid=".$contract['contractid'];
        $res = $contractmod->select_sql($sql);
        if(count($res) == 1){
            //如果要添加的合同已经存在于contracts表中 说明之前已经添加过，则不再添加程序不再向下执行
            $info['code'] = 3;
            return json_encode($info);
        }
//         $contractmod->startTransaction();
        /*
         * 添加合同
         */
        $res = $contractmod->add($contract);
        if($res){
            /* $contractid = $contractmod->lastInsId(); //得到插入id
            //首先将集团负责人加入
            $sql = "select id from company where usertype='E'";
            $res = $contractmod->select_sql($sql);
            if(count($res) == 0){
                //如果表中不存在集团负责人 则将添加的合同置为无效 并且返回错误信息 程序不再向下执行
                $info['code'] = 3;
                $sql = 'update contracts set iseffective=0 where id='.$contractid;
                $res = $contractmod->sql($sql);
                return json_encode($info);
                
            }
            //表中存在集团负责人 则查找user表中对应的用户并且是管理员
            $Eusers = array();
            for($i=0;$i<count($res);$i++){
                $sql = "select id from user where companyid=".$res[$i]['id']." and ismanager='Y'";
                $user = $contractmod->select_sql($sql);
                $Eusers = array_merge(array_map(function($val){return $val['id'];},$user));
            }
            //如果存在集团负责人对应的user则 开始添加到contracttouser表中
            $contousermod = new ContracttouserModel();
            for($i = 0;$i<count($Eusers); $i++){
                $contousermod->add(array('userid'=>$Eusers[$i],'contractid'=>$contractid));
            }
            //合同和集团负责人都添加成功 则查找供应商id和项目公司id是否在company表中存在
            
            //首先查找供应商对应的是否存在
            $sql = "select id from company where mbsuserid='".$sup_id."' and usertype='S'";
            $res = $contractmod->select_sql($sql);
            $Eusers = array();  //重新初始化Eusers变量 存放下面的供应商和项目公司对应的userid
            $compid = array();
            if(count($res) == 1){
                //供应商存在 并且sup_id 由mbsid 改为companyid 然后查找项目公司是否存在
                $compid[] = $sup_id = $res[0]['id']; //得到供应商id
                $sql = "select id from company where mbsuserid='".$itemcomp_id."' and usertype='P'";
                $r = $contractmod->select_sql($sql);
                if(count($r) == 1){
                    //项目公司存在 并且itemcomp_id 由mbsid 改为companyid
                    $compid[] = $itemcomp_id = $r[0]['id'];
                }else{
                    $info['code'] = 4; //项目公司不存在  同样将添加的合同置为无效 并且返回错误信息 程序不再向下执行
                    $sql = 'update contracts set iseffective=0 where id='.$contractid;
                    $res = $contractmod->sql($sql);
                    return json_encode($info);
                }
                
            }else{
                //供应商不存在 同样将添加的合同置为无效 并且返回错误信息 程序不再向下执行
                $sql = 'update contracts set iseffective=0 where id='.$contractid;
                $res = $contractmod->sql($sql);
                return json_encode($info);
            }
            //在user表中查找供应商和项目公司对应的用户 并且是管理员
            for($i=0;$i<count($compid);$i++){
                $sql = "select id from user where companyid=".$compid[$i]." and ismanager='Y'";
                $user = $contractmod->select_sql($sql);
                $Eusers = array_merge($Eusers,array_map(function($val){return $val['id'];},$user));
            }
            //然后将其添加到contracttouser表中
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