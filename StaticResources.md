# 调试与部署 #
## 调试 ##
在url后加入`&debug=1`即可避免缓存和Minify，如`index.php?debug=1`。

## 部署 ##
  * 全局资源，执行`php sload.php`；
  * 单个页面资源，打开对应页面，加入`purge=1`参数，例如更新了`index.js`需使之生效，则打开`index.php?purge=1即可`。

# `sload.php` #

负责全局静态资源的静态缓存，将`include/static_resources.php`中定义的js、css资源minfy、连接后缓存到`/cache`中。若全局静态资源发生变动，执行`php sload.php`进行刷新。


# `load.php` #

负责动态缓存，为`memcache`缓存。其参数包括
## name ##
  * 不指定。载入全局资源，输出同`sload.php`的缓存。
  * 对应php页面的名称，如`index.php`。如此会载入该页面所有依赖的资源。
  * 指定js/css文件的名称，如`common.js`, `common.css`。但此时文件必须在默认目录下，即`/js`与`/styles`。如此只载入单个资源。
## format ##
若`name`参数指定的是php页面名称，则确定载入资源的类型，否则无用。
## debug ##
为真则不缓存，不minify，直接拼接后输出。
## purge ##
强制刷新缓存。