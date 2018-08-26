<?php
/**
 * Created by PhpStorm.
 * User: mengyuan
 * Date: 18/04/21
 * Time: 下午12:02
 */
require_once 'baiduAip/AipSpeech.php';
require_once 'baiduAip/AipNlp.php';

Class Convert
{
    static $posArr = array('f','s','t','v', 'r','q','m','vd', 'vn', 'a', 'ad', 'an', 'd', 'p', 'c', 'u', 'xc', 'w');

    public static function setAudio($appId, $apiKey, $apiSecret, $file, $type)
    {
        $result = array();

        $client = new AipSpeech($appId, $apiKey, $apiSecret);
        $result = $client->asr(file_get_contents($file), $type, 16000, array(
            'dev_pid' => '1536',
        ));
        if(empty($result))
        {
            Tool::log_print('error', 'change text fail ');
            return $result;
        }
        if($result['err_msg'] != 'success.')
        {
            Tool::log_print('error', json_encode($result));
        }

        return $result;
    }

    public static function setPpl($appId, $apiKey, $apiSecret, $content)
    {
        $result = [
            'keyword' => '',
            'frase' => ''
        ];

        $nlp = new AipNlp($appId, $apiKey, $apiSecret);
        $arrLexer = $nlp->lexer($content);
        if(empty($arrLexer))
        {
            Tool::log_print('error', 'ppl text fail');
            return $result;
        }
        //Tool::log_print('error', json_encode($arrLexer));
        if($arrLexer['status'] != 0)
        {
            Tool::log_print('error', json_encode($arrLexer));
            return $result;
        }
        foreach ($arrLexer['items'] as $item)
        {
            if(in_array($item['pos'], static::$posArr))
            {
                continue;
            }
            $result['keyword'] .= $item['item'].'|';
        }
        $result['frase'] = $arrLexer['text'];

        return $result;
    }


}
