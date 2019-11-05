<?php
/**
 * ���ߣ�����
 * ���˲��ͣ����䲩��
 * ����url��www.onmpw.com
 * ************
 * onmpwSessionHandler��  ��дsession����
 * ��session�浽redis���ݿ���
 * ************
 */
namespace Lib\Session;
class onmpwSessionHandler implements \SessionHandlerInterface{
    
    private $save_handle = '';
    private $reconnect = false;  //�Ƿ���������  Ĭ�ϲ���������
    private $handle = '';
    private $auth = null;   //�Ƿ����û���֤��Ĭ����������֤���������Ϊnull����Ϊ��֤����
    private $prefix = 'onmpw_PHPSESSION';
    private $config = array(
        'SAVE_HANDLE'=>'Redis',
        'HOST'=>'127.0.0.1',
        'PORT'=>6379,
        'AUTH'=>null,    //�Ƿ����û���֤��Ĭ����������֤���������Ϊnull����Ϊ��֤����
        'TIMEOUT'=>0,   //���ӳ�ʱ
        'RESERVED'=>null,
        'RETRY_INTERVAL'=>100,  //��λ�� ms ����
        'RECONNECT'=>false, //���ӳ�ʱ�Ƿ�����  Ĭ�ϲ�����
    );
    
    public function __construct($config = array()){
        
        if(!empty($config)) $this->config = array_merge($this->config,$config);
        
        $this->parseConfig();
    }
    public function parseConfig(){
        
        $this->save_handle = $this->config['SAVE_HANDLE'];
        
        $this->reconnect = $this->config['RECONNECT'];
        
        $this->auth = $this->config['AUTH'];
    }

    /**
     * Redis����������
     * @param string $host ������ַ
     * @param int $port ���Ӷ˿�
     * @param int $timeout ���ӳ�ʱʱ��
     * @param string $reserved
     * @param int $retry_interval
     *
     * @return boolean
     * @throws \RedisException
     */
    public function redisConnect($host = '127.0.0.1',$port = 6379,$timeout = 0,$reserved = null,$retry_interval = 100){
        //ʵ����Redis����
        try{
            
            $this->handle = new \Redis();
            
        }catch(\RedisException $e){
            
            throw $e;
        
        }
        
        
        /*
         * �ж��Ƿ��������� 
         */
        if(!$this->reconnect){
            
            $this->handle->connect($host,$port,$timeout);
            
        }else{
            
            $this->handle->connect($host,$port,$timeout,$reserved,$retry_interval);
            
        }
        /*
         * �ж��Ƿ���������֤
         * ��������֤�������֤�ſɼ��������Ĳ���
         */
        if(!is_null($this->auth)){
            
            $this->handle->auth($this->auth);
            
        }
        return true;
    }
    
    /**
     * ��������
     */
    private function parseConnect(){
        
        if($this->save_handle == 'Redis'){
            
            $this->redisConnect($this->config['HOST'], $this->config['PORT'], $this->config['TIMEOUT'], $this->config['RESERVED'], $this->config['RETRY_INTERVAL']);
        
        }
        
    }
    
    /**
     * ��session_start()���������õ�ʱ��ú���������
     * 
     * @see SessionHandlerInterface::open()
     */
    public function open($save_path, $name){
        /*
         * �������ӷ�����
         */
        $this->parseConnect();
        return true;
        
    }
    
    /**
     * �رյ�ǰsession
     * ��session�رյ�ʱ��ú����Զ�������
     * 
     * @see SessionHandlerInterface::close()
     */
    public function close(){
        return true;
    }
    
    /**
     * ��session�洢�ռ��ȡsession�����ݡ�
     * ������session_start()������ʱ��ú����ᱻ����
     * ������session_start()�������õ�ʱ���ȴ���open�������ٴ����ú���
     * 
     * @see SessionHandlerInterface::read()
     */
    public function read($session_id){
        /*
         * ����sessionId �������
         */
        $key = $this->prefix.':'.$session_id;
        //��ȡ��ǰsessionid�µ�data����
        $res = $this->handle->hGet($key,'data');
        //��ȡ����Ժ� ����ʱ�䣬˵���Ѿ�������session
        $this->handle->hSet($key,'last_time',time());
        return $res;
        
    }

    /**
     * ��session������д�뵽session�Ĵ洢�ռ��ڡ�
     * ��session׼���ô洢�͹رյ�ʱ����øú���
     *
     * @param $session_id
     * @param $session_data
     * @return bool
     * @see SessionHandlerInterface::write()
     */
    public function write($session_id, $session_data){
        /*
         * ����sessionId �������
         */
        $key = $this->prefix.':'.$session_id;
        //�鿴�ü������Ƿ����
        if(!$this->handle->exists($key)){
            /*
             * ������������µ�����
             * ����������ʱ��
             */
            $this->handle->hset($key,'last_time',time());
        }else{
            /*
             * ���ڣ�����¸ü�ֵ
             */
            $this->handle->hMset($key,array('last_time'=>time(),'data'=>$session_data));
        }
        return true;
        
    }

    /**
     * ����session
     *
     * @param $session_id
     * @see SessionHandlerInterface::destroy()
     */
    public function destroy($session_id){
        /*
         * ����sessionId �������
         */
        $key = $this->prefix.':'.$session_id;
        $this->handle->hDel($key,'data');
    }

    /**
     * �������session��Ҳ����������ڵ�session��
     * �ú����ǻ���php.ini�е�����ѡ��
     * session.gc_divisor, session.gc_probability �� session.gc_lifetime�����õ�ֵ��
     *
     * @param $maxlifetime
     * @see SessionHandlerInterface::gc()
     */
    public function gc($maxlifetime){
        /*
         * ȡ�����е� ����ָ��ǰ׺�ļ�
         */
        $keys = $this->handle->keys($this->prefix.'*');
        
        $now =time(); //ȡ�����ڵ�ʱ��
        foreach($keys as $key){
            //ȡ�õ�ǰkey��������ʱ��
            $last_time = $this->handle->hGet($key,'last_time');
            /*
             * �鿴��ǰʱ������ĸ���ʱ���ʱ����Ƿ񳬹������������
             */
            if(($now - $last_time) > $maxlifetime){
                //�����������������ʱ�� ��ɾ����key
                $this->handle->del($key);
            }
            
        }
        
    }
}