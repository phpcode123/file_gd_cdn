<?php
namespace app\index\controller;


use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cache;


class Api extends BaseController
{
    
    public function get_server_status(){
        if(Request::isPost()){
            $api_token = Request::param("api_token") ?? "";
            if($api_token == Config::get("app.api_token")){
                try{

                    exec("df -h  -m ".Config::get("app.install_path")."|awk '{print $4}'",$disk_avail);
                    

                    if($disk_avail > Config::get("app.cdn_server_disk_min_avail")){
                        $disk_status = 1;
                    }else{
                        $disk_status = 0;
                    }



                    //command running status
                    if(Cache::has(Config::get("app.redis_prefix")."file_server")){
                        if((time()-Cache::get(Config::get("app.redis_prefix")."file_server")) > 30 ){
                            $server_status = 1;
                            
                        }else{
                            $server_status = 0;
                        }
                    }else{
                        $server_status = 0;
                    }


                    $return_data = [
                        "status" => 200,
                        "message" => "success",
                        "data" => [
                            "disk_status" => $disk_status,
                            "disk_avail" => $disk_avail[1],
                            "server_status" => $server_status
                        ]
                    ];
                    
                }catch(\Exception $e){
                    $return_data = [
                        "status" => 300,
                        "message" => "Exception error",
                        "data" => []
                    ];
                }

                

            }else{
                $return_data = [
                    "status" => 300,
                    "message" => "Api token error",
                    "data" => []
                ];

            }

        }else{
            $return_data = [
                "status" => 300,
                "message" => "Just allow post method!",
                "data" => []
            ];
        }
        
        return json_encode($return_data);
       
    }




    public function delete_file(){
        if(Request::isPost()){
            $api_token = Request::param("api_token") ?? "";
            if($api_token == Config::get("app.api_token")){
                try{


                    $file_hash = Request::param("file_hash") ?? "";

                    $file_data = Db::table("tp_file")->where("file_hash",$file_hash)->where("delete_status","0")->order("id","asc")->select();

                    if(count($file_data) == 0){
                        $return_data = [
                            "status" => 300,
                            "message" => "file_data less than 1",
                            "data" => []
                        ];
                    }else{
                        //判断hark_link值是否大于0，如果大于0则先将此值减1
                        if($file_data[0]['hard_link'] > 0){

                            if(Db::table("tp_file")->where("id",$file_data[0]['id'])->dec("hard_link")->update()){
                                $message = "File deleted successful";
                                $delete_status = 1;
                            }else{
                                $message = "File deleted fail";
                                $delete_status = 0;
                            }
                        }else{
                            //当文件存在且删除状态为0时
                            if(file_exists($file_data[0]['file_path']) && $file_data[0]['delete_status']==0){
                                if(unlink($file_data[0]['file_path'])){
                                    
                                    $message = "File deleted successful";
                                    $delete_status = 1;
                                    //文件删除状态
                                    Db::table("tp_file")->where("id",$file_data[0]["id"])->update(["delete_status"=>1]);
                                }else{
                                    $message = "File deleted fail";
                                    $delete_status = 0;
                                }
                            }else{
                                $message = "file is not exists";
                                $delete_status = 1;
                                //更新文件删除状态
                                Db::table("tp_file")->where("id",$file_data[0]["id"])->update(["delete_status"=>1]);
                            }
                        }


                        
                        


                        $status_code = 200;
                        $return_data = [
                            "status" => $status_code,
                            "message" => $message,
                            "data" => [
                                "delete_status" => $delete_status,  
                            ]
                        ];
                    }





                    
                    
                }catch(\Exception $e){
                    $return_data = [
                        "status" => 300,
                        "message" => "Exception error",
                        "data" => []
                    ];
                }

            

            }else{
                $return_data = [
                    "status" => 300,
                    "message" => "Api token error",
                    "data" => []
                ];

            }

        }else{
            $return_data = [
                "status" => 300,
                "message" => "Just allow post method!",
                "data" => []
            ];
        }
        
        return json_encode($return_data);
       
    }
}