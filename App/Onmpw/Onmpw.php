<?php

//namespace Onmpw;

use Exceptions\FileNotFoundException;
use Exceptions\HandlerExceptions;
use Lib\Router;

/**
 * �������ļ� ������������Ӧ�ó���
 *
 * @author liuhanzeng
 *
 */
class Onmpw
{

    private static $_map = array();

    private static $EXT = '.php';


    /**
     *
     * @var array
     */
    protected static $_inits = [
        HandlerExceptions::class
    ];

    protected static function _Init()
    {

        // ���������ļ���·��
        _set_include_path(APP_PATH);

        //����session
        session_start();

        //ע���Զ�������|�ӿڷ���
        spl_autoload_register(['self', 'autoload']);
        require APP_PATH . '../vendor/autoload.php';
    }

    /**
     * �Զ����뺯��
     *
     * @param string $class
     *
     * @return bool
     */
    protected static function autoload($class)
    {
        $after_ext = '.php';
        if (!isset(self::$_map[$class]) || empty(self::$_map[$class])) {
            //���� \ ��һ�γ��ֵ�λ��֮ǰ���ַ���
            $name = strstr($class, '\\', true);

            // ����Զ����ص��� ��Lib��Ext��Inter Exceptions �е����ļ����߽ӿ��ļ���ô����ִ��
            if (in_array($name, array('Lib', 'Ext', 'Inter', 'Exceptions'))) {
                $class_name = str_replace('\\', '/', $class);
                $path = APP_PATH . 'Onmpw/';
                $struct = explode('/', $class_name);
                $file_name = substr($class_name, 0, -strlen($struct[count($struct) - 1])) . $struct[count($struct) - 1] . $after_ext;
            } else {
                $pre_ext = 'Action.';
                $class_name = str_replace('\\', '/', $class);

                // ������������ߵ������������ļ��Ļ�����ô����Ƿ��ǿ������ļ�
                $name = ltrim(strrchr($class_name, '/'), '/'); //����/���ַ��������һ�γ��ֵ�λ��֮����ַ���

                if (preg_match('/^[A-Z]?\w*Model$/', $name)) {
                    $pre_ext = 'Model.';
                }
                $name = $pre_ext . str_replace(rtrim($pre_ext, '.'), '', $name);//ȥ��Action���Ҽ���ǰ׺Action.

                $path = MODULE_PATH;  //����·��
                //�����ļ�����
                $file_name = str_replace(strrchr($class_name, '/'), '/' . $name . $after_ext, $class_name);
            }

            if (file_exists($path . $file_name)) {
                self::$_map[$class] = $path . $file_name;
                /** @noinspection PhpIncludeInspection */
                require self::$_map[$class];
            }
        }

        return true;

    }

    public static function start()
    {
        /*
         * ���ع��������ļ�
         */
        if (file_exists(CONFIG_PATH . 'app' . self::$EXT)) {
            \Common::C(\Common::Load_conf(CONFIG_PATH . 'app' . self::$EXT));
        }

        // ��ʼ·��
        Router::router();
    }

}