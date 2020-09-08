<?php
// +----------------------------------------------------------------------
// | 定时任务
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/4/1 20:53
// +----------------------------------------------------------------------

namespace app\swoole\command;

use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\Log;
use swoole_process;
use swoole_table;


class Cron extends Command
{
    //定时器轮询周期，精确到毫秒
    protected $tickTime;

    protected $daemon = false;

    //等于定时任务的数量
    protected $workerNum;

    //存定时任务表
    protected $table;

    //任务
    protected $tasks;

    public function configure()
    {
        $this->setName('cron')
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart|reload", 'start')
            ->addOption('daemon', 'd', Option::VALUE_NONE, '是否开启守护进程模式')
            ->setDescription('Xingo定时任务');
    }

    /**
     * 执行
     * @param Input $input
     * @param Output $output
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/18 10:54
     */
    public function execute(Input $input, Output $output)
    {
        $action = $input->getArgument('action');
        if (!in_array($action, ['start', 'stop', 'reload', 'restart'])) {
            $output->writeln("<error>Invalid argument action:{$action}, Expected start|stop|restart|reload .</error>");
            return false;
        }

        $this->init();
        $this->$action();
    }

    public function init()
    {
        //每分钟读取定时任务配置，存入swoole内存table
    }

    /**
     * 设置信号监听
     * @Author: wuyh
     * @Date: 2020/4/1 21:04
     */
    private function setSignal()
    {
        swoole_process::signal(SIGCHLD, function ($signo) {
            static $worker_count = 0;
            while ($ret = swoole_process::wait(false)) {
                $worker_count++;
                $workerNum = $this->table->get('workers_num');
                Log::record("PID={$ret['pid']}worker进程退出!", [$workerNum, $signo], 'cron');

                if ($worker_count >= $workerNum['workers_num']) {
                    Log::record("主进程退出!", [], 'cron');
                    foreach ($this->jobs as $job) {
                        $this->removeWorkPid($job['name']);
                    }

                   $this->removePid();
                    swoole_process::kill($this->getPid(), SIGKILL);
                }
            }
        });
    }



    /**
     * 定时任务列表
     * @return array
     * @Author: wuyh
     * @Date: 2020/4/1 21:22
     */
    private function getTasks()
    {
        $tasks = [
            [
                'name' => 'initUserForQueue',//任务名
                'time' => '*/1 * * * *',//定时规则 分 小时 天 周 月
                'command' => 'app\swoole\Cron\closeLive',//清理缓存里未关闭的直播间
            ],
        ];

        return $tasks;
    }


    /**
     * 启动worker进程处理定时任务
     *
     */
    private function startWorkers()
    {
        $this->table = new swoole_table(1024);
        $this->table->column("workers_num", swoole_table::TYPE_INT);

        foreach ($this->tasks as $job) {
            $this->table->column($job['name'] . '_worker', swoole_table::TYPE_STRING, 1024 * 20);
        }
        $this->table->create();
        $this->table->set('workers_num', ["workers_num" => 0]);
        $this->table->incr('workers_num', 'workers_num', $this->workerNum);

        //启动worker进程
        for ($i = 0; $i < $this->workerNum; $i++) {
            $this->newProcess($i);
        }
    }

    /**
     * 注册定时任务
     * @Author: wuyh
     * @Date: 2020/4/1 21:28
     */
    static protected function register_timer()
    {
        $_this = $this;
        swoole_timer_tick(60000, function () use ($_this){
            $_this->tasks = $this->getTasks();
        });

    }

    /**
     * 创建新的进程
     * @param $i
     * @Author: wuyh
     * @Date: 2020/4/1 21:21
     */
    private function newProcess($i)
    {
        $process    = new swoole_process(array($this, 'workerCallBack'), false);
        $processPid = $process->start();
        Log::record("工作worker{$processPid}启动", [$this->jobs[$i]['name']], 'cron.work');
    }

    /**
     * 子进程创建成功后的回调
     * @param swoole_process $worker
     * @Author: wuyh
     * @Date: 2020/4/1 21:22
     */
    public function workerCallBack(swoole_process $worker)
    {

    }


    /**
     * 启动
     * @Author: wuyh
     * @Date: 2020/4/1 21:35
     */
    public function start()
    {
        $this->output->writeln('定时任务开启...');

        if ($this->daemon) {
            swoole_process::daemon(true);
        }

        swoole_set_process_name('wyh_crontab_master');

        $this->startWorkers();

        $this->setSignal();

    }


    /**
     * 删除PID文件
     * @Author: wuyh
     * @Date: 2020/3/18 13:49
     */
    protected function removePid()
    {
        $masterPid = $this->config['pid_file'];

        if (is_file($masterPid)) {
            @unlink($masterPid);
        }
    }

    /**
     * 删除任务进程的Pid文件
     * @param $jobName
     * @Author: wuyh
     * @Date: 2020/4/1 21:34
     */
    public function removeWorkPid($jobName)
    {
        @unlink(RUNTIME_PATH. $jobName . "/work_id");
    }
}