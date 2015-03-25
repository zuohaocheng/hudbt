# 支持库 #
```
include/bittorent.php 总入口
  include/core.php 定义基本常数
  include/functions.php 主要Web支持函数
    include/globalfunctions.php Web/Tracker通用支持函数
    include/config.php 载入配置
```

# 准备工作 #
```
dbconn(); #连接数据库
require_once(get_langfile_path("edit.php", false, 'chs')); #载入语言文件
loggedinorreturn(); #赶走没登陆的
```

# 页面渲染 #
```
stdhead(); #页面头
# 主要内容
stdfoot(); #页面尾
```
这两个函数运用的`Smarty`排版引擎

呈现出的HTML结构
```
<html>
<head>
<script type="text/javascript" src="load.php?format=js&name=blah.php"></script>
<link rel="stylesheet" href="load.php?format=css&name=blah.php" type="text/css" media="screen" />
<!-- 载入minify后的Javascript和CSS -->
</head>
<body>
<div id="outer">
<!-- stdhead结束，开始页面内容 -->
blablablah
<!-- stdfoot开始 -->
</div>
</body>
</html>
```