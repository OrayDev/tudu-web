#!/bin/bash
PATH=/bin:/sbin:/usr/local/bin:/usr/local/sbin:/usr/bin:/usr/sbin:~/bin
export PATH

tmp_dir="/tmp/tudu_install"
ver=`uname -m`
user_name="tuduweb"
install_dir=
dir=`pwd`
script_dir=`echo ${dir%/*}`
file_dir="/www"

#check file path
if [ ! -d "$script_dir/htdocs" ];then
	echo 'File path error,please do not move any file away from the source package!'
	exit 0;
fi

#check if user is root
if [ $(id -u) != "0" ]; then
    echo "Error: You must be root to run this script, please use root to install Tudu"
    exit 1
fi

autoconf_dl="http://mirrors.ustc.edu.cn/gnu/autoconf/autoconf-2.64.tar.gz"
automake_dl="http://mirrors.ustc.edu.cn/gnu/automake/automake-1.12.tar.gz"
libtool_dl="http://mirror.bjtu.edu.cn/gnu/libtool/libtool-2.4.2.tar.gz"
freetds_dl="http://ibiblio.org/pub/Linux/ALPHA/freetds/stable/freetds-stable.tgz"
pcre_dl="ftp://ftp.csx.cam.ac.uk/pub/software/programming/pcre/pcre-8.21.tar.gz"
nginx_dl="http://nginx.org/download/nginx-0.8.55.tar.gz"
php_dl="http://www.php.net/get/php-5.3.20.tar.gz/from/cn2.php.net/mirror"
php_memcache_dl="http://pecl.php.net/get/memcache-2.2.6.tgz"
php_eaccelerator_dl="https://github.com/eaccelerator/eaccelerator/tarball/master"
httpsqs_dl="http://httpsqs.googlecode.com/files/httpsqs-1.7.tar.gz"
libevent_dl="https://github.com/downloads/libevent/libevent/libevent-2.0.21-stable.tar.gz"
tokyocabinet_dl="http://fallabs.com/tokyocabinet/tokyocabinet-1.4.41.tar.gz"
tokyotyrant_dl="http://fallabs.com/tokyotyrant/tokyotyrant-1.1.39.tar.gz"
sphinx_dl="http://download.oray.com/tudu/coreseek-3.2.14.tar.gz"

clear
echo "========================================================================="
echo "Tudu v0.1.6 for CentOS 5 Linux by Tudu"
echo "========================================================================="
echo "A tool for auto-compiling & installing Nginx+MySQL+PHP+Tudu on Linux "
echo ""
echo "For more information please visit http://www.tudu.com/"
echo "========================================================================="

