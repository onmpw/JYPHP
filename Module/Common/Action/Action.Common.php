<?php
namespace Common\Action;
use Lib\Action;
use User\Model\UserModel;
use Lib\CheckCode;

abstract class CommonAction extends Action{
    public function checkislogin($type = 1){
        if($type == 1){
            if(isset($_SESSION['userid']))
                return true;
            else{
                if(isset($_COOKIE['mobile'])&&isset($_COOKIE['password'])){
                    $mod = new UserModel();
//                     $sql = "select u.id,username uname,password pwd,companyid cid,usertype utype from user u left join company c on u.companyid=c.id where contactmethod='{$_COOKIE['mobile']}'";
                    $sql = "select id,username uname,password pwd from user where contactmethod='{$_COOKIE['mobile']}'";
                    $res = $mod->select_sql($sql);
                    for ($i = 0; $i < count($res); $i ++) {
                        if ($res[$i]['pwd'] == $_COOKIE['password']) {
                            // 登录成功
                            $_SESSION['userid'] = $res[$i]['id'];
//                             $_SESSION['cid'] = $res[$i]['cid'];
                            $_SESSION['uname'] = $res[$i]['uname'];
//                             $_SESSION['utype'] = $res[$i]['utype'];
//                             $islogin = true;
                            return true;
//                             break;
                        }
                    }
                }
                return false;
            }
            
        }elseif($type == 2){
            if(isset($_SESSION['managerid']))
                return true;
            return false;
        }
    }
    /**
     * 创建验证码函数
     * @param string $id
     * @param unknown $config
     */
    public function make_verify_code($id = '',$config=array()){
        $verify = new CheckCode($config);
        $verify->entry($id);
    }
    
    /**
     * 上传图片
     */
    public function UploadImg(){
        $file = $_FILES['upload'];
        /*
         * 设定允许上传的头像的类型
        */
        $allow_type = array('jpg','jpeg','png','gif');
        /*
         * 设定上传路径
        */
        $upload_path = DOC_ROOT."Data/Upload/Images/";
        $furl = "/Data/Upload/Images/";
        $time = time();
        $filename = substr($file['name'],0,strrpos($file['name'],'.'));
        $filetype = substr($file['name'],strrpos($file['name'],'.')+1);
        $filename = \Common::make_hash('sha1', $filename,\Common::make_rand_str()).".".$filetype;
        /*
         * 按照一定的规则 重命名文件名称
         */
        /*
         * 调用 公共上传函数上传头像
         */
        //         $res = $this->upload_file($file,$allow_type,$upload_path,$filename);
        if(is_uploaded_file($file['tmp_name'])){
            if(!is_dir($upload_path)) mkdir($upload_path,0777,true);
            if(move_uploaded_file($file['tmp_name'],$upload_path.$filename)){
                $res['error'] = 0;
                $res['content'] = $furl.$filename;
                return $furl.$filename;
            }else{
                $res['error'] = 1;
                $res['error_info'] = \Common::C('UPLOAD_FAILED');
            }
        }
        return json_encode($res);
    }
}