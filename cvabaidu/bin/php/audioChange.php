<?php
/**
 * Created by PhpStorm.
 * User: mengyuan
 * Date: 18/04/22
 * Time: 下午3:35
 * explain: php run.php  audioChange setAudioInfo
 * status 字段说明
 * -3 接口地址打不开,
 * -2 接口地址没有m3u8地址,
 * -1 m3u8地址无法访问,
 * 0 正常,
 * 1 正在识别,
 * 2 识别完成,
 * 4 断的次数较多,
 * 5 1分钟内没有正常的ts,
 * 6 时长小于10秒,
 * 7 识别失败, 格式或者采样率不正确
 * 8 人工,
 * 9 其他失败
 *10 语音清晰度不够
 */

include "../../lib/common/DbInfo.php";
include "../../lib/Convert.php";

Class audioChange
{
    static $appkeyMap = [
        0 => [AUDIO_APP_ID, AUDIO_API_KEY, AUDIO_SECRET_KEY],
        1 => [AUDIO_APP_ID1, AUDIO_API_KEY1, AUDIO_SECRET_KEY1],
        2 => [AUDIO_APP_ID2, AUDIO_API_KEY2, AUDIO_SECRET_KEY2],
        3 => [AUDIO_APP_ID3, AUDIO_API_KEY3, AUDIO_SECRET_KEY3],
        4 => [AUDIO_APP_ID4, AUDIO_API_KEY4, AUDIO_SECRET_KEY4],
        5 => [AUDIO_APP_ID5, AUDIO_API_KEY5, AUDIO_SECRET_KEY5],
        6 => [AUDIO_APP_ID6, AUDIO_API_KEY6, AUDIO_SECRET_KEY6],
        7 => [AUDIO_APP_ID7, AUDIO_API_KEY7, AUDIO_SECRET_KEY7],
    ];
    /**
     * 单进程转换
     */
    public static function setAudioInfo()
    {
        ini_set('date.timezone','PRC');
        $db = DbInfo::getConnect();
	$time = time() - (10 * 60);//只拿最近10分钟的任务;
        $result = mysqli_query($db, "SELECT * FROM task WHERE status = 0 AND start >= $time ORDER BY start desc");
        while ($row = mysqli_fetch_array($result)) {
            if (empty($row['path'])) {
                Tool::log_print('error', 'path is empty');
                $sql = "UPDATE task SET status = 9 WHERE task_uuid = '{$row['task_uuid']}'";
                mysqli_query($db, $sql);
                continue;
            }
            if (!file_exists($row['path'])) {
                Tool::log_print('error', 'file not exists');
                $sql = "UPDATE task SET status = 9 WHERE task_uuid = '{$row['task_uuid']}'";
                mysqli_query($db, $sql);
                continue;
            }
            $sql = "UPDATE task SET status = 1 WHERE task_uuid = '{$row['task_uuid']}'";
            mysqli_query($db, $sql);
            //转换
            $audResult = Convert::setAudio(AUDIO_APP_ID, AUDIO_API_KEY, AUDIO_SECRET_KEY, $row['path'], 'amr');
            if ($audResult['err_no'] == 3301) {
                $sql = "UPDATE task SET status = 10 WHERE task_uuid = '{$row['task_uuid']}'";
                mysqli_query($db, $sql);
                continue;
            }
            if ($audResult['err_no'] == 3312) {
                $sql = "UPDATE task SET status = 7 WHERE task_uuid = '{$row['task_uuid']}'";
                mysqli_query($db, $sql);
                continue;
            }
            if ($audResult['err_msg'] == 'success.') {
                //分词
                $ret = Convert::setPpl(AUDIO_APP_ID2, AUDIO_API_KEY2, AUDIO_SECRET_KEY2, $audResult['result'][0]);
                if (!empty($ret)) {
                    $arr = ['start', 'start_time', 'end', 'end_time', 'frase', 'keyword', 'create_time', 'update_time', 'task_uuid', 'channel'];
                    $date = date("Y-m-d H:i:s");
                    $key = implode(',', $arr);
                    $sql = "INSERT  INTO keyword ({$key}) values ('{$row['start']}', '{$row['start_time']}', '{$row['end']}', '{$row['end_time']}', '{$ret['frase']}', '{$ret['keyword']}', '{$date}', '{$date}', '{$row['task_uuid']}', '{$row['channel']}')";
                    //var_dump($sql);
                    mysqli_query($db, $sql);
                    $sql = "UPDATE task SET status = 2 WHERE task_uuid = '{$row['task_uuid']}'";
                    mysqli_query($db, $sql);
                }
            }
        }
        
        mysqli_free_result($result);
        mysqli_close($db);
    }

    /**
     * work 结藕处理逻辑
     */
    public static function workInfo($appId, $apiKey, $apiSecret, $startTime, $endTime)
    {
        ini_set('date.timezone','PRC');
        $db = DbInfo::getConnect();
        $result = mysqli_query($db, "SELECT * FROM task WHERE status = 0 and unix_timestamp(create_time)>{$startTime} and unix_timestamp(create_time)<{$endTime} ORDER BY start desc");
        if(!empty($result))
        {
            while ($row = mysqli_fetch_array($result)) {
                //var_dump($row);
                if (empty($row['path'])) {
                    Tool::log_print('error', 'path is empty');
                    $sql = "UPDATE task SET status = 9 WHERE task_uuid = '{$row['task_uuid']}'";
                    mysqli_query($db, $sql);
                    continue;
                }
                if (!file_exists($row['path'])) {
                    Tool::log_print('error', 'file not exists');
                    $sql = "UPDATE task SET status = 9 WHERE task_uuid = '{$row['task_uuid']}'";
                    mysqli_query($db, $sql);
                    continue;
                }
                $sql = "UPDATE task SET status = 1 WHERE task_uuid = '{$row['task_uuid']}'";
                mysqli_query($db, $sql);
                //转换
                $audResult = Convert::setAudio($appId, $apiKey, $apiSecret, $row['path'], 'amr');
                if ($audResult['err_no'] == 3301) {
                    $sql = "UPDATE task SET status = 10 WHERE task_uuid = '{$row['task_uuid']}'";
                    mysqli_query($db, $sql);
                    continue;
                }
                if ($audResult['err_no'] == 3312) {
                    $sql = "UPDATE task SET status = 7 WHERE task_uuid = '{$row['task_uuid']}'";
                    mysqli_query($db, $sql);
                    continue;
                }
                if ($audResult['err_msg'] == 'success.') {
                    //分词
                    $ret = Convert::setPpl($appId, $apiKey, $apiSecret, $audResult['result'][0]);
                    if (!empty($ret)) {
                        $arr = ['start', 'start_time', 'end', 'end_time', 'frase', 'keyword', 'create_time', 'update_time', 'task_uuid', 'channel'];
                        $date = date("Y-m-d H:i:s");
                        $key = implode(',', $arr);
                        $sql = "INSERT  INTO keyword ({$key}) values ('{$row['start']}', '{$row['start_time']}', '{$row['end']}', '{$row['end_time']}', '{$ret['frase']}', '{$ret['keyword']}', '{$date}', '{$date}', '{$row['task_uuid']}', '{$row['channel']}')";
                        //var_dump($sql);
                        mysqli_query($db, $sql);
                        $sql = "UPDATE task SET status = 2 WHERE task_uuid = '{$row['task_uuid']}'";
                        mysqli_query($db, $sql);
                    }
                } else {
                    $sql = "UPDATE task SET status = 0 WHERE task_uuid = '{$row['task_uuid']}'";
                    mysqli_query($db, $sql);
                }
            }
            mysqli_free_result($result);
        }

        mysqli_close($db);
    }

    /**
     * 多进程转换 main
     * explain: php run.php  audioChange setAudioInfoNew
     */
    public static function setAudioInfoNew()
    {
        ini_set('date.timezone','PRC');
        for($i=0; $i<PROCESSES; $i++)
        {
            $ppid = posix_getpid();
            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new Exception('fork error!');
            } elseif ($pid > 0) {
                cli_set_process_title("php-process changeapp I am master,my pid is {$ppid}.");
                //sleep(30);
            } else {
                $startTimeStamp = strtotime(date('Y-m-d',time()));
                //$startTimeStamp = strtotime("-1 day");
                $cpid = posix_getpid();
                cli_set_process_title("php-process changeapp I am work,my pid is {$cpid}.");
                $startTime = $startTimeStamp + $i * 1350;
                $endTime = $startTimeStamp + ($i+1) * 1350;
                self::workInfo(AUDIO_APP_ID, AUDIO_API_KEY, AUDIO_SECRET_KEY, $startTime, $endTime);
                //echo $cpid;
                //sleep(10);
                exit;
            }
        }

        while (pcntl_waitpid(0, $status) != -1) {
            $status = pcntl_wexitstatus($status);
            echo "Work $status completed\n";
        }
    }

    public static function setAudioInfoNew1()
    {
        ini_set('date.timezone','PRC');
        for($i=0; $i<PROCESSES; $i++)
        {
            $ppid = posix_getpid();
            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new Exception('fork error!');
            } elseif ($pid > 0) {
                cli_set_process_title("php-process changeapp I am master,my pid is {$ppid}.");
                //sleep(30);
            } else {
                $startTimeStamp = strtotime(date('Y-m-d',time()));
                //$startTimeStamp = strtotime("-1 day");
                $cpid = posix_getpid();
                cli_set_process_title("php-process changeapp I am work,my pid is {$cpid}.");
                $startTime = $startTimeStamp + $i * 1350 + 10800;
                $endTime = $startTimeStamp + ($i+1) * 1350 + 10800;
                self::workInfo(AUDIO_APP_ID1, AUDIO_API_KEY1, AUDIO_SECRET_KEY1, $startTime, $endTime);
                //echo $cpid;
                //sleep(10);
                exit;
            }
        }

        while (pcntl_waitpid(0, $status) != -1) {
            $status = pcntl_wexitstatus($status);
            echo "Work $status completed\n";
        }
    }

    public static function setAudioInfoNew2()
    {
        ini_set('date.timezone','PRC');
        for($i=0; $i<PROCESSES; $i++)
        {
            $ppid = posix_getpid();
            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new Exception('fork error!');
            } elseif ($pid > 0) {
                cli_set_process_title("php-process changeapp I am master,my pid is {$ppid}.");
                //sleep(30);
            } else {
                $startTimeStamp = strtotime(date('Y-m-d',time()));
                //$startTimeStamp = strtotime("-1 day");
                $cpid = posix_getpid();
                cli_set_process_title("php-process changeapp I am work,my pid is {$cpid}.");
                $startTime = $startTimeStamp + $i * 1350 + 21600;
                $endTime = $startTimeStamp + ($i+1) * 1350 + 21600;
                self::workInfo(AUDIO_APP_ID2, AUDIO_API_KEY2, AUDIO_SECRET_KEY2, $startTime, $endTime);
                //echo $cpid;
                //sleep(10);
                exit;
            }
        }

        while (pcntl_waitpid(0, $status) != -1) {
            $status = pcntl_wexitstatus($status);
            echo "Work $status completed\n";
        }
    }

    public static function setAudioInfoNew3()
    {
        ini_set('date.timezone','PRC');
        for($i=0; $i<PROCESSES; $i++)
        {
            $ppid = posix_getpid();
            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new Exception('fork error!');
            } elseif ($pid > 0) {
                cli_set_process_title("php-process changeapp I am master,my pid is {$ppid}.");
                //sleep(30);
            } else {
                $startTimeStamp = strtotime(date('Y-m-d',time()));
                //$startTimeStamp = strtotime("-1 day");
                $cpid = posix_getpid();
                cli_set_process_title("php-process changeapp I am work,my pid is {$cpid}.");
                $startTime = $startTimeStamp + $i * 1350 + 32400;
                $endTime = $startTimeStamp + ($i+1) * 1350 + 32400;
                self::workInfo(AUDIO_APP_ID3, AUDIO_API_KEY3, AUDIO_SECRET_KEY3, $startTime, $endTime);
                //echo $cpid;
                //sleep(10);
                exit;
            }
        }

        while (pcntl_waitpid(0, $status) != -1) {
            $status = pcntl_wexitstatus($status);
            echo "Work $status completed\n";
        }
    }

    public static function setAudioInfoNew4()
    {
        ini_set('date.timezone','PRC');
        for($i=0; $i<PROCESSES; $i++)
        {
            $ppid = posix_getpid();
            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new Exception('fork error!');
            } elseif ($pid > 0) {
                cli_set_process_title("php-process changeapp I am master,my pid is {$ppid}.");
                //sleep(30);
            } else {
                $startTimeStamp = strtotime(date('Y-m-d',time()));
                //$startTimeStamp = strtotime("-1 day");
                $cpid = posix_getpid();
                cli_set_process_title("php-process changeapp I am work,my pid is {$cpid}.");
                $startTime = $startTimeStamp + $i * 1350 + 43200;
                $endTime = $startTimeStamp + ($i+1) * 1350 + 43200;
                self::workInfo(AUDIO_APP_ID4, AUDIO_API_KEY4, AUDIO_SECRET_KEY4, $startTime, $endTime);
                //echo $cpid;
                //sleep(10);
                exit;
            }
        }

        while (pcntl_waitpid(0, $status) != -1) {
            $status = pcntl_wexitstatus($status);
            echo "Work $status completed\n";
        }
    }

    public static function setAudioInfoNew5()
    {
        ini_set('date.timezone','PRC');
        for($i=0; $i<PROCESSES; $i++)
        {
            $ppid = posix_getpid();
            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new Exception('fork error!');
            } elseif ($pid > 0) {
                cli_set_process_title("php-process changeapp I am master,my pid is {$ppid}.");
                //sleep(30);
            } else {
                $startTimeStamp = strtotime(date('Y-m-d',time()));
                //$startTimeStamp = strtotime("-1 day");
                $cpid = posix_getpid();
                cli_set_process_title("php-process changeapp I am work,my pid is {$cpid}.");
                $startTime = $startTimeStamp + $i * 1350 + 54000;
                $endTime = $startTimeStamp + ($i+1) * 1350 + 54000;
                self::workInfo(AUDIO_APP_ID5, AUDIO_API_KEY5, AUDIO_SECRET_KEY5, $startTime, $endTime);
                //echo $cpid;
                //sleep(10);
                exit;
            }
        }

        while (pcntl_waitpid(0, $status) != -1) {
            $status = pcntl_wexitstatus($status);
            echo "Work $status completed\n";
        }
    }

    public static function setAudioInfoNew6()
    {
        ini_set('date.timezone','PRC');
        for($i=0; $i<PROCESSES; $i++)
        {
            $ppid = posix_getpid();
            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new Exception('fork error!');
            } elseif ($pid > 0) {
                cli_set_process_title("php-process changeapp I am master,my pid is {$ppid}.");
                //sleep(30);
            } else {
                $startTimeStamp = strtotime(date('Y-m-d',time()));
                //$startTimeStamp = strtotime("-1 day");
                $cpid = posix_getpid();
                cli_set_process_title("php-process changeapp I am work,my pid is {$cpid}.");
                $startTime = $startTimeStamp + $i * 1350 + 64800;
                $endTime = $startTimeStamp + ($i+1) * 1350 + 64800;
                self::workInfo(AUDIO_APP_ID6, AUDIO_API_KEY6, AUDIO_SECRET_KEY6, $startTime, $endTime);
                //echo $cpid;
                //sleep(10);
                exit;
            }
        }

        while (pcntl_waitpid(0, $status) != -1) {
            $status = pcntl_wexitstatus($status);
            echo "Work $status completed\n";
        }
    }

    public static function setAudioInfoNew7()
    {
        ini_set('date.timezone','PRC');
        for($i=0; $i<PROCESSES; $i++)
        {
            $ppid = posix_getpid();
            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new Exception('fork error!');
            } elseif ($pid > 0) {
                cli_set_process_title("php-process changeapp I am master,my pid is {$ppid}.");
                //sleep(30);
            } else {
                //$startTimeStamp = strtotime(date('Y-m-d',time()));
                $startTimeStamp = strtotime("-1 day");
                $cpid = posix_getpid();
                cli_set_process_title("php-process changeapp I am work,my pid is {$cpid}.");
                $startTime = $startTimeStamp + $i * 1350 + 75600;
                $endTime = $startTimeStamp + ($i+1) * 1350 + 75600;
                self::workInfo(AUDIO_APP_ID7, AUDIO_API_KEY7, AUDIO_SECRET_KEY7, $startTime, $endTime);
                //echo $cpid;
                //sleep(10);
                exit;
            }
        }

        while (pcntl_waitpid(0, $status) != -1) {
            $status = pcntl_wexitstatus($status);
            echo "Work $status completed\n";
        }
    }

    public static function workInfoNew($appId, $apiKey, $apiSecret, $startTime, $endTime)
    {
        ini_set('date.timezone','PRC');
        $db = DbInfo::getConnect();
        $result = mysqli_query($db, "SELECT * FROM task WHERE status = 0 and unix_timestamp(create_time)>{$startTime} and unix_timestamp(create_time)<{$endTime} ORDER BY start desc");
        if(!empty($result))
        {
            while ($row = mysqli_fetch_array($result)) {
                //var_dump($row);
                //var_dump($row);
                if (empty($row['path'])) {
                    Tool::log_print('error', 'path is empty');
                    $sql = "UPDATE task SET status = 9 WHERE task_uuid = '{$row['task_uuid']}'";
                    mysqli_query($db, $sql);
                    continue;
                }
                if (!file_exists($row['path'])) {
                    Tool::log_print('error', 'file not exists');
                    $sql = "UPDATE task SET status = 9 WHERE task_uuid = '{$row['task_uuid']}'";
                    mysqli_query($db, $sql);
                    continue;
                }
                $sql = "UPDATE task SET status = 1 WHERE task_uuid = '{$row['task_uuid']}'";
                mysqli_query($db, $sql);
                //转换
                $audResult = Convert::setAudio($appId, $apiKey, $apiSecret, $row['path'], 'amr');
                if ($audResult['err_no'] == 3301) {
                    $sql = "UPDATE task SET status = 10 WHERE task_uuid = '{$row['task_uuid']}'";
                    mysqli_query($db, $sql);
                    continue;
                }
                if ($audResult['err_no'] == 3312) {
                    $sql = "UPDATE task SET status = 7 WHERE task_uuid = '{$row['task_uuid']}'";
                    mysqli_query($db, $sql);
                    continue;
                }
                if ($audResult['err_msg'] == 'success.') {
                    //分词
                    $ret = Convert::setPpl($appId, $apiKey, $apiSecret, $audResult['result'][0]);
                    if (!empty($ret)) {
                        $arr = ['start', 'start_time', 'end', 'end_time', 'frase', 'keyword', 'create_time', 'update_time', 'task_uuid', 'channel'];
                        $date = date("Y-m-d H:i:s");
                        $key = implode(',', $arr);
                        $sql = "INSERT  INTO keyword ({$key}) values ('{$row['start']}', '{$row['start_time']}', '{$row['end']}', '{$row['end_time']}', '{$ret['frase']}', '{$ret['keyword']}', '{$date}', '{$date}', '{$row['task_uuid']}', '{$row['channel']}')";
                        //var_dump($sql);
                        mysqli_query($db, $sql);
                        $sql = "UPDATE task SET status = 2 WHERE task_uuid = '{$row['task_uuid']}'";
                        mysqli_query($db, $sql);
                    }
                } else {
                    $sql = "UPDATE task SET status = 0 WHERE task_uuid = '{$row['task_uuid']}'";
                    mysqli_query($db, $sql);
                }
            }
            mysqli_free_result($result);
        }

        mysqli_close($db);
    }

    public static function setAudioInfoPro()
    {
        ini_set('date.timezone','PRC');
        $timeStamp = strtotime(date('Y-m-d H:i',time()));
        for($i=0; $i<30; $i++)
        {
            $ppid = posix_getpid();
            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new Exception('fork error!');
            } elseif ($pid > 0) {
                cli_set_process_title("php-process changeapp I am master,my pid is {$ppid}.");
                //sleep(30);
            } else {
                $key = floor($i/8);
                //$startTimeStamp = strtotime(date('Y-m-d',time()));
                //$startTimeStamp = strtotime("-1 day");
                $cpid = posix_getpid();
                cli_set_process_title("php-process changeapp I am work,my pid is {$cpid}.");
                $endTime = $timeStamp - $i * 10;
                $startTime = $timeStamp - ($i+1) * 10;
                //var_dump($key);
                //var_dump(self::$appkeyMap[$key]);
                self::workInfoNew(self::$appkeyMap[$key][0], self::$appkeyMap[$key][1], self::$appkeyMap[$key][2], $startTime, $endTime);
                //echo date("Y-m-d H:i:s", $startTime)."\n";
                //echo date("Y-m-d H:i:s", $endTime)."\n";
                //sleep(10);
                exit;
            }
        }

        while (pcntl_waitpid(0, $status) != -1) {
            $status = pcntl_wexitstatus($status);
            echo "Work $status completed\n";
        }
    }

}
