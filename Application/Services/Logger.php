<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services;

/**
 * @category MED CRM
 */
class Logger {		
			
	private static $log=[];	
	private static $longQueries=[];
	
	private static $dblog = null;
	private static $longquery = null;
	private static $systemlog = null;
	private static $dbtime= 0;
	private static $logshow = 1;

	public function __construct($dblog=0, $systemlog=0, $long_query_time=0)
	{
		
		if(self::$dblog == null){
			self::$dblog = $dblog;
		}
		if(self::$systemlog == null){
			self::$systemlog = $systemlog;
		}
		if(self::$longquery == null){
			self::$longquery = $long_query_time;
		}
		
	}
	
	public static function pushDb($sql, $time=0)
	{				
		if(self::$dblog){			
			self::$log[] = 'DB: '.sprintf("%.4F сек, ", $time).$sql;
			self::$dbtime = self::$dbtime + $time;
		}
		
		if(self::$longquery){
			$timesec = floatval(sprintf("%.4F", $time));
			if($timesec > self::$longquery){
				self::$longQueries[] = "LONG_QUERY: $timesec, $sql";
			}
		}
	}

	public static function pushSystem($message)
	{				
		if(self::$systemlog){			
			self::$log[] = "SYSTEM: $message";
		}
	}
	
	public static function push($message)
	{
		self::$log[] = "LOCAL: $message";
	}
	
	public static function getLog()
	{
		if(self::$logshow){
			return array_merge(self::$log, self::$longQueries);
		}
		return [];
	}
	
	public static function getDbTime()
	{
		return self::$dbtime;
	}
	
	public static function Off()
	{		
		self::$dblog = 0;				
		self::$systemlog = 0;		
		self::$longquery = 0;
		self::$logshow = 0;
	}
}
