

## 简介

   1、FILE_GD_CDN是FILE_GD的上传文件接管服务，CDN服务器支持无限拓展。
   2、软件设计开发时就较为着重于性能，代码精简，希望大家能习惯~~
   3、软件系免费分享，项目也只适用于国外英文项目，请勿在国内瞎搞，不然自负法律责任！ 


## 技术相关

* 环境：linux + nginx + mysql + php + redis
* 后端：ThinkPHP6.0
* 前端：无需前端 

## 安装使用

* 本程序仅支持LNMP环境，其它环境未测试，建议安装使用linux宝塔。（MYSQL5.7 + PHP7.4 + REDIS）
* 安装：将网站目录上传到web，然后在宝塔面板中绑定好cdn域名，域名建议使用从100开始的后缀，如：f100或cdn100，数字递增，第二台服务器就是f101或cdn101，依次类推。
* 然后修改/file_gd_cdn/config/app.php配置文件
~~~

...
    'install_path'           => '/www/wwwroot/file_gd_cdn/',  //修改为自己当前目录
    'main_server_url'        => 'https://mian.com', //主服务器域名，后面不要带斜杠
    'cdn_server_url'         => 'https://f101.cdn_server.com', //当前cdn域名，后面不要带斜杠,

    //允许跨域上传的域名，多个域名,用逗号隔开,必须要带http或https前缀,后面不要带斜杠
    'allow_origin_url'        => [
        'https://mian.com'        //自己的主域名
    ],

...
~~~




* MYSQL:建立空数据库，恢复根目录下的/file_gd_cdn/file_gd_cdn_20230924.sql文件，然后配置数据库文件
       数据库配置文件：/file_gd/config/database.php, 并修改下列行(字母大写部分)
~~~

...
'database'        => env('database.database', 'YOUR_DATABASE'),
'username'        => env('database.username', 'YOUR_MYSQL_USERNAME'),
'password'        => env('database.password', 'YOUR_MYSQL_PASSWORD'),
...
~~~

* 伪静态文件目录(只做了Nginx适配)：/file_gd_cdn/public/.htaccess  内容复制宝塔配置里即可
* 后台地址：无后台，使用/public/adminer.php用来简易查看下数据库即可

* 再在file_gd(自己的主站)后台 服务器 》添加服务器 ，将当前cdn的域名添加到后台中





## 
[FILE_GD主站程序](https://github.com/PHPCODE123/file_gd "FILE_GD")