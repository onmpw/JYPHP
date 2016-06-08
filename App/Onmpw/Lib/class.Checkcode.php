<?php
/**
 * ����֤������ʹ�õĹ����д���ʹ���ϵ�bug ���� ������֤���ʱ����Ҫʵ����һ����֤���� ������֤��ʱ����Ҫ����һ����֤����
* ��ʱ�ͻ�����������֤���ʵ������������п��ܲ�һ�� ����˵ ����ʱ��  ����ڲ�����ʱ�������˹���ʱ��  ������֤��ʱ��û������
* ��ô�ͻ�ʹ��Ĭ�ϵĹ���ʱ�� ��ô���������ʱ��ͻ᲻һ�� �Ǻ�����Ҫ�Ľ���ͻ�������
*/
namespace Lib;

class Checkcode {

    //     public static $_instance;

    protected $config =	array(
        'seKey'     =>  'ThinkPHP.CN',   // ��֤�������Կ
        'codeSet'   =>  '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY',             // ��֤���ַ�����
        'expire'    =>  1800,            // ��֤�����ʱ�䣨s��
        'useZh'     =>  false,           // ʹ��������֤��
        'zhSet'     =>  '�����ҵ�������ʱҪ��������һ�ǹ�������巢�ɲ���ɳ��ܷ������˲����д�����������Ϊ����������ѧ�¼��ظ���ͬ����˵�ֹ����ȸ�����Ӻ������С��Ҳ�����߱������������ʵ�Ҷ������ˮ������������������ʮս��ũʹ��ǰ�ȷ���϶�·ͼ�ѽ�������¿���֮��ӵ���Щ�������¶�����������˼�����ȥ�����������ѹԱ��ҵ��ȫ�������ڵ�ƽ��������ëȻ��Ӧ�����������ɶ������ʱ�չ�������û���������ϵ������Ⱥͷ��ֻ���ĵ����ϴ���ͨ�����Ͽ��ֹ�����������ϯλ����������ԭ�ͷ�������ָ��������ںܽ̾��ش˳�ʯǿ�������Ѹ���ֱ��ͳʽת�����о���ȡ������������־�۵���ôɽ�̰ٱ��������汣��ί�ָĹܴ�������֧ʶ�������Ϲ�רʲ���;�ʾ������ÿ�����������Ϲ����ֿƱ�������Ƹ��������������༯������װ����֪���е�ɫ����ٷ�ʷ����������֯�������󴫿ڶϿ��ɾ����Ʒ�вβ�ֹ��������ȷ������״��������Ŀ����Ȩ�Ҷ����֤��Խ�ʰ��Թ�˹��ע�첼�����������ر��̳�������ǧʤϸӰ�ð׸�Ч���ƿ��䵶Ҷ������ѡ���»������ʼƬʩ���ջ�������������ҩ����Ѵ��ʿ���Һ��׼��ǽ�ά�������������״����ƶ˸������ش幹���ݷǸ���ĥ�������ʽ���ֵ��̬���ױ�������������̨���û������ܺ���ݺ����ʼ��������Ͼ��ݼ���ҳ�����Կ�Ӣ��ƻ���Լ�Ͳ�ʡ���������ӵ۽�����ֲ������������ץ���縱����̸Χʳ��Դ�������ȴ����̻����������׳߲��зۼ������濼�̿�������ʧ��ס��֦�־����ܻ���ʦ������Ԫ����ɰ�⻻̫ģƶ�����ｭ��Ķľ����ҽУ���ص�����Ψ�们վ�����ֹĸ�д��΢�Է�������ĳ�����������൹�������ù�Զ���Ƥ����ռ����Ȧΰ��ѵ�ؼ��ҽ��ƻ���������ĸ�����ֶ���˫��������ʴ����˿Ůɢ��������Ժ�䳹����ɢ�����������������Ѫ��ȱ��ò�����ǳ���������������̴���������������Ͷ��ū����ǻӾഥ�����ͻ��˶��ٻ����δͻ�ܿ���ʪƫ�Ƴ�ִ����կ�����ȶ�Ӳ��Ŭ�����Ԥְ������Э�����ֻ���ì������ٸ�������������ͣ����Ӫ�ո���Ǯ��������ɳ�˳��ַ�е�ذ����İ��������۵��յ���ѽ�ʰɿ��ֽ�������������ĩ������ڱ������������������𾪶ټ�����ķ��ɭ��ʥ���մʳٲ��ھؿ��������԰ǻ�����������������ӡ�伱�����˷�¶��Ե�������������Ѹ��������ֽҹ������׼�����ӳ��������ɱ���׼辧�尣ȼ��������ѿ��������̼��������ѿ����б��ŷ��˳������͸˾Σ������Ц��β��׳����������������ţ��Ⱦ�����������Ƽ�ֳ�����ݷô���ͭ��������ٺ�����Դ��ظ���϶¯����úӭ��ճ̽�ٱ�Ѯ�Ƹ�������Ը���������̾䴿������������³�෱�������׶ϣ�ذܴ�����ν�л��ܻ���ڹ��ʾ����ǳ���������Ϣ������������黭�������������躮ϲ��ϴʴ���ɸ���¼������֬ׯ��������ҡ���������������Ű²��ϴ�;�������Ұ�ž�ıŪ�ҿ�����ʢ��Ԯ���Ǽ���������Ħæ�������˽����������������Ʊܷ�������Ƶ�������Ҹ�ŵ����Ũ��Ϯ˭��л�ڽ���Ѷ���鵰�պ������ͽ˽������̹����ù�����ո��伨���ܺ���ʹ�������������ж�����׷���ۺļ���������о�Ѻպ��غ���Ĥƪ��פ������͹�ۼ���ѩ�������������߲��������ڽ������˹�̿������������ǹ���ð������Ͳ���λ�����Ϳζ����Ϻ�½�����𶹰�Ī��ɣ�·쾯���۱�����ɶ���ܼ��Ժ��浤�ɶ��ٻ���ϡ���������ǳӵѨ������ֽ����������Ϸ��������ò�����η��ɰ���������ˢ�ݺ���������©�������Ȼľ��з������Բ����ҳ�����ײ����ȳ����ǵ������������ɨ������оү���ؾ����Ƽ��ڿ��׹��ð��ѭ��ף���Ͼ����������ݴ���ι�������Ź�ó����ǽ���˽�ī������ж����������ƭ�ݽ�',              // ������֤���ַ���
        'useImgBg'  =>  false,           // ʹ�ñ���ͼƬ
        'fontSize'  =>  25,              // ��֤�������С(px)
        'useCurve'  =>  true,            // �Ƿ񻭻�������
        'useNoise'  =>  true,            // �Ƿ�����ӵ�
        'imageH'    =>  0,               // ��֤��ͼƬ�߶�
        'imageW'    =>  0,               // ��֤��ͼƬ���
        'length'    =>  5,               // ��֤��λ��
        'fontttf'   =>  '',              // ��֤�����壬�����������ȡ
        'bg'        =>  array(243, 251, 254),  // ������ɫ
        'reset'     =>  true,           // ��֤�ɹ����Ƿ�����
    );

