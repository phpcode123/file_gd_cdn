<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Db;


class FileServer extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('app\command\fileserver')
            ->setDescription('the app\command\fileserver command');
    }

    protected function execute(Input $input, Output $output)
    {
    
        //注意，此命令行必须使用root超级用户运行，否则可能会出现删除文件失败现象
        while(True){
            try{
                $file_data = Db::table("tp_file")->where('file_status',"-1")->order("id","desc")->select();

                for($i=0;$i<count($file_data);$i++){
                    $short_str = $file_data[$i]["short_str"];
                   

                    //------------------- http begin -------------------
                    $httpclass = new \app\index\controller\HttpClass($this->app);
                    $count_num = 0;
                    //循环5次
                    while($count_num < 5){
                        $count_num += 1;
                        $api_url = Config::get("app.main_server_url")."/index.php/api/get_file_status";
                        
                        $http_json_data = $httpclass->post(
                            $api_url,
                            [
                                "api_token" => Config::get("app.api_token"),
                                "short_str" => $short_str
                            ]
                            ,Config::get("app.api_token")
                        
                        );
                        
                        $http_data = json_decode((string)$http_json_data,true);
                        try{
                            //跳出当前循环
                            if($http_data["status"]){
                                break;
                            }
                        }catch(\Exception $e){
                            //do something
                        }
                    }
                    //------------------- http end -------------------

                    //var_dump($http_data);

                
                    //status 200是正常状态，201为主服务器中无此数据
                    if($http_data['status'] == 200){
                        $file_status = 1;
                        Db::table("tp_file")->where("id",$file_data[$i]["id"])->update(["file_status"=>$file_status]);
                        echo ">> id:{$file_data[$i]['id']} http_data_status:{$http_data['status']} file_status:1  set file_status = 1 updated".PHP_EOL;
                    }

                    if($http_data['status'] == 201){
                        $file_status=0;
                        //如果时间戳
                        if((time() - $file_data[$i]['timestamp']) > 60*60*12){
                            Db::table("tp_file")->where("id",$file_data[$i]["id"])->update(["file_status"=>$file_status]);
                            echo ">> id:{$file_data[$i]['id']} http_data_status:{$http_data['status']} file_status:0  set file_status = 0  updated  - ";
                            
                            
                            //当文件存在且删除状态为0时
                            if(file_exists($file_data[$i]['file_path']) && $file_data[0]['delete_status'] == 0){
                                if(unlink($file_data[$i]['file_path'])){
                                    echo " file deleted successful!".PHP_EOL;
                                    //文件删除状态
                                    Db::table("tp_file")->where("id",$file_data[$i]["id"])->update(["delete_status"=>1]);
                                }else{
                                    echo " file deleted fail!".PHP_EOL;
                                }
                            }else{
                                echo " file is not exists".PHP_EOL;
                                //更新文件删除状态
                                Db::table("tp_file")->where("id",$file_data[$i]["id"])->update(["delete_status"=>1]);
                            }
                            
                           
                        }else{
                            echo ">> id:{$file_data[$i]['id']} http_data_status:{$http_data['status']} file_status:0  timestamp < 12 Hour".PHP_EOL;
                        }
                        
                        
                    }

                    //设置当前command redis timestamp值，用于api/server_status的调用，当时间戳超时30秒，说明服务可能异常 
                    //echo Config::get("app.redis_prefix");
                    Cache::set(Config::get("app.redis_prefix")."file_server",time());
                    sleep(1);
                    
                }
                sleep(10);
            }catch(\Exception $e){
                dump($e);
                sleep(10);
            }
        }

    }
}
