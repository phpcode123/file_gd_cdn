<?php
namespace app\index\controller;


use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Cache;


class File extends BaseController
{

    public function download(){
        $otherclass = new Otherclass($this->app);
        $user_agent = $otherclass->get_user_agent();
        $user_ip = $otherclass->get_user_ip();
       

        //file_hash不为空
        $file_hash = Request::param("file_hash") ?? "";


        if($file_hash == ""){
            echo "file_hash error!";
            exit();
        }


        #--------- 判断file_token与timestamp begin -----------------
        
        $file_timestamp = Request::param("t") ?? 0;
        $file_token = Request::param("token") ?? "";

        $file_token_ = md5(Config::get("app.api_token").$file_timestamp);  //验证md5的token加密值

        if(($file_token != $file_token_) || (time()-$file_timestamp) > 60*30){  //超时30分针则跳转show file页面
            echo "Paramter error, Do not steal links! Please try again later.";
            exit();
        
        }
        
        #--------- 判断file_token与timestamp end -----------------








        $file_data = Db::table("tp_file")->where("file_hash",$file_hash)->where("delete_status","0")->order("id","desc")->limit(1)->select();
        if(count($file_data) == 0){
            echo "File not exest!";
            exit();
        }

        //判断文件的delete_status ,如果此状态为1则说明文件已删除
        if($file_data[0]['delete_status'] == 1){
            echo "File deleted!";
            exit();
        }else{
            //----------------------- hits点击量自增1 begin -------------------------- 
            $hits_add_key = Config::get("app.redis_prefix")."file_hits_".$file_hash."_".md5($user_agent.$user_ip);
            if(!Cache::has($hits_add_key)){
                Cache::set($hits_add_key, 1, 60*30);
                Db::table("tp_file")->where("file_hash",$file_hash)->inc('hits')->update();
                Db::table("tp_file")->where("file_hash",$file_hash)->update(['last_timestamp'=>time()]);
            }
            //----------------------- hits点击量自增1 end  -------------------------- 
        }



        //---------------- 开始下载 begin ----------------------------------
        $path=$file_data[0]['file_path'];
        if(!file_exists($path)){
           echo "File not exist!";
           exit();
        }
        
        //$fp=fopen($path,'r');
        $filesize=filesize($path);
        
        header('Content-Description:File Transfer');
        header("Content-Type:".$file_data[0]['file_type']);
        header('Content-Transfer-Encoding:binary');
        header("Accept-Ranges: bytes");
        header('Expires:0');
        header('Cache-Control:must-revalidate');
        header('Pragma:public');
        header("Content-Length:".$filesize);
        header("Content-Disposition:attachment;filename=".$file_data[0]['file_name']);
        $fp = fopen($path, "rb"); 
        fseek($fp,0); 
        while (!feof($fp)) { 
            set_time_limit(0); 
            print (fread($fp, 1024 * 8)); 
            flush(); 
            ob_flush(); 
        } 
        fclose($fp); 
        exit ();
        

    }


}