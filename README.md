图度云办公系统
========

图度是国内知名互联网服务商Oray自主研发的一款完全免费的企业办公系统。完全颠覆传统OA，为企业、项目组以及任何需要协同工作的大中小型工作团体等提供工作管理、项目管理、工作流管理等在线应用服务。

http://www.tudu.com/

运行环境
--------
操作系统：Linux/Unix/Windows<br />
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
