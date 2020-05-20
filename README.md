# JYPHP
一个简单易用的PHP框架。
***
* [项目简介](#project_descrbe)
* [安装]()
##### <span id="project_descrbe">项目简介</span>

最初开发这个项目的目的就是用来学习的。最开始在接触PHP的时候一直就是使用原生代码加HTML来开发，感觉效率很低。直到后来接触到第一个框架才发现原来PHP还可以这样用，很高大上。
后来在开发过程中陆续的接触了其他的框架，渐渐的就对框架的实现原理和底层的架构产生了兴趣。于是萌生了自己也开发一个框架的想法，当然主要是想通过实际动手能更好的理解和运用这些原理和模式。

最初的想法就是这么简单。但是理想很丰满，现实却是残酷的，开发的过程是比较艰难的。由于工作的原因，中间也是有将近一年的时间没有更新。现在时间相对有些宽松，可以继续实践。
>  在此刻，框架主体已经搭建完成，而且也已经填充了一些内容。基本上是可以使用了，后续会陆续添加新的功能，并且更新相应的文档。

##### <span id="project_install">安装</span>
安装相对来说还是比较简单的，主要有两种方式。
###### 一、 原始安装方式
使用git 拉取代码到安装目录，然后执行`composer update`
```bash
$ git clone https://github.com/onmpw/JYPHP.git
$ cd JYPHP
$ composer update
```
然后等着安装完成就可以了。

至于整个环境，可以用apache也可以用nginx。这里我推荐使用nginx+php的方式。
###### 二、docker一键部署
随着容器部署的渐行渐近，在学习的过程中也尝试着将此框架用docker打包部署。里面的环境也是我上面推荐的 nginx+php。 对于数据库那肯定是php的最佳搭档mysql了。

部署方式很简单，首先需要在我们自己的服务器或者pc上面安装`docker`和`docker-compose`。
> 具体怎么安装docker 和 docker-compse 可以去docker官网或者google/百度去搜索。

如果安装上了docker和docker-compose。 那接下来就简单了。首先也是使用git将代码获取到本地。接下来就是进入目录 执行deploy.sh shell安装脚本。

```bash
$ git clone https://github.com/onmpw/JYPHP.git
$ cd JYPHP
$ ./deploy.sh
    docker 服务正常
    docker-compose 正常
    下面配置服务项
    Checking If The Data Dir Exist...
    Checking If The File .env Exist...
    数据库名称 (jiyi): 输入默认使用的数据库名称
    root用户密码(123456): root账户的密码
```
该脚本提供了四个参数 up(默认) start stop rm。 
* up: 创建容器并运行
* start: 运行服务
* stop: 停止服务
* rm: 删除服务
在安装过程中开始会检测是否有安装docker和docker-compose。 然后是检查并创建一些必要的文件。接着就是输入数据库的两个简单的配置项。

目前部署脚本比较简单，后续会慢慢完善...

> 这里需要注意的是一定要进入到项目根目录下面执行 ./deploy.sh 不要在项目之外用绝对路径执行shell脚本，目前还不支持这样做。

部署完成之后（由于需要安装整个环境，所以比较慢）, 需要登入到mysql中根据需要创建账号。创建完数据库访问用户之后，编辑 .env 配置文件对数据库的配置项进行修改。

对于nginx和php-fpm的配置文件，在项目中是有提供的，可以根据自己的需要进行修改。

