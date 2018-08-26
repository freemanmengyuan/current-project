<?php
include "Tool.php";
include "../../conf/db.conf.php";
include "../../conf/constant.conf.php";

/**
 * database connect
 *
 */
class DbInfo
{

	//获取redis连接
	public static function getAllRedis()
	{
		$flag = Db::$runtime;
		if($flag != "online")
			$flag = "test";
		$redis=new Redis();
		$info = Db::$allRedis[$flag];
		$redis->connect($info['host'],$info['port']);
		$redis->auth($info['auth']);
		$redis->select($info['index']);
		return $redis;
	}

	//获取redis连接
	public static function getNowRedis()
	{
		$flag = Db::$runtime;
		if($flag != "online")
			$flag = "test";
		$redis=new Redis();
		$info = Db::$nowRedis[$flag];
		$redis->connect($info['host'],$info['port']);
		$redis->auth($info['auth']);
		$redis->select($info['index']);
		return $redis;
	}

	//获取mysql连接
	public static function getConnect()
	{
		$flag = Db::$runtime;
		if($flag != "online")
			$flag = "test";
		$db = Db::$gpbzx[$flag];
		$con=mysqli_connect($db['host'],$db['username'],
			$db['password'],$db['database'],$db['port']);
		// Check connection
		if (!$con)
		{
			Tool::log_print("Error","Failed to connect to MySQL: " . mysqli_connect_error());
			return null;
		}
		mysqli_query($con,'set names utf8');
		return $con;
	}

	public static function insertIncrToRedis($redis, $key, $result)
	{
		$his = $redis->hget($key, date("Y"));
		if(!empty($his))
		{
			$hisArr = json_decode($his,true);
		}
		else
		{
			$hisArr = [];
		}
		$hisArr[date("Ymd")] = $result;
		$redis->hSet($key, date("Y"), json_encode($hisArr));
	}

}
