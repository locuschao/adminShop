<?php
//redis处理延迟队列
namespace app\common\service;
use think\Cache;

class DelayQueueService{

    protected $prefix = 'delay_queue:';
    protected $redis = null;
    protected $key = '';

    public function __construct($queue)
    {
        $this->key = $this->prefix . $queue;
        $this->redis = Cache::store('redis')->handler();
    }

    public function delTask($value)
    {
        return $this->redis->zRem($this->key, $value);
    }

    public function getTask()
    {

        //获取任务，以0和当前时间为区间，返回一条记录
        return $this->redis->zRangeByScore($this->key, 0, time(), ['limit' => [0, 1]]);
    }

    public function addTask($name, $time, $data)
    {
        //添加任务，以时间作为score，对任务队列按时间从小到大排序
        return $this->redis->zAdd(
            $this->key,
            $time,
            json_encode([
                'task_name' => $name,
                'task_time' => $time,
                'task_params' => $data,
            ], JSON_UNESCAPED_UNICODE)
        );
    }

    public function run()
    {
        //每次只取一条任务
        $task = $this->getTask();
        if (empty($task)) {
            return false;
        }
        $task = $task[0];
        //有并发的可能，这里通过zrem返回值判断谁抢到该任务
        if ($this->delTask($task)) {
            $task = json_decode($task, true);
            echo '任务：' . $task['task_name'] . ' 运行时间：' . date('Y-m-d H:i:s') . PHP_EOL;

            return true;
        }

        return false;
    }

}
