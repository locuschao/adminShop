<?php
// +----------------------------------------------------------------------
// | Swoole服务
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 rights reserved.
// +----------------------------------------------------------------------
// | Author: wuyh
// +----------------------------------------------------------------------
// | Date: 2020/3/18 10:34
// +----------------------------------------------------------------------
namespace app\swoole\command;

use think\Config;
use Swoole\Process;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Server extends Command
{
    //swoole的参数
    protected $swoole;
    protected $serverType;
    protected $sockType;
    protected $mode;
    protected $host   = '0.0.0.0';
    protected $port   = 9501;
    protected $config = [];
    /**
     * 命令行配置
     * @Author: wuyh
     * @Date: 2020/3/18 10:53
     */
    public function configure()
    {
        $this->setName('server:ws')
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart|reload", 'start')
            ->addOption('host', 'H', Option::VALUE_OPTIONAL, '服务地址', null)
            ->addOption('port', 'p', Option::VALUE_OPTIONAL, '监听端口', null)
            ->addOption('daemon', 'd', Option::VALUE_NONE, '是是否开启守护进程模式')
            ->setDescription('Websocket服务');
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

    /**
     * 初始化
     * @Author: wuyh
     * @Date: 2020/3/18 11:12
     */
    protected function init()
    {
        $this->config = Config::get('server');


        if (empty($this->config['pid_file'])) {
            $this->config['pid_file'] = RUNTIME_PATH . 'swoole_server.pid';
        }
        $this->config['pid_file'] .= '_' . $this->getPort();
    }


    /**
     * 启动服务（包括SWOOLE HTTP, SERVER, WEBSOCEKT服务）
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/18 11:08
     */
    public function start()
    {
        $pid = $this->getMasterPid();

        if ($this->isRunning($pid)) {
            $this->output->writeln('<error>服务进程已经在运行.</error>');
            return false;
        }

        $host = $this->getHost();
        $port = $this->getPort();
        $type = !empty($this->config['type']) ? $this->config['type'] : 'socket';
        $mode = !empty($this->config['mode']) ? $this->config['mode'] : SWOOLE_PROCESS;
        $sockType = !empty($this->config['sock_type']) ? $this->config['sock_type'] : SWOOLE_SOCK_TCP;

        switch ($type) {
            case 'socket':
                $swooleClass = 'Swoole\Websocket\Server';
                break;
            case 'http':
                $swooleClass = 'Swoole\Http\Server';
                break;
            default:
                $swooleClass = 'Swoole\Server';
        }

        $this->swoole = new $swooleClass($host, $port, $mode, $sockType);

        // 开启守护进程模式
        if ($this->input->hasOption('daemon')) {
            $this->config['daemonize'] = true;
        }

        foreach ($this->config as $name => $val) {
            if (0 === strpos($name, 'on')) {
                $this->swoole->on(substr($name, 2), $val);
                unset($this->config[$name]);
            }
        }

        //为了让http也能调用服务，所以onRequest事件回调写在这
        $server = $this->swoole;
        $this->swoole->on('request', function ($request, $response) use ($server) {
            call_user_func_array([new \app\swoole\service\Server(), 'onRequest'], [$request, $response, $server]);
        });

        // 设置服务器参数
        $this->swoole->set($this->config);

        $this->output->writeln("Swoole {$type} server started: <{$host}:{$port}>" . PHP_EOL);
        $this->output->writeln('You can exit with <info>`CTRL-C`</info>');

        swoole_set_process_name($this->config['master_process_name']);

        // 启动服务
        $this->swoole->start();
    }

    /**
     * 平滑重启server
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/18 13:49
     */
    protected function reload()
    {
        // 柔性重启使用管理PID
        $pid = $this->getMasterPid();

        if (!$this->isRunning($pid)) {
            $this->output->writeln('<error>没有服务进程在运行.</error>');
            return false;
        }

        $this->output->writeln('重启服务...');
        Process::kill($pid, SIGUSR1);
        $this->output->writeln('> success');
    }

    /**
     * 停止server
     * @return bool
     * @Author: wuyh
     * @Date: 2020/3/18 13:49
     */
    protected function stop()
    {
        $pid = $this->getMasterPid();

        if (!$this->isRunning($pid)) {
            $this->output->writeln('<error>没有服务进程在运行.</error>');
            return false;
        }

        $this->output->writeln('正在停止服务...');

        Process::kill($pid, SIGTERM);
        $this->removePid();

        $this->output->writeln('> success');
    }

    /**
     * 重启server
     * @Author: wuyh
     * @Date: 2020/3/18 13:49
     */
    protected function restart()
    {
        $pid = $this->getMasterPid();

        if ($this->isRunning($pid)) {
            $this->stop();
        }

        sleep(2);

        $this->start();
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
            unlink($masterPid);
        }
    }

    /**
     * 获取主进程PID
     * @return int
     * @Author: wuyh
     * @Date: 2020/3/18 13:50
     */
    protected function getMasterPid()
    {
        $pidFile = $this->config['pid_file'];

        if (is_file($pidFile)) {
            $masterPid = (int)file_get_contents($pidFile);
        } else {
            $masterPid = 0;
        }

        return $masterPid;
    }

    /**
     * 获取端口
     * @return int|mixed
     * @Author: wuyh
     * @Date: 2020/3/18 18:05
     */
    protected function getPort()
    {
        if ($this->input->hasOption('port')) {
            $port = $this->input->getOption('port');
        } else {
            $port = !empty($this->config['port']) ? $this->config['port'] : 9501;
        }

        return $port;
    }

    /**
     * 判断PID是否在运行
     * @access protected
     * @param  int $pid
     * @return bool
     */
    protected function isRunning($pid)
    {
        if (empty($pid)) {
            return false;
        }

        return Process::kill($pid, 0);
    }

    /**
     * 获取HOST
     * 可在命令行里传参
     * @return mixed|string
     * @Author: wuyh
     * @Date: 2020/3/18 18:05
     */
    protected function getHost()
    {
        if ($this->input->hasOption('host')) {
            $host = $this->input->getOption('host');
        } else {
            $host = !empty($this->config['host']) ? $this->config['host'] : '0.0.0.0';
        }

        return $host;
    }
}