<?php
class TableReflectionTest extends PHPUnit_Framework_TestCase {
	private $con;
	private $cache;
	public function setUp() {
		$this->con = new \Badtomcat\Db\Connection\MysqlPdoConn ( [
				'host' => '127.0.0.1',
				'port' => 3306,
				'database' => 'garri',
				'user' => 'root',
				'password' => 'root',
				'charset' => 'utf8' 
		] );
		$this->con->exec ( "
CREATE TABLE `gg` (
  `pk1` int(10) unsigned NOT NULL,
  `pk2` int(10) unsigned NOT NULL,
  `fint` int(10) unsigned DEFAULT '16',
  `data` varchar(10) DEFAULT NULL,
  `fenum` enum('aa','bb') DEFAULT NULL COMMENT 'comment_enum',
  `fset` set('a','bc') DEFAULT NULL,
  `notnullable` text NOT NULL,
  PRIMARY KEY (`pk1`,`pk2`),
  UNIQUE KEY `fint` (`fint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='gg_comment';
			
			CREATE TABLE `schedules` (
			  `schedeles_id` int(11) NOT NULL AUTO_INCREMENT,
			  `schedeles_date` date NOT NULL,
			  `schedeles_doc` int(11) NOT NULL,
			  `schedeles_status` int(11) NOT NULL,
			  PRIMARY KEY (`schedeles_id`),
			  KEY `schedeles_doc` (`schedeles_doc`)
			) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8
		" );
// 		$this->cache = new \Tian\Memcache ( [ 
// 				'host' => '192.168.33.10',
// 				'port' => 11111 
// 		] );
		$this->cache = null;
	}
	public function tearDown() {
		$this->con->exec ( "
			DROP TABLE `gg`;
			DROP TABLE `schedules`;
		" );
		if($this->cache){
			$this->cache->flush ();
		}
		
	}
	public function testPk() {
		$info = new \Badtomcat\Db\MysqlTableReflection ( 'gg', $this->con, $this->cache );
		$this->assertArraySubset ( [ 
				'pk1',
				'pk2' 
		], $info->getPk () );
	}
	public function testCol() {
		$info = new \Badtomcat\Db\MysqlTableReflection ( 'gg', $this->con, $this->cache );
		$this->assertArraySubset ( [
				'pk1',
				'pk2',
				'fint',
				'data',
				'fenum',
				'fset',
				'notnullable',
		], $info->getColumnNames() );
	}
	public function testComment() {
		$info = new \Badtomcat\Db\MysqlTableReflection ( 'gg', $this->con, $this->cache );
		$this->assertEquals( 'gg_comment', $info->getTableComment());
	}
	public function testTableType() {
		$info = new \Badtomcat\Db\MysqlTableReflection ( 'gg', $this->con, $this->cache );
		$this->assertEquals( 'InnoDB', $info->getEngineType() );
	}
	
	public function testType(){
		$info = new \Badtomcat\Db\MysqlTableReflection ( 'gg', $this->con, $this->cache );
		$this->assertEquals( 'enum', $info->getType('fenum'));
	}
	
	public function testLen(){
		$info = new \Badtomcat\Db\MysqlTableReflection ( 'gg', $this->con, $this->cache );
		$this->assertEquals( '10', $info->getLen('data'));
	}
	
	public function testisunsigned(){
		$info = new \Badtomcat\Db\MysqlTableReflection ( 'gg', $this->con, $this->cache );
		$this->assertTrue( $info->isUnsiged('fint'));
	}
	
	public function testisNullField(){
		$info = new \Badtomcat\Db\MysqlTableReflection ( 'gg', $this->con, $this->cache );
		$this->assertTrue( $info->isNullField('data'));
		$this->assertNotTrue($info->isNullField('notnullable'));
	}
	
	
	public function testgetDefault(){
		$info = new \Badtomcat\Db\MysqlTableReflection ( 'gg', $this->con, $this->cache );
		$this->assertEquals('16', $info->getDefault('fint'));
	}
	
	public function testisPk(){
		$info = new \Badtomcat\Db\MysqlTableReflection ( 'gg', $this->con, $this->cache );
		$this->assertTrue( $info->isPk('pk1'));
	}
	
	public function testisAutoIncrement(){
		$info = new \Badtomcat\Db\MysqlTableReflection ( 'schedules', $this->con, $this->cache );
		$this->assertTrue( $info->isAutoIncrement('schedeles_id'));
	}
}