    private $_image   = NULL;     // ��֤��ͼƬʵ��
    private $_color   = NULL;     // ��֤��������ɫ

    private $_error_code = NULL;  //��֤�������� ��ʾ��֤��Ĵ�������  �����Լ��ӵı���

    /**
     * �ܹ����� ���ò���
     * @access public
     * @param  array $config ���ò���
     */
    public function __construct($config=array()){
        $this->config   =   array_merge($this->config, $config);
    }
    /* private function __construct($config=array()){
     $this->config   =   array_merge($this->config, $config);
     } */

    /* public static function __instance($config=array()){
     if(!(self::$_instance instanceof Verify)){
     self::$_instance= new self($config);
     }
     return self::$_instance;

     } */

    /**
     * ʹ�� $this->name ��ȡ����
     * @access public
     * @param  string $name ��������
     * @return multitype    ����ֵ
     */
    public function __get($name) {
        return $this->config[$name];
    }

    /**
     * ������֤������
     * @access public
     * @param  string $name ��������
     * @param  string $value ����ֵ
     * @return void
     */
    public function __set($name,$value){
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

    /**
     * �������
     * @access public
     * @param  string $name ��������
     * @return bool
     */
    public function __isset($name){
        return isset($this->config[$name]);
    }

    /**
     * ��֤��֤���Ƿ���ȷ
     * @access public
     * @param string $code �û���֤��
     * @param string $id ��֤���ʶ
     * @return bool �û���֤���Ƿ���ȷ
     */
    public function check($code, $id = '') {
        $key = $this->authcode($this->seKey).$id;
        // ��֤�벻��Ϊ��
        $secode = \Common::session($key);
        /*
         * �������ע�͵Ĵ�����ԭ���Ĵ���
        */
        /* if(empty($code) || empty($secode)) {

        return false;
        } */
        /*
         * ������δ������Լ���д�Ĵ���  ��ʼ
        */
        if(empty($code)){
            $this->setErrorCode(0);
            return false;
        }
        if(empty($secode)){
            $this->setErrorCode(1);
            return false;
        }
        /*
         * ����
         */
        // session ����
        if(NOW_TIME - $secode['verify_time'] > $secode['verify_expire']){
            \Common::session($key, null);
            $this->setErrorCode(2);
            return false;
        }
        //����

        if($this->authcode(strtoupper($code)) == $secode['verify_code']) {

            $this->reset && \Common::session($key, null);
            return true;
        }
        return false;
    }

    /**
     * �����Լ���ӵĺ��� ����������֤�����������
     * ��֤�����������    0 ��ʾ �������֤��Ϊ��   1��ʾ ��ǰ��֤����ʧЧ  2��ʾ ��ǰ��֤���ѹ���
     * ˵���� ��֤����ʧЧ����֤���ѹ��� ����ͬ
     *      ��֤����ʧЧ ���������
     *          1����������֤���Ѿ���ȷ��֤��һ�� ��ʱ�Ὣsession�б��浱ǰ��֤����Ϊ��
     *          2����������֤�������Ժ󣬺ܳ�ʱ��û��������֤�������� ��֤��ʱ�� ��֤���Ѿ�����ʧЧ
     *      ��֤����� ֻ����֤��ʧЧ�ĵڶ������
     *
     * @access private
     * @param string $code
     * @return void
     */
    private function setErrorCode($code=null){
        $this->_error_code= $code;
    }

    public function getErrorCode(){
        return $this->_error_code;
    }

    /**
     * �����֤�벢����֤���ֵ�����session��
     * ��֤�뱣�浽session�ĸ�ʽΪ�� array('verify_code' => '��֤��ֵ', 'verify_time' => '��֤�봴��ʱ��');
     * @access public
     * @param string $id Ҫ������֤��ı�ʶ
     * @return void
     */
    public function entry($id = '') {
        // ͼƬ��(px)
        $this->imageW || $this->imageW = $this->length*$this->fontSize*1.5 + $this->length*$this->fontSize/2;
        // ͼƬ��(px)
        $this->imageH || $this->imageH = $this->fontSize * 2.5;
        // ����һ�� $this->imageW x $this->imageH ��ͼ��
        $this->_image = imagecreate($this->imageW, $this->imageH);
        // ���ñ���
        imagecolorallocate($this->_image, $this->bg[0], $this->bg[1], $this->bg[2]);

        // ��֤�����������ɫ
        $this->_color = imagecolorallocate($this->_image, mt_rand(1,150), mt_rand(1,150), mt_rand(1,150));
        // ��֤��ʹ���������
        $ttfPath = dirname(__FILE__) . '/Verify/' . ($this->useZh ? 'zhttfs' : 'ttfs') . '/';

        if(empty($this->fontttf)){
            $dir = dir($ttfPath);
            $ttfs = array();
            while (false !== ($file = $dir->read())) {
                if($file[0] != '.' && substr($file, -4) == '.ttf') {
                    $ttfs[] = $file;
                }
            }
            $dir->close();
            $this->fontttf = $ttfs[array_rand($ttfs)];
        }
        $this->fontttf = $ttfPath . $this->fontttf;

        if($this->useImgBg) {
            $this->_background();
        }

        if ($this->useNoise) {
            // ���ӵ�
            $this->_writeNoise();
        }
        if ($this->useCurve) {
            // �������
            $this->_writeCurve();
        }

        // ����֤��
        $code = array(); // ��֤��
        $codeNX = 0; // ��֤���N���ַ�����߾�
        if($this->useZh){ // ������֤��
            for ($i = 0; $i<$this->length; $i++) {
                $code[$i] = iconv_substr($this->zhSet,floor(mt_rand(0,mb_strlen($this->zhSet,'utf-8')-1)),1,'utf-8');
                imagettftext($this->_image, $this->fontSize, mt_rand(-40, 40), $this->fontSize*($i+1)*1.5, $this->fontSize + mt_rand(10, 20), $this->_color, $this->fontttf, $code[$i]);
            }
        }else{
            for ($i = 0; $i<$this->length; $i++) {
                $code[$i] = $this->codeSet[mt_rand(0, strlen($this->codeSet)-1)];
//                 $codeNX  += mt_rand($this->fontSize*1.2, $this->fontSize*1.6);
                $codeNX  += mt_rand($this->fontSize*0.8, $this->fontSize*1);
//                 imagettftext($this->_image, $this->fontSize, mt_rand(-40, 40), $codeNX, $this->fontSize*1.6, $this->_color, $this->fontttf, $code[$i]);
                imagettftext($this->_image, $this->fontSize, mt_rand(-40, 40), $codeNX, $this->fontSize*1.4, $this->_color, $this->fontttf, $code[$i]);
            }
        }
         
        // ������֤��
        $key        =   $this->authcode($this->seKey);
        $code       =   $this->authcode(strtoupper(implode('', $code)));
        $secode     =   array();
        $secode['verify_code'] = $code; // ��У���뱣�浽session
        $secode['verify_time'] = NOW_TIME;  // ��֤�봴��ʱ��
        //�Լ��޸ĵĴ���
        $secode['verify_expire']=$this->config['expire']; //���ǽ�����ʱ�䱣������

        \Common::session($key.$id, $secode);

        header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header("content-type: image/png");

        // ���ͼ��
        imagepng($this->_image);
        imagedestroy($this->_image);
    }

    /**
     * ��һ������������һ�𹹳ɵ�������Һ���������������(����Ըĳɸ�˧�����ߺ���)
     *
     *      ���е���ѧ��ʽզ����������д����
     *		�����ͺ�������ʽ��y=Asin(��x+��)+b
     *      ������ֵ�Ժ���ͼ���Ӱ�죺
     *        A��������ֵ������������ѹ���ı�����
     *        b����ʾ������Y���λ�ù�ϵ�������ƶ����루�ϼ��¼���
     *        �գ�����������X��λ�ù�ϵ������ƶ����루����Ҽ���
     *        �أ��������ڣ���С������T=2��/�O�بO��
     *
     */
    private function _writeCurve() {
        $px = $py = 0;

        // ����ǰ����
        $A = mt_rand(1, $this->imageH/2);                  // ���
        $b = mt_rand(-$this->imageH/4, $this->imageH/4);   // Y�᷽��ƫ����
        $f = mt_rand(-$this->imageH/4, $this->imageH/4);   // X�᷽��ƫ����
        $T = mt_rand($this->imageH, $this->imageW*2);  // ����
        $w = (2* M_PI)/$T;

        $px1 = 0;  // ���ߺ�������ʼλ��
        $px2 = mt_rand($this->imageW/2, $this->imageW * 0.8);  // ���ߺ��������λ��

        for ($px=$px1; $px<=$px2; $px = $px + 1) {
            if ($w!=0) {
                $py = $A * sin($w*$px + $f)+ $b + $this->imageH/2;  // y = Asin(��x+��) + b
                $i = (int) ($this->fontSize/5);
                while ($i > 0) {
                    imagesetpixel($this->_image, $px + $i , $py + $i, $this->_color);  // ����(while)ѭ�������ص��imagettftext��imagestring�������Сһ�λ�����������whileѭ��������Ҫ�úܶ�
                    $i--;
                }
            }
        }

        // ���ߺ󲿷�
        $A = mt_rand(1, $this->imageH/2);                  // ���
        $f = mt_rand(-$this->imageH/4, $this->imageH/4);   // X�᷽��ƫ����
        $T = mt_rand($this->imageH, $this->imageW*2);  // ����
        $w = (2* M_PI)/$T;
        $b = $py - $A * sin($w*$px + $f) - $this->imageH/2;
        $px1 = $px2;
        $px2 = $this->imageW;

        for ($px=$px1; $px<=$px2; $px=$px+ 1) {
            if ($w!=0) {
                $py = $A * sin($w*$px + $f)+ $b + $this->imageH/2;  // y = Asin(��x+��) + b
                $i = (int) ($this->fontSize/5);
                while ($i > 0) {
                    imagesetpixel($this->_image, $px + $i, $py + $i, $this->_color);
                    $i--;
                }
            }
        }
    }

    /**
     * ���ӵ�
     * ��ͼƬ��д��ͬ��ɫ����ĸ������
     */
    private function _writeNoise() {
        $codeSet = '2345678abcdefhijkmnpqrstuvwxyz';
        for($i = 0; $i < 10; $i++){
            //�ӵ���ɫ
            $noiseColor = imagecolorallocate($this->_image, mt_rand(150,225), mt_rand(150,225), mt_rand(150,225));
            for($j = 0; $j < 5; $j++) {
                // ���ӵ�
                imagestring($this->_image, 5, mt_rand(-10, $this->imageW),  mt_rand(-10, $this->imageH), $codeSet[mt_rand(0, 29)], $noiseColor);
            }
        }
    }

    /**
     * ���Ʊ���ͼƬ
     * ע�������֤�����ͼƬ�Ƚϴ󣬽�ռ�ñȽ϶��ϵͳ��Դ
     */
    private function _background() {
        $path = dirname(__FILE__).'/Verify/bgs/';
        $dir = dir($path);

        $bgs = array();
        while (false !== ($file = $dir->read())) {
            if($file[0] != '.' && substr($file, -4) == '.jpg') {
                $bgs[] = $path . $file;
            }
        }
        $dir->close();

        $gb = $bgs[array_rand($bgs)];

        list($width, $height) = @getimagesize($gb);
        // Resample
        $bgImage = @imagecreatefromjpeg($gb);
        @imagecopyresampled($this->_image, $bgImage, 0, 0, 0, 0, $this->imageW, $this->imageH, $width, $height);
        @imagedestroy($bgImage);
    }

    /* ������֤�� */
    private function authcode($str){
        $key = substr(md5($this->seKey), 5, 8);
        $str = substr(md5($str), 8, 10);
        return md5($key . $str);
    }

}
