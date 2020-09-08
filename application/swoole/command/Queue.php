<?php
// +----------------------------------------------------------------------
// | 队列
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


class Queue extends Command
{
    public function configure()
    {
        $this->setName('queue')
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart|reload", 'start')
            ->addOption('daemon', 'd', Option::VALUE_NONE, '是否开启守护进程模式')
            ->setDescription('XinGo队列');
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
//        $action = $input->getArgument('action');
//        if (!in_array($action, ['start', 'stop', 'reload', 'restart'])) {
//            $output->writeln("<error>Invalid argument action:{$action}, Expected start|stop|restart|reload .</error>");
//            return false;
//        }
//
//        $this->init();
//        $this->$action();

        return ;
    }
}