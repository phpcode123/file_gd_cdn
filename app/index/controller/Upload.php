<?php
namespace app\index\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cache;




class Upload extends BaseController 
{


    public function index()
    { 
        
       //only post
       if(Request::isPost()){  
            //允许跨域访问
            header("Access-Control-Allow-Credentials: true");
            //header("Access-Control-Allow-Origin: *");  //允许所有域名访问
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';  
            $allow_origin = Config::get("app.allow_origin_url");
            //设置跨域
            if(in_array($origin, $allow_origin)){  
                header('Access-Control-Allow-Origin:'.$origin);       
            }


            header("Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With, X-Token");
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');

            // 处理 OPTIONS 请求
            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                exit;
            }

            //-------------------  判断当前服务器硬盘容量 begin -------------------------------
            exec("df -h /www/wwwroot -m |awk '{print $4}'",$output);//输出单位为M(兆)
            $disk_avail = $output[1];
            if($disk_avail < Config::get("app.cdn_server_disk_avail")){
                $json_data = [
                    "stutas" => 300,
                    "message" => "Disk full.",
                    "files" => []
                ];
    
                echo  json_encode($json_data);
                
            }
            //-------------------  判断当前服务器硬盘容量  end -------------------------------

        

            $otherclass = new Otherclass($this->app);

            $upload_file_name = $_FILES['files']['name'][0]; 
            $upload_file_size = $_FILES['files']['size'][0];
            $upload_file_type = $_FILES['files']['type'][0];
            $file_hash =  hash_file("sha256",$_FILES['files']['tmp_name'][0], false);

            //根据文件hash来匹配当前服务器是否有上传此文件，如果匹配到了此hash，则停止复制文件，节约服务器空间,暂时先不写这个逻辑
            $file_exist_status_data = Db::table("tp_file")->where("file_hash",$file_hash)->where("delete_status","0")->order("id","asc")->limit(1)->select();

            if(count($file_exist_status_data) == 1){
                //当前文件已经存在于数据库里
                $status = 200;
                $message = "success";
                $files_data = [
                    //"key"  => hash("sha256",$upload_file_name.time().mt_rand(1000,9999).uniqid()),
                    "name" => $upload_file_name,
                    "size" => $upload_file_size,
                    "type" => $upload_file_type,
                    "hash" => $file_hash,
                    "url" => $file_exist_status_data[0]['file_url'],
                    "timestamp" => time()
                ];
                //更新hark_link值，hard_link灵感来源于linux系统中的软连接，多一个hard_link则此值+1，用户删除文件时要先判断此值
                Db::table("tp_file")->where("id",$file_exist_status_data[0]['id'])->inc('hard_link')->update();
            }else{
 
                // if($upload_file_size  > 2147483648){   //2G
                //     echo "<font color=\"red\"size=\"2\">*Over limit ,Max size 2G.</font>";  
                //     exit;
                // }

                //echo "test";
                $extension = pathInfo($upload_file_name,PATHINFO_EXTENSION); 

                //如果文件名长度大于255，重命名文件
                if(strlen($upload_file_name) > 200){
                    $upload_file_name = substr($upload_file_name,0,100).".".$extension;
                }
                //统一将文件重命名




                //根据客户端的ip生成一个临时的文件目录
                $user_ip = $otherclass->get_user_ip();
                //echo $user_ip;
                //ufp  user file path
                $user_redis_key = Config::get("app.redis_prefix")."_ufp_".md5($user_ip);
                if(Cache::has($user_redis_key)){
                    $user_file_path = Cache::get($user_redis_key);
                }else{
                    $user_file_path = time().mt_rand(100,999);
                    Cache::set($user_redis_key, $user_file_path,60*60);
                }
                


                $base_path ="/upload/";
                $date_path = date("Ym/d/");
                //文件路径 /upload/202307/07/timestampxxx/file_name.file_extend  //去重  
                $file_path = $base_path.$date_path.$user_file_path."/"; 
                

                if(!is_dir(Config::get("app.install_path")."public".$file_path)){
                    mkdir(Config::get("app.install_path")."public".$file_path,0777,true);
                }
                
                $base_file_name = $file_path.$upload_file_name; ///upload/202307/07/xxxx/xxx.jpg
                $full_path_name = Config::get("app.install_path")."public".$base_file_name; 

                //php文件重命名文件
                if(preg_match("#php#i", $extension)){
                    //重命名文件，直接在文件后面增加一个随机字符串和文件后缀
                    $new_extension_name = "_rename_".substr(uniqid(),6,6);
                    $base_file_name = $base_file_name.$new_extension_name;
                    $full_path_name = $full_path_name.$new_extension_name;
                }

                //exe文件重命名文件,当文件小于10M时则执行重命名选项
                if(preg_match("#exe#i", $extension) && $upload_file_size<10000000){
                    //重命名文件，直接在文件后面增加一个随机字符串和文件后缀
                    $new_extension_name = "_rename_".substr(uniqid(),6,6);
                    $base_file_name = $base_file_name.$new_extension_name;
                    $full_path_name = $full_path_name.$new_extension_name;
                }

                //apk文件重命名文件
                // if(preg_match("#apk#i", $extension)){
                //     //重命名文件，直接在文件后面增加一个随机字符串和文件后缀
                //     $new_extension_name = "_rename_".substr(uniqid(),6,6);
                //     $base_file_name = $base_file_name.$new_extension_name;
                //     $full_path_name = $full_path_name.$new_extension_name;
                // }

                //判断文件是否存在
                if(file_exists($full_path_name)){
                    //重命名文件，直接在文件后面增加一个随机字符串和文件后缀,并更新base_file_name文件名
                    $new_extension_name = "_".substr(uniqid(),6,12).".".$extension;
                    $base_file_name = $base_file_name.$new_extension_name;
                    $full_path_name=$full_path_name.$new_extension_name;
                }



                
                


                
                if(move_uploaded_file($_FILES['files']['tmp_name'][0],$full_path_name)){
                    try{
                 
                        //去掉远程请求主服务器生成short_str的方案，使用从服务器自己生成
                        //$file_key = hash("sha256",$upload_file_name.time().mt_rand(1000,9999).uniqid());  已全面去掉file_key 改为file_hash定位文件
                               
                        $status = 200;
                        $message = "success";
                        $files_data = [
                            "name" => $upload_file_name,
                            "size" => $upload_file_size,
                            "type" => $upload_file_type,
                            "hash" => $file_hash,
                            "url" => Config::get("app.cdn_server_url").$base_file_name,
                            "timestamp" => time()
                        ];
                        


                        //将此条上传数据插入数据库中
                        $insert_data = [
                            "file_hash" => $file_hash,
                            "file_name" => $upload_file_name,
                            "file_size" => $upload_file_size,
                            "file_type" => $upload_file_type,
                            "file_path" => $full_path_name,
                            "file_url" => Config::get("app.cdn_server_url").$base_file_name,
                            "timestamp" => time()
                        ];

                        Db::table("tp_file")->strict(false)->insert($insert_data);


                        
                    }catch(\Exception $e){
                        var_dump($e);
                    }
    
                }else{
                    $status = 300;
                    $message = "error";
                    $files_data = [];
                }
            }   

    
            $json_data = [
                "stutas" => $status,
                "message" => $message,
                "files" => [$files_data]
            ];

             echo json_encode($json_data);
        
        }else{
            abort(404,"error");
        }

    }
}