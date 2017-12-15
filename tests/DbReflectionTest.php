<?php
class DbReflectionTest extends PHPUnit_Framework_TestCase {
	private $con;
	public function setUp()
	{
		//echo "setup";

		$this->con = new \Badtomcat\Db\Connection\MysqlPdoConn([
				'host' => '127.0.0.1',
				'port' => 3306,
				'database' => 'garri',
				'user' => 'root',
				'password' => 'root',
				'charset' => 'utf8'
		]);
		$this->con->exec('
			CREATE TABLE `gg` (
				`pk1` int(10) unsigned NOT NULL,
				`pk2` int(10) unsigned NOT NULL,
				`data` varchar(10) DEFAULT NULL,
				PRIMARY KEY (`pk1`,`pk2`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			
			CREATE TABLE `schedules` (
			  `schedeles_id` int(11) NOT NULL AUTO_INCREMENT,
			  `schedeles_date` date NOT NULL,
			  `schedeles_doc` int(11) NOT NULL,
			  `schedeles_status` int(11) NOT NULL,
			  PRIMARY KEY (`schedeles_id`),
			  KEY `schedeles_doc` (`schedeles_doc`)
			) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8
		');

	}
	
	public function tearDown()
	{
		//echo "tear down";
		$this->con->exec("
			DROP TABLE `gg`;
			DROP TABLE `schedules`;
		");
	}
	public function testMysqlReflection() {
// 		$cache = new \Tian\Memcache([
// 				'host'     => '192.168.33.10',
// 				'port'     => 11111,
// 		]);
		//echo "test...";
		$info = new \Badtomcat\Db\MySqlDbReflection($this->con,null);
		$this->assertTrue($info->tableExists('gg'));
		$this->assertTrue($info->tableExists('schedules'));
		$this->assertTrue(!$info->tableExists('lol'));
	}
}


