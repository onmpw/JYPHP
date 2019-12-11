<?php
namespace Lib;

use Symfony\Component\HttpFoundation\Response;

class Action{
    
    private $_obj = array(); //存放实例化的对象

    private $response;
    
    /**
     * 自定义显示模板函数
     * 
     * @param string $tpl
     */
    protected function display($tpl = ''){
        
        if(!isset($this->_obj['tpl'])) {
            $this->_obj['tpl'] = Template::_newInstance();
        }
        /*
         * 首先判断参数$tpl是否为空 如果为空，那么按照默认的当前模块下面的方法输出页面
         */
        $this->_obj['tpl']->_setTplFoulder(AC_NAME);
        if(empty($tpl)){
            //得到当前
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
     * 自定义模板变量分配函数
     * @param unknown $tpl_var 变量名称
     * @param string $val   变量值
     */
    protected function assign($tpl_var, $val = null){
        if(!isset($this->_obj['tpl'])) {
            $this->_obj['tpl'] = Template::_newInstance();
        }
        $this->_obj['tpl']->_assign($tpl_var,$val);
        
        return ;
    }

    public function send($message)
    {
        $response = new Response(
            'Content',
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );

        $response->setContent($message);
        $response->send();
    }
}