<?php
namespace Lib;

class Template{
    
    private $_tpl_ext = '';  //ģ����չ��
    
    private $_smarty_obj = '';
    
    private $_var = array();
    
    public static $temobj;
    /*
     * һ������ֻ��һ��ģ��ʵ��
     */
    private function __construct(){
        $this->_tpl_ext = '.html';
        \Common::Ext('Smarty:Smarty@class');
        $this->_setTemplate_constants(\Common::C('CONSTANTS'));
        $this->_smarty_obj = new \Smarty();
        $this->_smarty_obj->setTemplateDir(MODULE_PATH.MODULE_NAME.'/tpl');
        $this->_smarty_obj->setCompileDir(DATA_PATH.'compile/'.MODULE_NAME);
        $this->_smarty_obj->setCacheDir(DATA_PATH.'cache/');
        $this->_smarty_obj->setConfigDir(DATA_PATH.'config/');
        $this->_smarty_obj->registerFilter('pre', '\\Common::smarty_constant_filter');
    }
    
    public static function _newInstance(){
        if(self::$temobj instanceof self){
            return new self::$temobj;
        }
        return new self;
    }
    
    /**
     * ����smarty ģ��Ŀ¼
     * 
     * @param string $template_dir
     */
    public function _setTemplateDir($template_dir = ''){
        if(empty($template_dir)){
            $this->_smarty_obj->setTemplateDir(MODULE_PATH.MODULE_NAME.'/tpl');
        }else{
            $this->_smarty_obj->setTemplateDir($template_dir);
        }
    }
    
    /**
     * ����smarty����Ŀ¼
     * 
     * @param string $compile_dir
     */
    public function _setCompileDir($compile_dir = ''){
        if(empty($compile_dir)){
            $this->_smarty_obj->setCompileDir(\Common::C('COMPILE_DIR'));
        }else{
            $this->_smarty_obj->setCompileDir($compile_dir);
        }
    }
    
    /**
     * ����smarty����Ŀ¼
     * 
     * @param string $cache_dir
     */
    public function _setCacheDir($cache_dir = ''){
        if(empty($cache_dir)){
            $this->_smarty_obj->setCacheDir(\Common::C('CACHE_DIR'));
        }else{
            $this->_smarty_obj->setCacheDir($cache_dir);
            
        }
    }
    
    /**
     * ����smarty����Ŀ¼
     * 
     * @param string $config_dir
     */
    public function _setConfigDir($config_dir = ''){
        if(empty($config_dir)){
            $this->_smarty_obj->setConfigDir(\Common::C('CONFIG_DIR'));
        }else{
            $this->_smarty_obj->setConfigDir($config_dir);
        }
    }
    /**
     * Ϊģ���������ֵ
     * 
     * @param mixed $tpl_var
     * @param string $val
     */
    public function _assign($tpl_var, $val = null){
        if(is_array($tpl_var)){
            $this->_smarty_obj->assign($tpl_var);
        }else{
            $this->_smarty_obj->assign($tpl_var,$val);
        }
    }
    
    /**
     * ��ʾģ���ļ�
     * 
     * @param string $tpl
     */
    public function _display($tpl = ''){
        if(empty($tpl)){
            $this->_smarty_obj->display($this->_var['dis_foulder'].'/'.$this->_var['dis_tpl_name'].$this->_tpl_ext);
        }else{
            $this->_smarty_obj->display($tpl.$this->_tpl_ext);
        }
    }
    
    /**
     * ����ģ���ļ���չ��
     * 
     * @param string $ext
     */
    public function _setTplExt($ext = ''){
        if(!empty($ext)){
            $this->_tpl_ext = $ext;
        }
    }
    
    /**
     * ����ģ���ļ������ļ�������
     * @param unknown $val
     */
    public function _setTplFoulder($val){
        $this->_var['dis_foulder'] = $val;
    }
    
    /**
     * ����ģ���ļ�����
     * @param unknown $val
     */
    public function _setTplName($val){
        $this->_var['dis_tpl_name'] = $val;
    }
    
    public function _setTemplate_constants($const = array()){
        if(is_array($const)){
            foreach($const as $_key=>$_val){
                defined($_key) or define($_key,$_val);
            }
        }
    }
}