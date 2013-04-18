tudu-web
========

图度云办公系统安装说明

运行环境
操作系统：Linux/Unix/Windows（仅Web服务）
数 据 库：mysql
Web 服务：nginx/Apache（自行配置）
队列服务：httpsqs
其他：memcache（或其他兼容memcache协议的服务）,coreseek

PHP (>=5.2)
必要扩展：
PDO
PDO_MYSQL
memcache
mbstring
simplexml


1.自动安装（仅支持CentOS 5.x）
  自动安装会为用户安装图度系统需要的所有组件，并配置好相关的服务基础环境。
  安装步骤：
    1.解压安装包，并赋予目录和文件正确的访问权限
    2.执行install/tudu.sh，过程需要输入域名，组件安装目录，数据库根密码等
    3.执行完成后，通过浏览器访问第二部时输入的域名（域名解析请提前做好）
    4.根据Web安装向导，进行系统环境的初始化
    5.完成安装
