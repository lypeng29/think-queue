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
    }
    /**
     * 任务1
     */
    public function task1(Job $job, $data) 
    {
        // echo $job->attempts()."\n";
        // echo $data['type'];
        // exit();
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