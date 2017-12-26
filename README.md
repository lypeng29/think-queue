# think-queue
使用的thinkPHP5.0与topthink/think-queue ^v1.1.4，参考：<a href="https://github.com/tp5er/think-queue/tree/master/doc" target="_blank">https://github.com/tp5er/think-queue/tree/master/doc</a>

## 逻辑流程
- 前端不停的会生成任务：Queue::push();
- 命令窗口监听执行：php think queue:listen

## 环境与版本
- php5.5(php5.5之前的版本，不支持queue类中的try{}finally{}写法)
- tp5.0（我用的最新版5.0.13）
- think-queue v1.1.4（最新v2.0要求tp5.1，不支持thinkPHP5.0）

## 安装tp5.0
```bash
cd E:\www\
composer create-project topthink/think s1  --prefer-dist
```
## 安装think-queue
```bash
cd E:\www\s1\
composer require topthink/think-queue ^v1.1.4
```
注：这里需要指定版本，think-queue最新版为v2.0(现在是2017-12-26)，默认会安装V2.0，但是v2.0要求thinkPHP版本为v5.1，不支持thinkPHP5.0

## 检测安装结果
打开cmd，进入项目根目录，执行`php think`，里面如果能看到queue的几个命令说明OK了，如下图：
![](http://www.lypeng.com/Uploads/2017/5a420ed61ff63.jpg)

## 建立数据表(注意修改表前缀配置)
```mysql
CREATE TABLE `dp_jobs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` TINYINT(3) UNSIGNED NOT NULL,
  `reserved` TINYINT(3) UNSIGNED NOT NULL,
  `reserved_at` INT(10) UNSIGNED DEFAULT NULL,
  `available_at` INT(10) UNSIGNED NOT NULL,
  `created_at` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;
```
## 修改配置
1. extra\queue.php文件,`'connector' => 'database'`（这里采用mysql数据库方式）
2. database.php文件,root root 3306 s1 dp_

## 代码编写
这里我把任务的生成与执行都放到一个文件了，新建文件：app\home\controller\Jobs.php，代码如下：
``` php
<?php
namespace app\home\controller;
use think\Queue;
use think\queue\Job;
class Jobs
{
    public function index(){
        //生成任务
        $data = array(
            'type'=>'search',
            'key'=>'222',
        );
        Queue::push('app\home\controller\Jobs@task1', $data, $queue = null);
		//三个参数依次为：需要执行的方法，传输的数据，任务名默认为default
    }
    /**
     * 任务1
     */
    public function task1(Job $job, $data)
    {
        //处理任务逻辑
        if($data['type'] == 'search'){
            $result = file_get_contents('http://www.baidu.com/s?wd='.$data['key']);
            if($result){
                echo "task1 success \n";
                $isJobDone = true;
            }else{
                echo "task1 failed \n";
                $isJobDone = false;
            }
        }else{
            echo "task1 failed \n";
            $isJobDone = false;
        }

        //执行结果处理
        if ($isJobDone) {
            //成功删除任务
            $job->delete();
        } else {
            //任务轮询4次后删除
            if ($job->attempts()>3) {
                // 第1种处理方式：重新发布任务,该任务延迟10秒后再执行
                // $job->release(10); 
                // 第2种处理方式：原任务的基础上1分钟执行一次并增加尝试次数
                //$job->failed();   
                // 第3种处理方式：删除任务
                $job->delete();  
            }
        }
    }
    //任务2
    public function task2(){
        //... ...
    }
}
?>
```

## 效果体验
`E:\www\s1>php public/index.php home/jobs/index`，执行命令后，数据库会多一条记录，如下图
![](http://www.lypeng.com/Uploads/2017/5a420cb1281a9.png)
`php think queue:listen`，执行后，窗口处于监听状态，会自动执行刚入库的记录，然后你在新增一条消息，监听到记录，又会开始执行！如下图
![](http://www.lypeng.com/Uploads/2017/5a420cce2d270.jpg)
## 说明
代码里面关于执行失败，有下面三种处理方法，但是测试不理想，开启了第三种，但感觉一直是删除原任务，发布新任务，并在原次数上增加尝试次数~
``` php
// 第1种处理方式：重新发布任务,该任务延迟10秒后再执行
//$job->release(10);
// 第2种处理方式：原任务的基础上1分钟执行一次并增加尝试次数
//$job->failed();
// 第3种处理方式：删除任务
$job->delete();
```

## 代码下载
<a href="https://github.com/lypeng29/think-queue" target="_blank">https://github.com/lypeng29/think-queue</a>
