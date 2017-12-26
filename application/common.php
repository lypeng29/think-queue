<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function logs($word='') {
    $fp = fopen("up.txt","a");
    flock($fp, LOCK_EX) ;
    fwrite($fp, var_export($word, true));
    fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time()));
    flock($fp, LOCK_UN);
    fclose($fp);
}
function lastsql() {
  echo(M()->getLastSql());
}