mkdir -p $file_dir
cp -r $script_dir/* $file_dir/
echo "The tudu web file will locate in $file_dir/"

#set main domain name
echo "========================================================================="
domain="tudu.com"
echo "Please input your domain:"
read -p "(Default domain: tudu.com):" domain
if [ "$domain" = "" ]; then
	domain="tudu.com"
fi
	
#set mysql root password
echo "========================================================================="
while [ 1 ]
do
	mysql_root_pwd=
	echo "Please input the root password of mysql:"
	read -p "( Default password: root ):" mysql_root_pwd
	if [ "$mysql_root_pwd" = "" ]; then
		mysql_root_pwd="root"
	
	fi
	mrpconfirm=
	echo "========================================================================="
	echo "mysql root password is $mysql_root_pwd"
	read -p "(Confirm please input: y ,if not please press the enter button):" mrpconfirm
	case "$mrpconfirm" in
	y|Y|Yes|YES|yes|yES|yEs|YeS|yeS)
	break
	;;
	n|N|No|NO|no|nO)
	continue
	;;
	*)
	continue
	esac
done

#add web user & tmp floder
if [ ! -d $tmp_dir ]; then
	mkdir -p $tmp_dir
fi
cd $tmp_dir || exit 1

user_exit=`id -u $user_name`
if [ -z "$user_exit" ]; then
	echo "Create user $user_name"
	/usr/sbin/groupadd $user_name
	/usr/sbin/useradd -g $user_name -s /sbin/nologin $user_name
fi

while [ 1 ]
do
	echo "========================================================================="
	echo "Please input a path for web server & plugin installtion:"
	read -p "( Default installtion path:/usr/local/ ):" install_dir
	if [ "$install_dir" = "" ]; then
		install_dir="/usr/local/"
	fi

	sysconfirm=
	echo "========================================================================="
	echo "Installtion path is $install_dir"
	read -p "(Confirm please input: y ,if not please press the enter button):" sysconfirm
	
	case "$sysconfirm" in
	y|Y|Yes|YES|yes|yES|yEs|YeS|yeS)
	mkdir -p $install_dir
	break
		;;
	n|N|No|NO|no|nO)
	continue
	;;
	*)
	continue
	esac
done	

clear
echo "====================="
echo "Install list:"
echo "====================="
echo "mysql"
echo "freetds"
echo "nginx"
echo "php"
echo "php_memcache"
echo "php_eaccelerator"
echo "ttserver"
echo "httpsqs"
echo "sphinx"
echo "====================="

read -n 1 -p "Press any key to start installtion..."

#init system
echo "========================================================================="
echo "Install system component..."
sleep 5
yum update -y
yum install gcc g++ gcc-* gcc-c++* make autoconf automake libtool imake libicu-devel libxml2-devel expat-devel libmcrypt-devel curl-devel libjpeg-devel libpng-devel freetype-devel bzip2-devel zlib-devel -y

sed -i -r  's/^\s*SELINUX(.*)$/SELINUX=disabled/g' /etc/selinux/config 
setenforce 0

wget -N "$autoconf_dl"
echo "$autoconf_dl" |sed 's#^.*/##g'|awk '{printf "tar zxvf %s\n",$1}'|bash >$tmp_dir/code.log||exit 1
if [ ! -s "$tmp_dir/code.log" ]; then
	echo "error $tmp_dir/code.log"
	exit 1
else
	path=`cat $tmp_dir/code.log|awk -F/ '/\//{print $1}'|sort -u`
	if [ ! -d "$tmp_dir/$path" ]; then
		echo "$tmp_dir/$path is not dir"
		exit 1
	fi
	cd $tmp_dir/$path
fi
./configure || exit 1
make || exit 1
make install || exit 1
cd ../

wget -N "$automake_dl"
echo "$automake_dl" |sed 's#^.*/##g'|awk '{printf "tar zxvf %s\n",$1}'|bash >$tmp_dir/code.log||exit 1
if [ ! -s "$tmp_dir/code.log" ]; then
	echo "error $tmp_dir/code.log"
	exit 1
else
	path=`cat $tmp_dir/code.log|awk -F/ '/\//{print $1}'|sort -u`
	if [ ! -d "$tmp_dir/$path" ]; then
		echo "$tmp_dir/$path is not dir"
		exit 1
	fi
	cd $tmp_dir/$path
fi
./configure || exit 1
make || exit 1
make install || exit 1
cd ../

wget -N "$libtool_dl"
echo "$libtool_dl" |sed 's#^.*/##g'|awk '{printf "tar zxvf %s\n",$1}'|bash >$tmp_dir/code.log||exit 1
if [ ! -s "$tmp_dir/code.log" ]; then
	echo "error $tmp_dir/code.log"
	exit 1
else
	path=`cat $tmp_dir/code.log|awk -F/ '/\//{print $1}'|sort -u`
	if [ ! -d "$tmp_dir/$path" ]; then
		echo "$tmp_dir/$path is not dir"
		exit 1
	fi
	cd $tmp_dir/$path
fi
./configure || exit 1
make || exit 1
make install || exit 1
if [ ! `grep '/usr/local/lib' /etc/ld.so.conf` ];then
	echo "/usr/local/lib" >>/etc/ld.so.conf
fi
/sbin/ldconfig
cd ../

