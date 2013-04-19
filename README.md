图度云办公系统
========

图度是国内知名互联网服务商Oray自主研发的一款完全免费的企业办公系统。完全颠覆传统OA，为企业、项目组以及任何需要协同工作的大中小型工作团体等提供工作管理、项目管理、工作流管理等在线应用服务。

http://www.tudu.com/

运行环境
--------
操作系统：Linux/Unix/Windows(Windows可运行Web、PHP、mysql等支持Windows的服务，必要服务如：httpsqs，需要Linux/Unix系统运行)<br />
数 据 库：mysql<br />
Web 服务：nginx/Apache<br />
队列服务：httpsqs<br />
其他：memcache（或其他兼容memcache协议的服务）,coreseek<br />

PHP (>=5.2)<br />
必要扩展：<br />
PDO<br />
PDO_MYSQL<br />
memcache<br />
mbstring<br />
simplexml<br />


自动安装脚本（ install/centos.sh，仅支持CentOS 5.x ）<br />
--------
脚本会自动安装图度系统需要的所有组件，并配置好相关的服务基础环境。<br />
安装步骤：<br />
1.解压安装包，并赋予目录和文件正确的访问权限<br />
2.执行install/centos.sh，过程需要输入自定义访问的域名、组件安装目录、数据库根密码等<br />
3.执行完成后，通过浏览器访问第二步时输入的域名（域名解析请提前做好）<br />
4.根据Web安装向导，进行系统环境的初始化<br />
5.完成安装<br />

手工配置<br />
--------
1.安装配置上述运行环境中所需的服务组件
2.初始化数据库，install/sql中的sql文件为数据库初始化内容，按文件名顺序执行。注意客户端连接必须使用UTF8编码(SET NAMES UTF8)

3.install/conf中提供部分服务的配置模版
  nginx-tudu.conf Nginx站点配置模板，修改域名，路径等
  php.ini：PHP环境配置文件
  php-fpm：php-fpm服务配置文件
  tudu.conf 全文检索服务配置文件，需要填写部分数据库主机，密码等

4.以上配置完成后打开，修改/install/conf/config.ini.dist填写对应的配置信息后复制重命名为config.ini并复制/移动到/install目录

5.在浏览器中打开站点可执行Web安装程序
