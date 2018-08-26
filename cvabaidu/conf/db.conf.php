<?php
/**
 * Created by PhpStorm.
 * User: mengyuan
 * Date: 17/12/4
 * Time: 下午4:06
 */

class Db
{
    public static $gpbzx = [
        'test' => [
            'host' => '183.131.54.53',
            'username' => 'root',
            'password' => 'Aadmin18003435512',
            'database' => 'yuqing',
            'charset' => 'utf8',
            'port' => '3306',
        ],
        'online' => [
            'host' => '10.174.92.249',
            'username' => 'root',
            'password' => 'MhxzKhl',
            'database' => 'gpbzx',
            'charset' => 'utf8',
            'port' => '3306',
        ],
    ];

    public static $nowRedis = [
        'test' => [
            'host' => '10.174.92.249',
            'port' => 6379,
            'auth' => 'MhxzKhl&gpb%.1112',
            'index' => 1,
        ],
        'online' => [
            'host' => '10.174.92.249',
            'port' => 6379,
            'auth' => 'MhxzKhl&gpb%.1112',
            'index' => 1,
        ]
    ];

    public static $allRedis = [
        'test' => [
            'host' => '10.25.92.228',
            'port' => 6379,
            'auth' => 'MhxzKhl&gpb%.1112',
            'index' => 5,
        ],
        'online' => [
            'host' => '10.25.92.228',
            'port' => 6379,
            'auth' => 'MhxzKhl&gpb%.1112',
            'index' => 2,
        ]
    ];

    public static $runtime = 'test';

    public static $wstockUrl = [
        'kline' => 'http://db2015.wstock.cn/wsDB_API2/kline.php?q_type=0&desc=0&r_type=0&u=hzhfzx&p=abc123&',
    ];

    public static $dongcaiUrl = [
        'fund' => 'http://nufm.dfcfw.com/EM_Finance2014NumericApplication/JS.aspx/JS.aspx?type=ct&st=(BalFlowMain)&ps=8000&p=1&sr=-1&token=894050c76af8597a853f5b408b759f5d&cmd=C._A&sty=DCFFITA&rt=50280086',
        'income' => 'http://datainterface.eastmoney.com/EM_DataCenter/JS.aspx?type=SR&sty=YJBB&st=13&sr=-1&ps=8000&p=1&stat=0&rt=50421435',
        'outfit' => 'http://datainterface.eastmoney.com//EM_DataCenter/js.aspx?type=SR&sty=GGSR&ps=10000&p=1&code={{code}}&rt=50421676',
    ];

    public static $heng = [
        'key' => '25bd258a-400d-4bf1-99f4-f34855a7fde4',
        'secret' => '8c26e017-f6ba-48fb-abf3-baeb5f0bbf4a',
        'host' => 'sandbox.hscloud.cn',
        'token' => 'https://sandbox.hscloud.cn/oauth2/oauth2/token',
        'income' => 'https://sandbox.hscloud.cn/oauth2/oauth2/token',
    ];

    public static $boardType = [
        'industry' => [
            'board' => 'http://nufm.dfcfw.com/EM_Finance2014NumericApplication/JS.aspx?type=CT&cmd=C._BKHY&sty=FPGBKI&st=c&sr=-1&p=1&ps=5000&cb=&token=7bc05d0d4c3c22ef9fca8c2a912d779c&v=0.',
            'stock' => 'http://nufm.dfcfw.com/EM_Finance2014NumericApplication/JS.aspx?type=CT&sty=FCOIATA&sortType=C&sortRule=-1&page=1&pageSize=2000&token=7bc05d0d4c3c22ef9fca8c2a912d779c&jsName=quote_123&cmd=C.',
            'fund' => 'http://nufm.dfcfw.com/EM_Finance2014NumericApplication/JS.aspx?cmd=C._BKHY&type=ct&st=(BalFlowMain)&sr=-1&p=1&ps=500&token=894050c76af8597a853f5b408b759f5d&sty=DCFFITABK&rt=50280083',
            ],
        'concept' => [
            'board' => 'http://nufm.dfcfw.com/EM_Finance2014NumericApplication/JS.aspx?type=CT&cmd=C._BKGN&sty=FPGBKI&st=c&sr=-1&p=1&ps=5000&cb=&token=7bc05d0d4c3c22ef9fca8c2a912d779c&v=0.',
            'stock' => 'http://nufm.dfcfw.com/EM_Finance2014NumericApplication/JS.aspx?type=CT&sty=FCOIATA&sortType=C&sortRule=-1&page=1&pageSize=2000&token=7bc05d0d4c3c22ef9fca8c2a912d779c&jsName=quote_123&cmd=C.',
            'fund' => 'http://nufm.dfcfw.com/EM_Finance2014NumericApplication/JS.aspx?cmd=C._BKGN&type=ct&st=(BalFlowMain)&sr=-1&p=1&ps=500&token=894050c76af8597a853f5b408b759f5d&sty=DCFFITABK&rt=50280074',
            ],
    ];

    public static $outfitUrl = [
        'outfit' => 'http://datainterface.eastmoney.com//EM_DataCenter/js.aspx?type=SR&sty=GGSR&ps=10000&p=1&code={{code}}&rt=50421676',
        'position' => 'http://data.eastmoney.com/zlsj/zlsj_list.aspx?type=ajax&st=2&sr=-1&p=1&ps=10000&stat=1&cmd=1&date={{date}}&rt=50432066',
    ];
}