# 自行搭建服务器 #
## 代码签出 ##
本项目使用git管理，[签出方式](https://code.google.com/p/hudbt/source/checkout)。配置文件未包含在代码仓库中，请于[此处下载](https://code.google.com/p/hudbt/downloads/detail?name=configs.tar.bz2)。

若想签出源码请先[下载git](http://git-scm.com)，或直接下载[git for Windows](http://code.google.com/p/msysgit/downloads/)

一般的Linux和Mac OS X Lion自带，另外，由于某些你懂的原因，可能需要更改hosts/挂代理，以及取消git的SSL验证
```
git config --global http.sslVerify false
```

## 软件准备 ##
### HTTP服务器：NginX ###
http://nginx.org/en/download.html 解包即可，请勿使用中文目录（下同）

### PHP ###
必须5.4.0以上版本

编译安装的最小配置（含PEAR和php-fpm），并需要如下库支持（Linux）：
```
yum install gcc make automake autoconf libtool
yum install libxml2-devel openssl-devel
yum install libjpeg-devel libpng-devel freetype-devel libcurl-devel mysql-devel libedit-devel zlib-devel gd-devel

./configure '--with-pdo-mysql=mysqlnd' '--enable-mbstring' '--with-zlib' '--with-gd' '--enable-fpm' '--with-pear' '--with-openssl' '--enable-sockets'
```

Windows版下载地址 http://windows.php.net/download/

可以考虑将PHP目录加入PATH，以方便使用

#### PEAR ####
若PHP非编译安装，则于http://pear.php.net/go-pear.phar 下载后，在命令行中输入
```
php go-pear.phar
```
可能需要管理员权限，并选择php路径

#### 缓存 ####
为效率起见，生产服务器上必须安装缓存插件，如 [memcache](http://pecl.php.net/package/memcache)等。另外，也必须安装PHP opcode cache，推荐使用PHP 5.5自带的`opcache`。

在调试环境下，可以使用文件缓存代替`memcache`，从而省去安装插件的麻烦；也可以使用别的Cache，见 http://book.cakephp.org/2.0/en/core-libraries/caching.html 。使用文件缓存的方法为修改`cake/app/Config/bootstrap.php`, 将`Cache::config(...`项替换为
```
Cache::config('default', array('engine' => 'File'));
```


### 数据库服务器：MySQL ###
下载：http://dev.mysql.com/downloads/mysql/

（Win）安装时注意选择编码为UTF-8, 同时可以将MySQL路径加入PATH，以方便使用

（Linux、Mac）设置mysql.sock的路径：根据mysql配置文件my.cnf的内容指定php.ini中`[mysql]`段的pdo\_mysql.default\_socket，例如
```
pdo_mysql.default_socket=/var/mysql/mysql.sock
```

若MySQL地址、用户名等与下文设置的不同，需要更改Cake/app/config/database.php的内容。

### 配置文件、临时目录等 ###
```
mkdir cake/app/tmp templates_c cache log cache/users cache/douban imdb/cache imdb/images
chmod -R 777 cake/app/tmp templates_c log cache/ imdb/cache imdb/images
cp cake/app/Config/database.php.default  cake/app/Config/database.php
cp cake/app/Config/core.php.default  cake/app/Config/core.php
cp config/allconfig.php.default config/allconfig.php
```

日志会保存在`cake/app/tmp/logs`内，以帮助排查问题；若无日志，请检查权限和`cake/app/Config/core.php`内的`log`级别。

若login.php直接跳转至https，而https未配置，请直接将`allconfig.php`中的'securelogin'改为'no'

## 配置准备 ##
  * 启动数据库：(Unix)
```
sudo -b mysqld_safe
```
或(Windows)
```
mysqld
```

  * 建立数据库：首先下载基本的数据库包`hudbtYYYYMMDD.sql.bz2`，解压后，打开mysql命令行
```
mysql -uroot
```
输入
```
CREATE DATABASE hudbt; 
CREATE USER hudbt@localhost IDENTIFIED BY '123456';
GRANT SELECT, INSERT, UPDATE, DELETE ON hudbt.* TO hudbt@localhost;
USE hudbt;
SOURCE hudbtYYYYMMDD.sql;
```
  * 在config/allconfig.php中将数据库地址、用户名密码做相应修改
  * 配置nginx.conf
注意修改路径
```
    server {
        listen       80;
        #listen 443 ssl; #若启用https，则需要加上本行，并作相应配置
        root /var/webroot/hudbt; #要改
	
location / {
		 index index.html index.htm index.php;
	}

	location /cake {
		 try_files $uri $uri/ /cake/app/webroot/index.php?$uri&$args;
	}

        location ~ \.php$ {
	    fastcgi_pass 127.0.0.1:9000;
	    fastcgi_index index.php;            
	    fastcgi_param SCRIPT_FILENAME  $document_root$fastcgi_script_name;
            fastcgi_param HTTPS                 $https;
	    include fastcgi_params;       
        }

	location /ws/ {
	    proxy_pass http://127.0.0.1:12345;
	    proxy_http_version 1.1;
	    proxy_set_header Upgrade $http_upgrade;
	    proxy_set_header Connection "Upgrade";
	}
     }
```
  * 启动NginX
  * （Windows）配置`php.ini`
解除以下行的注释
```
date.timezone = 'Asia/Shanghai'
extension_dir = "ext"
extension=php_pdo_mysql.dll
extension=php_mbstring.dll
```
  * 启动`php-fpm`（首选）或`php-cgi`(Windows)
```
php-fpm

php-cgi -q -b 127.0.0.1:9000 
```
（Windows）可能需要在控制面板/Windows防火墙中将php-cgi加入防火墙例外
  * 启动memcahced
```
memcahced -d -u root
```

## 其它初始化 ##
### 生成静态资源 ###
如果不执行，CSS就会去旅游
```
php sload.php
```

### loadRevision ###
新建 include/loadrevision.php 内容为
```
<?php
$loadRevision = "";
```
### 注册用户并设为管理员 ###
在mysql中执行
```
UPDATE users SET status = 'confirmed', class = 16;
```
同时可以免去邮件验证。

### WebSocket ###
PM推送用到了WebSocket，因此需要启动WebSocket服务器
```
php -q ws/server.php
```

## 错误处理 ##
  * 若出现
```
The page you are looking for is temporarily unavailable.
Please try again later.
```
重启 `php-cgi` 即可