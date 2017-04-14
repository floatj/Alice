<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Exception;      //wtf... = =
class TestAlive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'TestAlive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test if a service is Alive or not';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //數量統計
        $success_count = 0;
        $fail_ccount = 0;
        $total_count = 0;

        //執行
        $now = date("Y-m-d H:i:s");

        echo "[$now] 開始執行 TestAlive，讀取 service list\n";

        //讀取服務清單
        $lines = file('./storage/service.txt');

        $total_count = count($lines);

        //迴圈執行 fsockopen
        foreach ($lines as $line_num => $line) {


            $parm = explode(',', $line);

            //去除換行字元
            $parm[3] = str_replace(PHP_EOL, '', $parm[3]);

            //$parm[0]  :   service ip or domain
            //$parm[1]  :   tcp or udp
            //$parm[2]  :   port
            //$parm[3]  :   descriptions

            echo "正在測試連線至服務 [".$parm[3]."] ...\n";
            try{
                $fp = fsockopen($parm[1]."://".$parm[0], $parm[2], $errno, $errstr, 10);
            }catch (Exception $e) {
                echo 'fsockopen 失敗，發現例外錯誤： ',  $e->getMessage(), "\n";

                //連線失敗
                $fail_ccount ++;
                echo "服務 [".$parm[3]."] 測試連線 Port [".$parm[2]."] 失敗，錯誤原因如下: \n";
                echo "$errstr ($errno)\n";

                //直接跳到下一個服務繼續測試
                continue;
            }


            if (!$fp) {
                //連線失敗
                $fail_ccount ++;
                echo "服務 [".$parm[3]."] 測試連線 Port [".$parm[2]."] 失敗，錯誤原因如下: \n";
                echo "$errstr ($errno)\n";
            } else {
                //連線成功
                $success_count ++;

                /*
                $out = "GET / HTTP/1.1\r\n";
                $out .= "Host: www.test-alive.com\r\n";
                $out .= "Connection: Close\r\n\r\n";
                fwrite($fp, $out);
                while (!feof($fp)) {
                    echo fgets($fp, 128);
                }
                */
                echo "服務 [".$parm[3]."] 測試連線 Port [".$parm[2]."] 成功 <3\n";
                fclose($fp);
            }

        }

        $now = date("Y-m-d H:i:s");
        echo "[$now] 執行完畢！\n";
        echo "統計：\n";
        echo "服務數量：$total_count\n";
        echo "連線成功：$success_count\n";
        echo "連線失敗：$fail_ccount\n";
        echo "健康比率：". round($success_count / $total_count * 100, 2)." %\n";
    }
}
