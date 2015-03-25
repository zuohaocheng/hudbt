# 本文是讲述新用户权限验证体系的 #
目前这个体系与旧的get\_user\_class()的线性体系，与ZuoHaoCheng写的涉及Cake的checkPrivilege并存。新权限验证体系的目标是剿除线性体系，与checkPrivilege并存。假如有朝一日本作者学会了烹制蛋糕，则把checkPrivilege也一并端了。
在取代旧势力的同时，新势力也需要不断变化以适应环境，所以这个新的权限验证体系会不断更新变化，请持续关注此页。
## 权限表 ##
所有的权限都存在functions.php的一个全局变量 $permissionConfig中。以下是2012-09-07 日时的范例：
```
$permissionConfig = [
"keeper" => [
	"boss" =>["setstoring","edittorrent"],
	"member" => "storing"
	],
"helper" =>[],
"former" =>[
	UC_MODERATOR => ["edittorrent"],
	UC_ADMINISTRATOR => ["setstoring"]
	]
];
```
keeper、helper等是新用户组的名称，而其中的"boss"等则是用户组的角色。存储权限的数组实际上是
```
$permissionConfig[$usergroupname][$role]
```
里面列举着不同组别+角色的所有权限。

而former则是继承自线性权限系统。里面按照每个旧用户等级存放 **在该等级新获得的权限** e.g. 总版可以拥有编辑种子的权力，而之前一级UP则没有。管理员继承了总版的编辑种子权限，而也有设置保种的权限。
这个fromer的设计是用于override低等级的权限。目前的override还是按照旧权限等级的规则来，在将来可能会逐步缩减override的范围。
## 数据库 ##
```
CREATE TABLE users_usergroups (
       `user_id` INT(10) UNSIGNED NOT NULL,
       `usergroup_id` MEDIUMINT(8) UNSIGNED NOT NULL,
       `role` VARCHAR(63) NOT NULL DEFAULT 'member',
       `added_by` INT(10) UNSIGNED NOT NULL,
       `added_date` DATETIME NOT NULL,
       `removed_by` INT(10) UNSIGNED,
       `removed_date` DATETIME,
       PRIMARY KEY (`user_id`,`usergroup_id`,`role`),       
       KEY `k_user_usergroup` (`user_id`, `usergroup_id`),
       KEY `k_usergroup_user` (`usergroup_id`, `user_id`),
       KEY `k_removedby` (`removed_by`)
) ENGINE=MyISAM;
CREATE TABLE usergroups (
       `group_id` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
       `group_name` VARCHAR(255) NOT NULL,
       PRIMARY KEY (`group_id`),
       CONSTRAINT `u_group_name` UNIQUE (`group_name`)
) ENGINE=MyISAM;
INSERT INTO usergroups VALUES(1,'keeper');
```
removed\_by和removed\_date设计的目的是实现逻辑删除而不是物理删除。所以在验证是否是某用户组的时候还必须验证 removed\_by==NULL
## 用户的新用户组信息存放 ##
存放在$CURUSER['usergroups']中，而在userdetails则存在$user['usergroups']里。
此信息由$get\_user\_group($userid)抓取，信息的结构是
```
{
'groupnameA' =>['role'=>"role_in_groupA",removed =>['removed_by' =>"removed_bysb",'removed_date' => "someday"]],
'groupnameB' => ['role' => "role_in_groupB"]
}
```
如果此人已经被移出某用户组，则其用户组信息则会有removed\_by,removed\_date。如果仍在某用户组则没有这两个信息。所以判断一个人是否在某用户组还需要检查有否removed。
## 函数 ##
### checkPermission($needle,$user) ###
位于functions.php中，$permissionConfig里面查找$needle($permissionConfig就是haystack)权限。$user的实参是$CURUSER。先遍历$user['usergroups']，访问其中的role，然后查在$permissionConfig中是否有对应的权限$needle，如有返回true；如无，则遍历$permissionConfig['former']查找$needle权限，如有则返回键值（键值是旧权限等级的数值），然后比较$user['class']（用户的旧权限等级），如果用户可以override，则返回true。如没在former里找到$needle则返回false。
找former可以避免如下写法
```
if(get_user_class()>=UC_MOD||checkPrivelege([Torrent,setstoring]))
```
checkPermission实现了和旧体系的兼容与override