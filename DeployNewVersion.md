# 如果修改了css或js #
  1. 修改`load.php`的revision
```
include/functions.php

$addition .= '&rev=当前日期';
```
  1. 清理`memcached`
```
$ telnet localhost 11211
> flush_all
```

# 如果修改了静态资源 #
  1. 重启nginx
```
nginx -s reload
```