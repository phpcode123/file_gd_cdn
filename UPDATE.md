## 版本更新说明

## CDN的更新记录于主程序的UPDATE文件中，此文件不再记录版本更新。

## --20230915
* 新增文件hard_link项，主要用于文件去重复，文件通过file_hash来定位，上传多个相同文件时只增加hard_link值，避免浪费服务器储存空间。当多个相同文件，用户删除文件时只减少file_hash值。此项多文件增值灵感来源于linux下文件软链接原理。
* 去掉了一些数据库无用的表项

## --20230713 
* 从服务器有远程请求主服务器short_str改为从服务器主动生成file_key,由从服务器file_key来确认定位文件。


## FILE_GD_CDN --20230705   
* File Uploader初始版本上线开发 服务器端