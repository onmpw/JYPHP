<?php
namespace User\Action;
use Common\Action\CommonAction;
use User\Model\UserModel;
use Onlinebid\Model\CompanyModel;
use Onlinebid\Model\ContracttouserModel;
class UserAction extends CommonAction{
    private $imgtype = array(
        'gif'=>'gif',
        'png'=>'png',
        'jpg'=>'jpeg',
        'jpeg'=>'jpeg'
    );
    public function login_do(){
        header("Content-Type:text/html;charset=utf-8");
        $mobile = \Common::post('mobile');
        $pass = md5(\Common::post('password'));
        $code = \Common::post('code');
        $mobileid = \Common::post('mobileid');
//         $verify = new \Lib\Checkcode();
//         if ($verify->check($code, 1)) {
            $mod = new UserModel();
//             $sql = "select u.id,username uname,password pwd,companyid cid,usertype utype from user u left join company c on u.companyid=c.id where contactmethod='{$mobile}'";
            $sql = "select id,username uname,password pwd from user where contactmethod='{$mobile}'";
            $res = $mod->select_sql($sql);
            $islogin = false;
            for ($i = 0; $i < count($res); $i ++) {
                if ($res[$i]['pwd'] == $pass) {
                    // 登录成功
                    $_SESSION['userid'] = $res[$i]['id'];
//                     $_SESSION['cid'] = $res[$i]['cid'];
                    $_SESSION['uname'] = $res[$i]['uname'];
                    //将数据库中的状态改为登录状态
                    $sql = "update user set islogin=1 where id={$res[$i]['id']}";
                    $res = $mod->sql($sql);
                    if($mobileid){
                        $_SESSION['mtoken'] = $mobileid;
                        if($res){
                            $sql = "select id from user_device where mtoken='{$mobileid}' and uid={$_SESSION['userid']}";
                            $res = $mod->select_sql($sql);
                            if(count($res) == 0){
                               $sql = "insert into user_device(mtoken,uid) values('{$mobileid}',{$_SESSION['userid']})";
                               $res = $mod->sql($sql);
                            }
                        }
                    }
//                     $_SESSION['utype'] = $res[$i]['utype'];
                    setcookie('mobile',$mobile,time()+60*60*24*30,"/");
                    setcookie('password',$pass,time()+60*60*24*30,"/");
                    $islogin = true;
                    break;
                }
            }
            if ($islogin) {
                // 登录成功，判断是供应商、项目公司还是集团负责人
                $uid = $_SESSION['userid'];
//                 $cid = $_SESSION['cid'];
//                 $sql = "select usertype from company where id=" . $cid;
//                 $utype = $mod->select_sql($sql)[0]['usertype'];
                /*
                 * 根据不同的角色跳转到不同的页面
                 */
//                 switch ($utype) {
//                     case 'S':
//                         $url = "/Onlinebid/Bidinfo/bidlist";
//                         break;
//                     case 'P':
//                         $url = "/Chat/Group/mygroup";
//                         break;
//                     case 'E':
//                         $url = "/Chat/Group/mygroup";
//                         break;
//                     default:
//                         $url = "/User/User/logout";
//                 }
                $url = '/Chat/Group/mygroup';
                header("Location:" . $url);
            } else {
                echo "<script>alert('用户名或密码错误！');history.go(-1)</script>";
            }
//         } else {
//             echo "<script>alert('验证码错误！');history.go(-1);</script>";
//         }
    }
    public function logout(){
        $mod = new UserModel();
        $sql = "update user set islogin=0 where id={$_SESSION['userid']}";
        $res = $mod->sql($sql);
        $sql = "update user_device ispush=0 where userid={$_SESSION['userid']} and mtoken='{$_SESSION['mtoken']}'";
        $res = $mod->sql($sql);
        unset($_SESSION['userid']);
//         unset($_SESSION['cid']);
        unset($_SESSION['uname']);
        $mtoken = $_SESSION['mtoken'];
        unset($_SESSION['mtoken']);
        setcookie('mobile',"",time()-3600*24*30,"/");
        setcookie('password',"",time()-3600*24*30,"/");
        header("Location:/Onlinebid/Index/index/mobileid/{$mtoken}");
    }
    
