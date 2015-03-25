Tracker部分主要有两个入口
  * `scrape.php` 负责提供总的下载、上传、做种人数
  * `announce.php` 负责提供具体的Peers的IP等信息，并记录流量

# 支持库 #
```
include/bittorent_announce.php 总入口
  include/core.php 定义基本常数
  include/functions_announce.php Tracker支持函数
    include/globalfunctions.php Web/Tracker通用支持函数
    include/config.php 载入配置
```

# 准备工作 #
```
dbconn_announce(); #连接数据库
```

# 步骤 #
  1. 赶走浏览器
  1. 赶走黑IP和端口
  1. 获取Peers
  1. 赶走黑客户端
  1. 赶走黑种子
  1. 算流量
  1. 改数据库