#install mysql
echo "========================================================================="
echo "Installing mysql..."
sleep 5
if [ `rpm -qa|grep mysql-server` ];then
	/etc/init.d/mysqld stop
	yum erase mysql-server -y
	rm -rf /var/lib/mysql
fi
yum install mysql mysql-server mysql-devel -y
service mysqld start

#init database
echo "========================================================================="
echo "Initializing database..."
echo "This may take serveral miniutes.Please wait..."
sleep 5
mysqladmin -u root password "$mysql_root_pwd"
mysql -uroot -p$mysql_root_pwd <<EOF
SET NAMES utf8;
create database \`tudu-db\`  character set utf8;
create user 'tudu'@'localhost' identified by 'tudu.com';
grant all on \`tudu-db\`.* to 'tudu'@'localhost';
flush privileges;
use tudu-db;
source $file_dir/install/sql/1.sql;
source $file_dir/install/sql/2.sql;
source $file_dir/install/sql/3.sql;
source $file_dir/install/sql/4.sql;
source $file_dir/install/sql/5.sql;
source $file_dir/install/sql/6.sql;
EOF
echo "Initialize database Finshed."

#install freetds
echo "========================================================================="
echo "Installing freetds..."
sleep 5
wget -N "$freetds_dl"
echo "$freetds_dl" |sed 's#^.*/##g'|awk '{printf "tar zxvf %s\n",$1}'|bash >$tmp_dir/code.log||exit 1
if [ ! -s "$tmp_dir/code.log" ]; then
	echo "error $tmp_dir/code.log"
	exit 1
else
	path=`cat $tmp_dir/code.log|awk -F/ '/\//{print $1}'|sort -u`
	if [ ! -d "$tmp_dir/$path" ]; then
		echo "$tmp_dir/$path is not dir"
		exit 1
	fi
	cd $tmp_dir/$path
fi
./configure --prefix=$install_dir/freetds --with-tdsver=8.0 --enable-msdblib --with-gnu-ld --enable-shared --enable-static || exit 1
make || exit 1
make install || exit 1
cd ../
touch $install_dir/freetds/include/tds.h
touch $install_dir/freetds/lib/libtds.a
if [ ! `grep "$install_dir/freetds/lib" /etc/ld.so.conf` ];then
	echo "$install_dir/freetds/lib" >>/etc/ld.so.conf
fi
/sbin/ldconfig

cat > $install_dir/freetds/etc/freetds.conf  <<EOF
[global]
        client charset = UTF-8
        tds version = 8.0
	
;       dump file = /tmp/freetds.log
;       debug flags = 0xffff

	port = 1433
        text size = 64512

[egServer50]
        host = symachine.domain.com
        port = 5000
        tds version = 5.0

[egServer70]
        host = ntmachine.domain.com
        port = 1433
        tds version = 7.0
EOF

#install nginx
echo "========================================================================="
echo "Install pcre...."
sleep 5
wget -N "$pcre_dl"
echo "$pcre_dl" |sed 's#^.*/##g'|awk '{printf "tar zxvf %s\n",$1}'|bash >$tmp_dir/code.log||exit 1
if [ ! -s "$tmp_dir/code.log" ]; then
	echo "error $tmp_dir/code.log"
	exit 1
else
	path=`cat $tmp_dir/code.log|awk -F/ '/\//{print $1}'|sort -u`
	if [ ! -d "$tmp_dir/$path" ]; then
		echo "$tmp_dir/$path is not dir"
		exit 1
	fi
	cd $tmp_dir/$path
fi
./configure ||exit 1
make ||exit 1 
make install ||exit 1
cd ../

echo "========================================================================="
echo "Install nginx...."
sleep 5
wget -N "$nginx_dl"
echo "$nginx_dl" |sed 's#^.*/##g'|awk '{printf "tar zxvf %s\n",$1}'|bash >$tmp_dir/code.log||exit 1
if [ ! -s "$tmp_dir/code.log" ]; then
	echo "error $tmp_dir/code.log"
	exit 1
else
	path=`cat $tmp_dir/code.log|awk -F/ '/\//{print $1}'|sort -u`
	if [ ! -d "$tmp_dir/$path" ]; then
		echo "$tmp_dir/$path is not dir"
		exit 1
	fi
	cd $tmp_dir/$path
fi
./configure --user=$user_name --group=$user_name --prefix=$install_dir/nginx --with-http_stub_status_module --without-http-cache ||exit 1 
make ||exit 1 
make install ||exit 1
cd ../
if [ ! `grep "$install_dir/nginx/sbin/nginx" /etc/rc.local` ];then
	echo "$install_dir/nginx/sbin/nginx" >>/etc/rc.local 
fi

cp $file_dir/install/conf/nginx.conf $install_dir/nginx/conf/
cp $file_dir/install/conf/nginx-tudu.conf $install_dir/nginx/conf/

sed -i "s#/usr/local/#$install_dir/#g" $install_dir/nginx/conf/nginx.conf
sed -i "s#domain.com#$domain#g" $install_dir/nginx/conf/nginx-tudu.conf
sed -i "s#/usr/local/#$install_dir/#g" $install_dir/nginx/conf/nginx-tudu.conf
sed -i "s#/data#$file_dir#g" $install_dir/nginx/conf/nginx-tudu.conf

#install php
echo "========================================================================="
echo "Install php...."
sleep 5
wget -N "$php_dl"
tar zxvf php-5.3.20.tar.gz||exit 1
cd $tmp_dir/php-5.3.20
if [ "$ver" = "x86_64" ]; then
	LDFLAGS="-L/usr/lib64/mysql" ./configure --prefix=$install_dir/php --with-mysql --with-gd --with-jpeg-dir --with-freetype-dir --with-zlib --with-config-file-path=$install_dir/php/etc --with-png-dir --with-libxml-dir --enable-short-tags --enable-mbstring --disable-debug --with-mssql=$install_dir/freetds --with-mcrypt --with-mhash --enable-sockets --with-curl --with-curlwrappers --enable-mbregex --enable-force-cgi-redirect --enable-xml --disable-rpath --enable-discard-path --enable-safe-mode --enable-bcmath --enable-shmop --enable-sysvsem --enable-soap --enable-pdo --with-pdo-mysql --with-pdo-dblib=$install_dir/freetds --enable-fpm 
else
	./configure --prefix=$install_dir/php --with-mysql --with-gd --with-jpeg-dir --with-freetype-dir --with-zlib --with-config-file-path=$install_dir/php/etc --with-png-dir --with-libxml-dir --enable-short-tags --enable-mbstring --disable-debug --with-mssql=$install_dir/freetds --with-mcrypt --with-mhash --enable-sockets --with-curl --with-curlwrappers --enable-mbregex --enable-force-cgi-redirect --enable-xml --disable-rpath --enable-discard-path --enable-safe-mode --enable-bcmath --enable-shmop --enable-sysvsem --enable-soap --enable-pdo --with-pdo-mysql --with-pdo-dblib=$install_dir/freetds --enable-fpm
fi
make || exit 1
make install ||exit 1
/bin/cp sapi/fpm/init.d.php-fpm /etc/rc.d/init.d/php-fpm
chmod 755 /etc/rc.d/init.d/php-fpm
chkconfig --add php-fpm
chkconfig --level 345 php-fpm on
cd ../
mkdir -p $install_dir/php/logs/

cp $file_dir/install/conf/php-fpm.conf $install_dir/php/etc/
cp $file_dir/install/conf/php.ini $install_dir/php/etc/

sed -i "s#/usr/local/#$install_dir/#g" $install_dir/php/etc/php-fpm.conf
sed -i "s#/usr/local/#$install_dir/#g" $install_dir/php/etc/php.ini

wget -N "$php_memcache_dl"
echo "$php_memcache_dl" |sed 's#^.*/##g'|awk '{printf "tar zxvf %s\n",$1}'|bash >$tmp_dir/code.log||exit 1
if [ ! -s "$tmp_dir/code.log" ]; then
	echo "error $tmp_dir/code.log"
	exit 1
else
	path=`cat $tmp_dir/code.log|awk -F/ '/\//{print $1}'|sort -u`
	if [ ! -d "$tmp_dir/$path" ]; then
		echo "$tmp_dir/$path is not dir"
		exit 1
	fi
		cd $tmp_dir/$path
fi
$install_dir/php/bin/phpize
chmod 755 configure 
./configure --with-php-config=$install_dir/php/bin/php-config || exit 1
make  || exit 1
make install  || exit 1
cd ../

wget -N "$php_eaccelerator_dl" --no-check-certificate
tar zxvf master >$tmp_dir/code.log||exit 1
if [ ! -s "$tmp_dir/code.log" ]; then
	echo "error $tmp_dir/code.log"
	exit 1
else
	path=`cat $tmp_dir/code.log|awk -F/ '/\//{print $1}'|sort -u`
	if [ ! -d "$tmp_dir/$path" ]; then
		echo "$tmp_dir/$path is not dir"
		exit 1
	fi
	cd $tmp_dir/$path
fi
$install_dir/php/bin/phpize
chmod 755 configure 
./configure --enable-eaccelerator=shared --with-php-config=$install_dir/php/bin/php-config || exit 1
make || exit 1
make install || exit 1
cd ../
mkdir -p $install_dir/eaccelerator_cache


if [ ! `grep '/etc/init.d/php-fpm start' /etc/rc.local` ];then
echo "/etc/init.d/php-fpm start" >>/etc/rc.local 
fi

/etc/init.d/php-fpm start

#install ttserver
echo "========================================================================="
echo "Install Ttserver...."
sleep 5
wget -N "$libevent_dl" --no-check-certificate
echo "$libevent_dl" |sed 's#^.*/##g'|awk '{printf "tar zxvf %s\n",$1}'|bash >$tmp_dir/code.log||exit 1
if [ ! -s "$tmp_dir/code.log" ]; then
	echo "error $tmp_dir/code.log"
	exit 1
else
	path=`cat $tmp_dir/code.log|awk -F/ '/\//{print $1}'|sort -u`
	if [ ! -d "$tmp_dir/$path" ]; then
		echo "$tmp_dir/$path is not dir"
		exit 1
	fi
	cd $tmp_dir/$path
fi
./configure --prefix=$install_dir/libevent ||exit 1
make ||exit 1 
make install ||exit 1 
cd ..
if [ ! `grep "$install_dir/libevent/lib" /etc/ld.so.conf` ];then
	echo "$install_dir/libevent/lib" >>/etc/ld.so.conf
fi
/sbin/ldconfig

wget -N "$tokyocabinet_dl"
echo "$tokyocabinet_dl" |sed 's#^.*/##g'|awk '{printf "tar zxvf %s\n",$1}'|bash >$tmp_dir/code.log||exit 1
if [ ! -s "$tmp_dir/code.log" ]; then
	echo "error $tmp_dir/code.log"
	exit 1
else
	path=`cat $tmp_dir/code.log|awk -F/ '/\//{print $1}'|sort -u`
	if [ ! -d "$tmp_dir/$path" ]; then
		echo "$tmp_dir/$path is not dir"
		exit 1
	fi
	cd $tmp_dir/$path
fi

if [ "$ver" = "x86_64" ]; then
	./configure --prefix=$install_dir/tokyocabinet || exit 1
else
	./configure --enable-off64 --prefix=$install_dir/tokyocabinet || exit 1
fi
make || exit 1
make install || exit 1
cd ../
if [ ! `grep "$install_dir/tokyocabinet/lib" /etc/ld.so.conf` ];then
	echo "$install_dir/tokyocabinet/lib" >>/etc/ld.so.conf
fi
/sbin/ldconfig
		
wget -N "$tokyotyrant_dl"
echo "$tokyotyrant_dl" |sed 's#^.*/##g'|awk '{printf "tar zxvf %s\n",$1}'|bash >$tmp_dir/code.log||exit 1
if [ ! -s "$tmp_dir/code.log" ]; then
	echo "error $tmp_dir/code.log"
	exit 1
else
	path=`cat $tmp_dir/code.log|awk -F/ '/\//{print $1}'|sort -u`
	if [ ! -d "$tmp_dir/$path" ]; then
		echo "$tmp_dir/$path is not dir"
		exit 1
	fi
	cd $tmp_dir/$path
fi
./configure --with-tc=$install_dir/tokyocabinet/ || exit 1
make || exit 1
make install || exit 1
cd ../
mkdir -p /ttserver

if [ ! `grep 'rm -rf /ttserver/ttserver.pid' /etc/rc.local` ];then
	echo "rm -rf /ttserver/ttserver.pid" >>/etc/rc.local
	echo "/usr/local/bin/ttserver -host 0.0.0.0 -port 11211  -thnum 4 -dmn -pid /ttserver/ttserver.pid -log /ttserver/ttserver.log -le -ulog /ttserver/ -ulim 128m -sid 1 -rts /ttserver/ttserver.rts /ttserver/database.tcb" >>/etc/rc.local
fi

#install httpsqs
echo "========================================================================="
echo "Install Httpsqs...."
sleep 5
wget -N "$httpsqs_dl"
echo "$httpsqs_dl" |sed 's#^.*/##g'|awk '{printf "tar zxvf %s\n",$1}'|bash >$tmp_dir/code.log||exit 1
if [ ! -s "$tmp_dir/code.log" ]; then
	echo "error $tmp_dir/code.log"
	exit 1
else
	path=`cat $tmp_dir/code.log|awk -F/ '/\//{print $1}'|sort -u`
	if [ ! -d "$tmp_dir/$path" ]; then
		echo "$tmp_dir/$path is not dir"
		exit 1
	fi
	cd $tmp_dir/$path
fi
sed -i "s#/usr/local/libevent-2.0.12-stable#$install_dir/libevent#g" Makefile
sed -i "s#/usr/local/tokyocabinet-1.4.47#$install_dir/tokyocabinet#g" Makefile
make||exit 1
make install || exit 1
cd ../
if [ ! `grep 'httpsqs -p 12181 -x /tmp/httpsqs -d' /etc/rc.local` ]; then
	echo "httpsqs -p 12181 -x /tmp/httpsqs -d" >>/etc/rc.local 
fi 

#install sphinx
echo "========================================================================="
echo "Install sphinx...."
sleep 5
wget -N "$sphinx_dl"
echo "$sphinx_dl" |sed 's#^.*/##g'|awk '{printf "tar zxvf %s\n",$1}'|bash >$tmp_dir/code.log||exit 1
if [ ! -s "$tmp_dir/code.log" ]; then
	echo "error $tmp_dir/code.log"
	exit 1
else
	path=`cat $tmp_dir/code.log|awk -F/ '/\//{print $1}'|sort -u`
	if [ ! -d "$tmp_dir/$path" ]; then
		echo "$tmp_dir/$path is not dir"
		exit 1
	fi
	cd $tmp_dir/$path
fi
cd mmseg-3.2.14
./bootstrap
./configure --prefix=$install_dir/mmseg3
make && make install ||exit 1
cd ..
cd csft-3.2.14
sh buildconf.sh
./configure --prefix=$install_dir/coreseek  --without-unixodbc --with-mmseg --with-mmseg-includes=$install_dir/mmseg3/include/mmseg/ --with-mmseg-libs=$install_dir/mmseg3/lib/ --with-mysql
make && make install ||exit 1
cd ..
cd $install_dir/mmseg3/etc/
wget http://www.wapm.cn/uploads/csft/3.2/dict/default/thesaurus.lib
cd -
mkdir -p $install_dir/coreseek/index
mkdir -p $install_dir/coreseek/shell

cp $file_dir/install/sphinx/tudu.conf $install_dir/coreseek/etc/

sed -i "s#/usr/local/#$install_dir/#g" $install_dir/coreseek/etc/tudu.conf

cat > $install_dir/coreseek/shell/index_rebuild.sh <<EOF
#!/bin/bash
 
source ~/.bash_profile
 
while [ 1 ]
do
	[ `ps -ef | grep indexer | grep -v 'grep' -c` -ne 0 ]  && {
		echo 'Some other indexer is running....'
		sleep 5
		continue
	}
        echo `date`
        $install_dir/coreseek/bin/indexer -c $install_dir/coreseek/etc/tudu.conf --all --rotate
	[ $? -eq 0 ] && exit
done
EOF
chmod u+x $install_dir/coreseek/shell/index_rebuild.sh

cat > $install_dir/coreseek/shell/index_inc.sh  <<EOF
#!/bin/bash
 
source ~/.bash_profile
 
while [ 1 ]
do
	[ `ps -ef | grep indexer | grep -v 'grep' -c` -ne 0 ]  && {
		echo 'Some other indexer is running....'
		sleep 5
		continue
	}
        echo `date`	
	$install_dir/coreseek/bin/indexer -c $install_dir/coreseek/etc/tudu.conf chat_inc --rotate
	[ $? -eq 0 ] && exit
done
EOF
chmod u+x $install_dir/coreseek/shell/index_inc.sh

if [ ! `grep "# Tudu crontab #" /var/spool/cron/root` ];then
cat >> /var/spool/cron/root  <<EOF
# Tudu crontab #
01 04 * * * $install_dir/coreseek/shell/index_rebuild.sh >> $install_dir/coreseek/var/log/index_rebuild.log 2>&1
*/5 * * * * $install_dir/coreseek/shell/index_inc.sh >> $install_dir/coreseek/var/log/index_inc.log 2>&1

*/1 * * * * $install_dir/php/bin/php $file_dir/scripts/task/run/run.php tudusqs --mode=1 --interval=3 --limit=60
*/1 * * * * $install_dir/php/bin/php $file_dir/scripts/task/run/run.php adminsqs --mode=1 --interval=3 --limit=60
*/1 * * * * $install_dir/php/bin/php $file_dir/scripts/task/run/run.php notify --mode=1 --interval=3 --limit=60
01 00 * * * $install_dir/php/bin/php $file_dir/scripts/task/run/run.php attend
15 00 * * * $install_dir/php/bin/php $file_dir/scripts/task/run/run.php discuz
30 00 * * * $install_dir/php/bin/php $file_dir/scripts/task/run/run.php notice
45 00 * * * $install_dir/php/bin/php $file_dir/scripts/task/run/run.php tudu
*/5 * * * * $install_dir/php/bin/php $file_dir/scripts/task/run/run.php meeting
EOF
fi
$install_dir/coreseek/shell/index_rebuild.sh 
$install_dir/coreseek/bin/searchd -c  $install_dir/coreseek/etc/tudu.conf &

cat > $file_dir/install/config.ini<<EOF
[tudu]
domain = $domain

[mysql]
host = 127.0.0.1
port = 3306
user = tudu
password = tudu.com
database = tudu-db

[httpsqs]
host = 127.0.0.1
port = 12181

[memcache]
host = 127.0.0.1
port = 11211

[sphinx]
host = 127.0.0.1
port = 9312
EOF

chown -R tuduweb.tuduweb $file_dir
chkconfig mysqld on
$install_dir/nginx/sbin/nginx
sleep 1
/usr/local/bin/ttserver -host 0.0.0.0 -port 11211  -thnum 4 -dmn -pid /ttserver/ttserver.pid -log /ttserver/ttserver.log -le -ulog /ttserver/ -ulim 128m -sid 1 -rts /ttserver/ttserver.rts /ttserver/database.tcb
sleep 1
/usr/bin/httpsqs -p 12181 -x /tmp/httpsqs -d

clear
echo "========================================================================="
echo "Install Completed!"
echo "========================================================================="
echo "The tudu web file located in $file_dir/"
echo "========================================================================="
echo "You can access http://$domain/ to continue Web installtion!"
echo "========================================================================="
