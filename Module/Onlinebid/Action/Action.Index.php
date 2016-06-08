<?php
namespace Onlinebid\Action;
use Common\Action\CommonAction;
use Onlinebid\Model\CompanyModel;
use Onlinebid\Model\RequireofbidtypeModel;
use Onlinebid\Model\UserModel;
class IndexAction extends CommonAction{
    
    public function Index(){
        $mid = \Common::get('mobileid');
        //首先查看token是否已经绑定
        $mod = new UserModel();
        $sql = "select id from user_device where mtoken={$mid}";
        $res = $mod->select_sql($sql);
        if(count($res)>0){
            $sql = "update user_device set ispush=0 where mtoken={$mid}";
            $res = $mod->sql($sql);
        }
        if(!$this->checkislogin()){
            if($mid === false){
                $this->assign('mobileid',0);
            }else{
                $this->assign('mobileid',$mid);
            }
            $this->display();
        }else{
            $url = "/Chat/Group/mygroup";
            header("Location:" . $url);
        }
    }
    public function code($id = ''){
        $config=array(
            'expire'     => 120,  //验证码过期时间 60s  如果不设置 则默认为1800s
            'useCurve'   => false,
            'useNoise'   => false,
            'imageW'     => 82,
            'imageH'     => 28,
            'fontSize'   => 17,
            'length'     => 4,
//             'codeSet'    => "0123456789",
            'bg'         => array(255,255,255),
            'fontttf'    => '6.ttf'
        );
        $this->make_verify_code($id,$config);
    }
    
    public function hehe(){
        $this->assign('var','lhz');
        $this->display();
    }
    
    public function test(){
        /* $link = \Lib\DB::getInstance();
        echo "<pre>";
        print_r($link->getTables()); */
        $mod = new CompanyModel();
//         $sql = "select id from bids where id=6";
//         $sql = "update bids set bidname='world' where id=11";
//         $res=$mod->sql($sql);
            $arr = array(
                'companyname'=>'供应商测试',
                'businesslicense' => '154789630123',
                'addtime'=>'212354587',
                'contactor1'=>'林青霞',
                'contactmethod1'=>'15933528121',
                'contactor2'=>'周星驰',
                'contactmethod2'=>'13331200753',
                'mbsuserid'=>3,
                'usertype' =>'P'
            );
            $mod->add($arr);
            $arr['businesslicense']='254789630123';
            $mod->add($arr);
//         var_dump($mod->add(array('mbsbidid'=>1,'bidname'=>"hello world")));
//         var_dump($mod -> getModelName());
//         $mod = new \Lib\Model();
//         $mod->where("id=1,name='aaaaa'");
    }
    
    public function hello(){
        $arr = array(
            'test'=>'a',
            'type'=>'b'
        );
        echo json_encode($arr);
    }
    public function ac(){
        echo $this->par();
    }
    public function adduser(){
        $soap = new \SoapClient("http://mobileapp.modernland.hk/Service/Webservice/WS_User.php?wsdl");
        $arr = array(
            'companyname'=>'供应商3',
            'businesslicense' => '154789630123',
            'addtime'=>'212354587',
            'contactor1'=>'王力宏',
            'contactmethod1'=>'15933528124',
            'contactor2'=>'宙斯',
            'contactmethod2'=>'13331200754',
            'mbsuserid'=>"1111111",
            'usertype' =>'S'
        );
        $userinfo = json_encode($arr);
        var_dump($soap->__call("open_account",array($userinfo)));
    }
    public function addbids(){
        $soap = new \SoapClient("http://mobileapp.modernland.hk/Service/Webservice/WS_Bids.php?wsdl");
        $arr = array(
            'mbsbidid'=>18,
            'mbsnoticeid'=>28,
            'bidname'=>'测试招标项1',   //招标项名称
            'bidnoticename'=>'测试招标公告名称1', //招标公告名称
            'location'=>'北京市东城区香河园路1号万国城MOMA1', //项目位置
            'content'=>'防火闭门器家用液压自动关门1', //招标内容
            'type'=>'公开招标',   //招标类别
            'scale'=>'总建筑面积 1000 ㎡，其中地上面积 1000 ㎡ ， 地下面积 1000 ㎡； 楼层 10 ， 总高度 80 m', //项目规模
            'range'=>'全部开放', //招标范围
            'requireofbidtype'=>'A', //投标资格要求
            'typecontent'=>'{"qualilevel":"一级","squaremeter":"500","registercapital":"100万元"}',   //投标资格要求内容
            'signdeadline'=>'1446219000', //投标截止时间
            'contactor' =>'李明全',  //联系人
            'mobile'=>'18962651666',  //联系电话
            'email' =>'ddzy@modernland.hk',  //电子邮箱
        );
        $bids = json_encode($arr);
        var_dump($soap->__call("bidnotice",array($bids)));
    }
    
