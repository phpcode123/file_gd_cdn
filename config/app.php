<?php
return [
    // +------------------------------------------------------------------
    // | 网站设置
    // +------------------------------------------------------------------

    //CDN程序可以无限拓展，每台服务器注意cdn_server_url的值
    'app_name'               => 'File Uploader CDN SERVER',
    'api_token'              => '28sdasdasdasdasdasdasd3',
    'install_path'           => '/www/wwwroot/new1024GB/file_gd_cdn/',  //app()->getRootPath()可使用助手函数 root_path()

    //主服务器地址
    'main_server_url'        => 'https://mian.com', //主服务器域名，后面不要带斜杠
    'cdn_server_url'         => 'https://f101.cdn_server.com', //当前cdn域名，后面不要带斜杠,

    //服务器硬盘必须留置容量
    'cdn_server_disk_min_avail'  => '2100',//当小于此值时禁止上传文件，单位M(兆),即必须大于2G才行

 
    //允许跨域上传的域名，多个域名,用逗号隔开,必须要带http或https前缀,后面不要带斜杠
    'allow_origin_url'        => [
        'https://mian.com'        //自己的主域名
    ],


    //redis 缓存设置
    'redis_host'             => '127.0.0.1',
    'redis_port'             =>  6379,
    'redis_prefix'           => 'file_gd_cdn_100_',   //每台服务器的redis前缀值都必须不一样，字符串可自定义，只要不重复即可。



    //file_key short_str长度 file为8  archive为7
    'short_str_length'   => 8,
  
    //常见蜘蛛User-agent, 注意不要以｜结尾，否则会匹配到所有的数据,｜为或运算符
    'spider_user_agent'      =>  'baiduspider|sogou|google|360spider|YisouSpider|Bytespider|bing|yodao|bot|robot|facebook|meta|twitter|reddit|WhatsApp|tiktok|Dalvik|telegram|crawler|ZaloPC|Zalo|discord|Aloha|CFNetwork|redditbot|HttpClient|tw\.ystudio\.BePTT|CFNetwork|com\.joshua\.jptt|okhttp/4',
    
    //移动端user_agent 用于程序逻辑判断是否是移动端
    'mobile_user_agent'      =>  'iPhone|Android|ios|mobile',


















    // -------------------以下是系统默认配置，如果不知道配置项请勿轻易修改-------------------
    // 默认跳转页面对应的模板文件【新增】
    'dispatch_success_tmpl' => app()->getRootPath() . '/public/tpl/dispatch_jump.tpl',
    'dispatch_error_tmpl'  => app()->getRootPath() . '/public/tpl/dispatch_jump.tpl',


    'http_exception_template'    =>  [
        // 定义404错误的模板文件地址
        404 =>  app()->getRootPath() . '/public/404.html',
        // 还可以定义其它的HTTP status
        401 =>  app()->getRootPath() . '/public/404.html',
    ],


    // ------------------------------------------------------------------
    // 应用地址
    'app_host'         => env('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',
    // 应用映射（自动多应用模式有效）
    'app_map'          => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => ["middleware","command"],
    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'    => 'Page error! Please try again later.',
    // 显示错误信息
    'show_error_msg'   => false,


];