    public function myinfo(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $mod = new UserModel();
        $uid = $_SESSION['userid'];
//         $cid = $_SESSION['cid'];
        $uname = $_SESSION['uname'];
        /* $sql = "select headimg himg,companylogo clogo,c.companyname cname from user u left join company c";
        $sql .= " on u.companyid=c.id where u.id=".$uid; */
        $sql = "select companyname cname,headimg himg from user where id={$uid}";
        $res = $mod->select_sql($sql)[0];
        $himg = '';
//         $res['clogo'] = $res['clogo'] == ''?'/Module/Public/Images/group_tx.png':$res['clogo'];
        if($res['himg'] == ''){
//             $himg = $res['clogo'] == ''?'/Module/Public/Images/group_tx.png':$res['clogo'];
            $himg = '/Module/Public/Images/group_tx.png';
        }else{
            $himg = $res['himg'];
        }
        $this->assign('headimg',$himg);
//         $this->assign('companylogo',$res['clogo']);
        $this->assign('uname',$uname);
        $this->assign('uid',$uid);
//         $this->assign('cmpid',$cid);
        $this->assign('cname',$res['cname']);
        $this->display();
    }
    
    public function modifypass(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $uid = $_SESSION['userid'];
        $uname = $_SESSION['uname'];
        $this->assign('uname',$uname);
        $this->assign('uid',$uid);
        $this->display();
    }
    
    public function modifypass_do(){
        //首先判断是否已经登录
        if(!$this->checkislogin()){
            header("Location:/Onlinebid/Index/index");
            return ;
        }
        $oldpass = \Common::post('oldpass');
        $newpass = \Common::post('newpass');
        $uid = \Common::post('userid');
        $mod = new UserModel();
        $sql = "select password pwd from user where id=".$uid;
        $res = $mod->select_sql($sql)[0];
        if(md5($oldpass) == $res['pwd']){ //如果就密码正确则允许修改
            $sql = "update user set password='".md5($newpass)."' where id=".$uid;
            $res = $mod->sql($sql);
            if($res){
                echo json_encode(array('code'=>0,'info'=>"SUC0"));
                exit;
            }else{
                echo json_encode(array('code'=>1,'info'=>"FAIL1"));
                exit;
            }
        }else{
            echo json_encode(array('code'=>2,'info'=>"FAIL2"));
            exit;
        }
    }
    /**
     * 上传公司logo
     */
    public function uploadlogo(){
        $upload = $this->ajaxUpload();
        if($upload === false){
            echo json_encode(array('code'=>1,'con'=>'failed'));
            exit;
        }
        //下面将数据存入数据库
        $cid = \Common::post('companyid');
        $mod = new CompanyModel();
        $sql = "update company set companylogo='".$upload."' where id=".$cid;
        $res = $mod->sql($sql);
        if($res){
            echo json_encode(array('code'=>0,'con'=>$upload));
        }else{
            echo json_encode(array('code'=>2,'con'=>'failed'));
        }
    }
    /**
     * 上传头像
     */
    public function uploadheadimg(){
        $upload = $this->ajaxUpload();
        if($upload === false){
            echo json_encode(array('code'=>1,'con'=>'failed'));
            exit;
        }
        //下面将数据存入数据库
        $uid = \Common::post('userid');
        $mod = new UserModel();
        $sql = "update user set headimg='".$upload."' where id=".$uid;
        $res = $mod->sql($sql);
        if($res){
            echo json_encode(array('code'=>0,'con'=>$upload));
        }else{
            echo json_encode(array('code'=>2,'con'=>'failed'));
        }
    }
    
    /**
     * 上传缩略图
     */
    public function uploadthumimg(){
        $upload = $this->ajaxUpload();
        if($upload === false){
            echo json_encode(array('code'=>1,'con'=>'failed'));
            exit;
        }
        echo json_encode(array('code'=>0,'con'=>$upload));
    }
    /**
     * 利用ajax上传的函数
     * 
     * @return mixed
     */
    private function ajaxUpload(){
        $message = \Common::post('message');
        $filename = \Common::post('filename');
        $ftype = \Common::post('filetype');
        $filename = \Common::make_hash('sha1', $filename,\Common::make_rand_str()).".".$ftype;
        $message = base64_decode(substr($message,strlen('data:image/'.$this->imgtype[strtolower($ftype)].';base64,')));
        $dir = DOC_ROOT."Data/Upload/Images/";
        $furl = "/Data/Upload/Images/";
        $file = fopen($dir.$filename,"w");
        if(fwrite($file,$message) === false){
            return false;
//             echo json_encode(array('code'=>1,'con'=>'failed'));
//             exit;
        }
        return $furl.$filename;
    }
    
