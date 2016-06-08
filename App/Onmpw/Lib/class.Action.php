<?php
namespace Lib;

class Action{
    
    private $_obj = array(); //���ʵ�����Ķ���
    
    /**
     * �Զ�����ʾģ�庯��
     * 
     * @param string $tpl
     */
    protected function display($tpl = ''){
        
        if(!isset($this->_obj['tpl']))
            $this->_obj['tpl'] = Template::_newInstance();
        /*
         * �����жϲ���$tpl�Ƿ�Ϊ�� ���Ϊ�գ���ô����Ĭ�ϵĵ�ǰģ������ķ������ҳ��
         */
        $this->_obj['tpl']->_setTplFoulder(AC_NAME);
        if(empty($tpl)){
            //�õ���ǰ
            $this->_obj['tpl']->_setTplName(strtolower(FC_NAME));
        }elseif(is_string($tpl)){
            if(false === strpos($tpl,':')){
                $this->_obj['tpl']->_setTplName(strtolower($tpl));
            }else{
                $args = explode(':', $tpl);
                if(!empty($args[0])){
                    $this->_obj['tpl']->_setTplFoulder($args[0]);
                }
                $this->_obj['tpl']->_setTplName(strtolower($args[1]));
            }
        }
        $this->_obj['tpl']->_display();
        return ;
    }
    /**
     * �Զ���ģ��������亯��
     * @param unknown $tpl_var ��������
     * @param string $val   ����ֵ
     */
    protected function assign($tpl_var, $val = null){
        if(!isset($this->_obj['tpl']))
            $this->_obj['tpl'] = Template::_newInstance();
        $this->_obj['tpl']->_assign($tpl_var,$val);
        
        return ;
    }
}