# 准备 #
  1. 下载PHP源码包 http://www.php.net/downloads.php
  1. 下载memcache源码包 http://pecl.php.net/package/memcache
  1. 确定php的路径， **后续工作均在该路径下完成**
```
ps ax | grep php-cgi
```
  1. 确定旧的configure参数
```
php -i | grep configure
```
  1. 备份旧的PHP目录

# 编译与安装 #
  1. 使用前述configure参数编译PHP，可能需要设置路径
```
export LDFLAGS='-L/usr/local/webserver/mysql/lib' #设定MySQL lib路径
export LIBS='-liconv' #目测是Auto-tools BUG了
```
  1. make install, 但 **不要** 结束旧的php-cgi进程
  1. 编译memcache
```
phpize
./configure --with-php-config=php-config
make
make install
```
  1. 重启php-cgi