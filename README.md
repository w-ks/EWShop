# EWShop
## 项目目录

``` |-- EWShop目录                          #项目根目录
	|-- index.php                         #主入口路由文件
	|-- config.inc.php                      #项目的主配置文件
	|-- .htaccess                           #项目根下的Apache配置文件
	|-- vendor                             	#Composer组件程序目录
	|-- app                             	#项目的应用程序目录
           |--controllers                    #MVC模式控制器目录
                  |--admin                 #后台控制目录
                  |--home                  #前台控制器目录
                  |--BaseControllers.php     #控制器的基类
           |--models                        #MVC模式Model目录
                  |--BaseDao.php            #数据库操作对象的基类
           |--views                         #MVC模式的视图目录
                  |--admin                 #后台模板文件目录
                     |--index              #后台子模块
                  |--home                  #前台第一套模板目录 
                  |--home2                  #前台第一套模板目录
                  |--public                  #前后台公共资源（CSS,JS,images）目录 
           |--helpers.php                    #项目的自定义函数库文件
	|-- class                             	#项目自己定义类库文件
	|-- uploads                             #资源上传保存的目录
```

## 后台模块

1.管理员登录

> 登录admin/login进行管理员登录



遗留问题

	开发一套新的项目模板
	商品加上多图片
	图片用的时候在缩放
		根据位置的尺寸获取图片，压缩成位置的尺寸，再从缓存中把图片展示到页面，就不用上传的时候给缩放成几十张图片了，而是用的时候就用程序来控制缩放。取得时候先压缩
	为系统加上缓存服务
		比如分类、友情链接，可以用组件的方式，静态化文件的方式，redis数据库的方式，memorycache方式。优化服务器缓存是法宝
		先从缓存里获取，获取到了从缓存里展示到页面，如果获取不到从数据库获取放到缓存里边，再展示。
		缓存可更新每小时或每两小时，也可以做后端按钮及时更新缓存
	加微信登录
	支付宝和微信支付
	加入日志功能
	购物车添加两次才显示添加成功，ajax？result
	用户和管理员不能同时登陆
	缓存
	laravel框架

小组件

​	分页

​	验证码

​	文件上传

​	保持会话

​	前台、后台





## 项目使用

1.创建新数据库，将`ewshop.sql` 文件导入新数据库

2.`config.inc.php` 

```php
define("TEMPNAME","home"); // home home2   切换模版

define("HOST","localhost");  // mysql主机
define("DBNAME","ewshop");   // 数据库名
define("USER","root");       // 用户
define("PASSWD","123456");   // 密码
define("TABPREFIX","ew_");   // 表前缀

define("PAGESNUM",6);        // 每页展示6个商品
```

3.配置虚拟主机 `.\httpd\conf\extra\httpd-vhosts.conf`

```
# 结合host文件，浏览器访问www.ewshop.com默认到Apache24\htdocs\myphp
<VirtualHost *:80>
	DocumentRoot "C:\Env\php\Apache\httpd-2.4.52-o111m-x64-vc15\Apache24\htdocs\myphp" 
	ServerName www.ewshop.com
</VirtualHost>  

<Directory "C:\Env\php\Apache\httpd-2.4.52-o111m-x64-vc15\Apache24\htdocs\myphp">
	Options indexes
	AllowOverride All
	Require local
</Directory>
```

4.配置host文件`C:\WINDOWS\System32\drivers\etc\hosts`

```
127.0.0.1       www.ewshop.com
```

5.重启`Apache`

```
# 管理员身份
./httpd -k restart
```

