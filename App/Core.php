<?php
/**
 * ����Ӧ�ø�Ŀ¼����
 * @var APP_PATH
 */
defined('DOC_ROOT') or define('DOC_ROOT',$_SERVER['DOCUMENT_ROOT'].'/');

/**
 * �������Ŀ¼
 */
defined('SERVICE_PATH') or define('SERVICE_PATH',DOC_ROOT.'Service/');
/**
 * ����Ӧ��������Ŀ¼
 */
defined('APP_PATH') or define('APP_PATH',DOC_ROOT.'App/');
/**
 * ����ӿ�·��
 */
defined('INTERFACE_PATH') or define('INTERFACE_PATH',APP_PATH.'Onmpw/Interface/');
/**
 * �������Ŀ¼
 */
defined('LIB_PATH') or define('LIB_PATH',APP_PATH.'Onmpw/Lib/');

/**
 * �����������չĿ¼
 */
 defined('EXT_PATH') or define('EXT_PATH',APP_PATH."Onmpw/Ext/");

/**
 * ���幫��Ŀ¼
 */
 defined('COMMON_PATH') or define('COMMON_PATH',APP_PATH ."Common/");

/**
 * ���������ļ�Ŀ¼
 */
 defined('CONFIG_PATH') or define('CONFIG_PATH',APP_PATH ."Config/");

 /**
  * ���� dataĿ¼
  */
 defined('DATA_PATH') or define('DATA_PATH',DOC_ROOT.'Data/');
 
 /**
  * ����ģ��Ŀ¼
  */
 defined('MODULE_PATH') or define('MODULE_PATH',DOC_ROOT.'Module/');
 
 /**
  * ���嵱ǰʱ��
  * @var unknown
  */
 define('NOW_TIME',      $_SERVER['REQUEST_TIME']);
 

 /**
  * ���빫����(Common) �ļ� �͹����������ļ�
  */
 include COMMON_PATH."Common.php";
/**
 * �������������ļ���·������
 * @param string $path
 */
function _set_include_path($path = ''){
    if(!empty($path)){
        if($path != get_include_path()){
            if(is_dir($path)){
                set_include_path($path);
            }
        }
    }
}

/**
 * ���� �������ļ� Onmpw
 */
Common::Import("#/Onmpw/Onmpw");
use Onmpw\Onmpw as Kernel;
class Core extends Kernel{
    public static function _Init()
    {
        // �ں˳�ʼ��
        parent::_Init();

        // ������Ҫ��ʼ���Ĺ���
        foreach(self::$_inits as $class){
            call_user_func([new $class,'_Init']);
        }
    }
}
