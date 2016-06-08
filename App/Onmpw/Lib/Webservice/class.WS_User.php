<?php
namespace Lib\Webservice;
use Inter\Webservice\I_WS_User;
use Onlinebid\Model\CompanyModel;
use Onlinebid\Model\UserModel;
class WS_User implements I_WS_User{
    /**
     * 开通手机账户
     * @see \Inter\Webservice\I_WS_User::open_account()
     */
    public function open_account($info = ''){
        file_put_contents("/tmp/test.txt", $info);
        if(empty($info)) return null;
        $infos = array();
        $infos = json_decode($info,true);
        $mod = new CompanyModel();
        //首先查找此用户是否已经在company表中存在
        $sql = "select id from company where mbsuserid=".$infos['mbsuserid']." and usertype='".$infos['usertype']."'";
        $res = $mod->select_sql($sql);
        //如果已经存在则程序停止向下执行
        if(count($res) == 1) return false;
        $res = $mod->add($infos);
        
        if($res){
            $lastinsid = $mod->lastInsId();
            $insuserids = array();
            $data = array(
                'username'=>'',
                'password'=>md5(\Common::C('DEFAULT_MOBILE_PASS')),
                'contactmethod'=>'',
                'companyid'=>$lastinsid,
                'addtime'=>time(),
                'ismanager'=>'Y'
            );
            $umod = new UserModel();
            if(isset($infos['contactor1'])){
                $data['username'] = $infos['contactor1'];
                $data['contactmethod'] = $infos['contactmethod1'];
                $res = $umod->add($data);
                if($res){
                    $insuserids[]=$umod->lastInsId();
                }
            }
            if(isset($infos['contactor2'])){
                $data['username'] = $infos['contactor2'];
                $data['contactmethod'] = $infos['contactmethod2'];
                $res = $umod->add($data);
                if($res){
                    $insuserids[]=$umod->lastInsId();
                }
            }
            if(isset($infos['contactor3'])){
                $data['username'] = $infos['contactor3'];
                $data['contactmethod'] = $infos['contactmethod3'];
                $res = $umod->add($data);
                if($res){
                    $insuserids[]=$umod->lastInsId();
                }
            }
            $umod->closeDb();
            $mod->closeDb();
            return json_encode(array('code'=>0));
        }
        return json_encode(array('code'=>1));
    }
}