    public function upnotice(){
        header("Content-type:text/html;charset=utf-8");
        $soap = new \SoapClient("http://mobileapp.modernland.hk/Service/Webservice/WS_Bids.php?wsdl");
        $arr = array(
            'mbsbidid'=>18,
            'mbsnoticeid'=>28,
            'bidname'=>'测试招标项1',   //招标项名称
            'bidnoticename'=>'测试招标公告名称1', //招标公告名称
            'location'=>'北京市东城区香河园路1号万国城MOMA1', //项目位置
            'content'=>'防火闭门器家用液压自动关门1', //招标内容
            'type'=>'公开招标',   //招标类别
            'scale'=>'总建筑面积 1000 ㎡，其中地上面积 1000 ㎡ ， 地下面积 1000 ㎡； 楼层 10 ， 总高度 80 m', //项目规模
            'range'=>'全部开放', //招标范围
            'requireofbidtype'=>'A', //投标资格要求
            'typecontent'=>'{"qualilevel":"一级","squaremeter":"500","registercapital":"100万元"}',   //投标资格要求内容
            'signdeadline'=>'1446219000', //投标截止时间
            'contactor' =>'李明全',  //联系人
            'mobile'=>'18962651666',  //联系电话
            'email' =>'ddzy@modernland.hk',  //电子邮箱
        );
        $bids = json_encode($arr);
        var_dump($soap->__call("bidnotice_update",array($bids)));
    }
    
    public function addbidtype(){
        $mod = new RequireofbidtypeModel();
        /* $arr1 = array(
            '资质等级:<em>{:qualilevel}</em>','企业近三年同类工程业绩不少于三项目同类工程经验不少于<em>{:squaremeter}</em>平方米 ，质量合格。',
            '公司和项目经理有与房地产行业100强企业合作','企业注册资金：<em>{:registercapital}</em>','近五年内无重大安全事故，业内无不良业绩记录'
        ); */
        /* $arr1 = array(
            '品牌或档次:<em>{:brandgrade}</em>','供应商/代理商注册资金：<em>{:registercaptial}</em>',
            '供应商质量管理体系认证证书:<em>{:certification}</em>','生产商必须具备招标设备或者材料的生产许可资质，并有相应的检测合格报告；',
            '投标单位具备良好设计配合及深化能力，能按照我司要求打样和配合，直到满足我司设计需求；工程经验丰富，供货周期满足工程要求，项目经理具备类似工程经验；',
            '企业近三年来具有类似<em>{:C116}</em>以上类似项目的工程，总合同金额在<em>{:contraceamount}</em>（含）人民币以上（或等值外币）的<em>{:C118}</em>工程经验，提供相关证明文件（合同复印件等），并满足我司考察要求；',
            '企业应具备ISO9001国际质量管理体系认证；',
            '企业在我司招标项目所在地有分公司或办事处，并且派驻专人负责联络，能响应项目的要求，有良好的技术合作与服务支持；',
            '近5年内无重大质量安全事故，业内无不良业绩及记录（投标时要求承诺）。'
        ); */
        $arr1 = array(
            '资质等级:<em>{:qualilevel}</em>','企业近三年同类工程业绩不少于三项目同类工程经验不少于<em>{:squaremeter}</em>平方米 ，质量合格；',
            '公司和项目经理有与房地产行业100强企业合作的经验；',
            '企业注册资金：<em>{:registercapital}</em>；',
            '近5年内无重大质量安全事故，业内无不良业绩及记录（投标时要求承诺）；',
            '具有相应的图纸深化能力；',
            '根据具体分包内容具体要求。'
        );
//         print_r(json_decode(json_encode($arr1),true));exit;
        
        $arr = array(
            'typename' => '独立专业分包类',
            'typekey'   => 'D',
            'contemplate'=> json_encode($arr1),
        );
        $res = $mod->add($arr);
        if($res){
            $lastinsid = $mod->lastInsId();
        }
        var_dump($res);
    }
    
    public function contracts(){
        $soap = new \SoapClient("http://mobileapp.modernland.hk/Service/Webservice/WS_Bids.php?wsdl");
        $arr = array(
            'contractname'=>'测试招标项合同',  //合同名称
            'contractid'=>12,   //合同id
            'deadline'=> '15896523', //合同失效的时间
            'validtime'=> '10年', //合同有效期
            'iseffective'=>1,  //合同是否有效
            'supplierid'=>3,   //mbs供应商id
            'itemcompanyid'=>3, //项目公司id
        );
        $info = json_encode($arr);
        var_dump($soap->__call("contracts",array($info)));
    }
    
}