    public function iosUploadimg(){
        $url = $this->ajaxUpload();
        echo $url===false?0:$url;
    }
    
    public function chatUpload(){
        $file = $_FILES['upimage'];
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
            file_put_contents("/tmp/tmp.txt", $filename);
            if(!is_dir($upload_path)) mkdir($upload_path,0777,true);
            if(move_uploaded_file($file['tmp_name'],$upload_path.$filename)){
                $res['error'] = 0;
                $res['content'] = $furl.$filename;
            }else{
                $res['error'] = 1;
                $res['error_info'] = \Common::C('UPLOAD_FAILED');
            }
        }
        echo json_encode($res);
    }
    
    public function uploadimages(){
        $filename = "testtest.jpg";
        $full = DOC_ROOT."Data/Upload/Images/".$filename;
        $message = \Common::post('message');
        $leng = \Common::post('currlenth');
        $size = \Common::post('filesize');
        $ftype = \Common::post('filetype');
//         $file = fopen($full,"a");
        $message = substr($message,strlen('data:image/'.$this->imgtype[strtolower($ftype)].';base64,'));
        $currlen = 0;
        unlink("/tmp/testtest.txt");
        while($currlen < strlen($message)){
//             fwrite($file,base64_decode(substr($message,$currlen,$leng)));
//             fwrite($file,base64_decode(substr($message,$currlen,$leng)));
            file_put_contents("/tmp/testtest.txt", substr($message,$currlen,$leng),FILE_APPEND);
            $currlen += $leng;
            usleep(20000);
        }
        $file = fopen($full,'w');
        fwrite($file,base64_decode(file_get_contents("/tmp/testtest.txt")));
        fclose($file);
        echo "100%";
        /* if(fwrite($file,$message) === false){
//             return false;
               echo json_encode(array('code'=>1,'con'=>'failed'));
               exit;
        }
        echo json_encode(array('code'=>0,'con'=>'success')); */
        /* for($i=0;$i<100000;$i++){
            flush();
            ob_flush();
            echo $i;
        } */
    }
    public function push(){
        $cid = \Common::post('cid');
        $uid = \Common::post('userid');
        $tid = \Common::post('tid');
        $mod = new ContracttouserModel();
        $sql = "select userid from contracttouser cu left join user u on cu.userid=u.id where u.islogin=1 and contractid={$cid} and userid!={$uid}";
        $res = $mod->select_sql($sql);
        $uids = array_map(function($e){return $e['userid'];}, $res);
        $sql = "select mtoken from user_device where uid in(".implode(',',$uids).") and ispush=1";
        $res = $mod->select_sql($sql);
        $mtokens = array_map(function($e){return strlen($e['mtoken'])==64?$e['mtoken']:"supplier_".$e['mtoken'];},$res);
        $sql = "select contractname cname from contracts where id={$cid}";
        $res = $mod->select_sql($sql)[0];
        $par = array(
            'message'=>$res['cname']."群:有新消息",
            'address'=>"/Chat/Group/chat/tid/{$tid}"
        );
        $str = json_encode($par);
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,\Common::C('PUSH_URL')."ByToken");
        curl_setopt($ch,CURLOPT_POST,1);
//         curl_setopt($ch,CURLOPT_POSTFIELDS,"msg={$res['cname']}群:有新消息&token=".implode(',',$mtokens));
        curl_setopt($ch,CURLOPT_POSTFIELDS,"msg=".$str."&token=".implode(',',$mtokens));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $con = curl_exec($ch);
        curl_close($ch);
        var_dump($con);
    }
    public function getState(){
        $token = \Common::post('mobileid');
        $state = \Common::post('state');//0 不推送 ，1 推送
        file_put_contents("/www/mobileapp/Data/cache/token.txt", $token."--".$state);
        $mod = new UserModel();
        if($state == 0){
            $sql = "update user_device set ispush=0 where mtoken='{$token}'";
            $res = $mod->sql($sql);
        }elseif($state == 1){
            $sql = "select u.id from user u left join user_device ud on ud.uid=u.id where mtoken='{$token}' and islogin=1";
            $res = $mod->select_sql($sql);
            if(count($res) == 1){
                $sql = "update user_device set ispush=1 where mtoken='{$token}' and uid={$res[0]['id']}";
                $res = $mod->sql($sql);
            }
        }
    }
}