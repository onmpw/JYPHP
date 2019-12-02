<?php


class App
{
    private $bindings = [];

    public $containers = [];

    private $parameter = [];


    /**
     * App constructor.
     */
    public function __construct()
    {
        $this->instance(App::class,$this);
    }

    /**
     * 工厂方法
     *
     * @param $abstract
     * @param array $parameters
     * @return object
     * @throws ReflectionException
     */
    public function make($abstract, array $parameters = [])
    {
        return $this->resolve($abstract,$parameters);
    }

    /**
     * 注册一个单例
     * @param $abstract
     * @param null $concrete
     */
    public function singleton($abstract,$concrete = null)
    {
        $this->bind($abstract,$concrete,true);
    }

    /**
     * @param $abstract
     * @param $concrete
     * @param bool $single
     */
    public function bind($abstract,$concrete = null, $single = false)
    {
        if(isset($this->bindings[$abstract])){
            unset($this->bindings[$abstract]);
        }

        if(is_null($concrete)){
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = compact('concrete','single');
    }

    /**
     * 实例化对象(对象都为单例)
     * @param $abstract
     * @param $object
     */
    public function instance($abstract,$object)
    {
        $this->containers[$abstract] = $object;
    }


    /**
     * 返回具体实例化对象
     *
     * @param $abstract
     * @param array $parameters
     * @return mixed
     *
     * @throws ReflectionException
     */
    private function resolve($abstract,$parameters = [])
    {
        $needBuild = !empty($parameters);

        if(isset($this->containers[$abstract]) && !$needBuild){
            return $this->containers[$abstract];
        }

        $this->parameter[] = $parameters;

        $concrete = $this->getConcrete($abstract);

        if($this->isCanBuild($concrete,$abstract)){
            $object = $this->build($concrete);
        }else{
            $object = $this->make($concrete);
        }

        if($this->isSingled($abstract) && !$needBuild) {
            $this->containers[$abstract] = $object;
        }

        array_pop($this->parameter);

        return $object;

    }

    /**
     * 是否是单例
     *
     * @param $abstract
     * @return mixed
     */
    private function isSingled($abstract)
    {
        return isset($this->bindings[$abstract]) ? $this->bindings[$abstract]['shared']:false;
    }

    /**
     * 是否可以实例化
     *
     * @param $concrete
     * @param $abstract
     * @return bool
     */
    private function isCanBuild($concrete,$abstract)
    {
        return $concrete == $abstract || $concrete instanceof Closure;
    }

    /**
     * 获取绑定的需要实例化的具体的类或闭包
     *
     * @param $abstract
     * @return mixed
     */
    private function getConcrete($abstract)
    {
        if(isset($this->bindings[$abstract]) && !empty($this->bindings[$abstract])){
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * 根据给定的类型实例化对象
     *
     * @param $concrete
     * @return mixed
     *
     * @throws ReflectionException
     * @throws Exception
     */
    private function build($concrete)
    {
        // 如果是闭包，则是自定义的实例化对象
        if($concrete instanceof Closure) {
            return $concrete($this,$this->getLastParameter());
        }

        // 使用反射实例化该类
        $reflector = new ReflectionClass($concrete);

        // 检测是否有构造函数
        $constructor = $reflector->getConstructor();

        if(is_null($constructor)){
            // 没有构造函数， 则直接返回new对象
            return new $concrete;
        }

        $parameters = $constructor->getParameters();

        $instances = [];
        foreach($parameters as $parameter){
            if($this->hasParameter($parameter)){
                $instances[] = $this->getLastParameter()[$parameter->name];
                continue;
            }
            $instances[] = is_null($parameter->getClass())
                            ? $this->resolveParameter($parameter)
                            : $this->buildClass($parameter->getClass()->name);
        }

        return $reflector->newInstanceArgs($instances);


    }

    /**
     * 是否指定了参数
     *
     * @param $parameter
     * @return bool
     */
    private function hasParameter($parameter)
    {
        return array_key_exists($parameter->name,$this->getLastParameter());
    }

    /**
     *
     * @param $parameter
     * @return mixed
     * @throws Exception
     */
    private function resolveParameter(ReflectionParameter $parameter)
    {
        if($parameter->isDefaultValueAvailable()){
            return $parameter->getDefaultValue();
        }
    }

    /**
     * 实例化指定class的对象作为参数
     *
     * @param $abstract
     * @return mixed
     * @throws ReflectionException
     */
    private function buildClass($abstract)
    {
        return $this->make($abstract);
    }

    /**
     * 返回实例化对象需要的参数
     *
     * @return mixed
     */
    private function getLastParameter()
    {
        return count($this->parameter) ? end($this->parameter) : [];

    }
}