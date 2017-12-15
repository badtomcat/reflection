<?php

/**
 * @author: awei.tian
 * @date: 2013-11-9
 * @function:
 */
// show table status from amzphp where name='ebank';
// +-------+--------+---------+------------+------+----------------+-------------+-----------------+--------------+-----------+----------------+---------------------+---------------------+------------+-----------------+----------+----------------+----------+
// | Name | Engine | Version | Row_format | Rows | Avg_row_length | Data_length |Max_data_length | Index_length | Data_free | Auto_increment | Create_time | Update_time | Check_time | Collation | Checksum | Create_options | Comment |
// +-------+--------+---------+------------+------+----------------+-------------+-----------------+--------------+-----------+----------------+---------------------+---------------------+------------+-----------------+----------+----------------+----------+
// | ebank | MyISAM | 10 | Dynamic | 3 | 1586 | 4760 |281474976710655 | 2048 | 0 | 4 | 2013-09-14 14:37:58 | 2013-09-14 14:44:19 | NULL | utf8_unicode_ci | NULL | | 电子银行 |
// +-------+--------+---------+------------+------+----------------+-------------+-----------------+--------------+-----------+----------------+---------------------+---------------------+------------+-----------------+----------+----------------+----------+

// SHOW COLUMNS FROM device_info
// +-------+-------------+------+-----+---------+----------------+
// | Field | Type | Null | Key | Default | Extra |
// +-------+-------------+------+-----+---------+----------------+
// | sid | int(11) | NO | PRI | NULL | auto_increment |
// | vv | varchar(50) | YES | | NULL | |
// +-------+-------------+------+-----+---------+----------------+

// Field Type Collation Null Key Default Extra Privileges Comment
// ------ ---------------- --------------- ------ ------ ------- ------ ------------------------------- ---------
// pk1 int(10) unsigned (NULL) NO PRI (NULL) select,insert,update,references
// pk2 int(10) unsigned (NULL) NO PRI (NULL) select,insert,update,references
// data varchar(10) utf8_general_ci YES (NULL) select,insert,update,references

// key_descriptions的结构为
// field => pk1 int(10) unsigned (NULL) NO PRI (NULL) select,insert,update,references
namespace Badtomcat\Db;

use \Badtomcat\Db\Connection\MysqlPdoConn;
use \Badtomcat\Cache\ICache;

class MysqlTableReflection implements ITableReflection
{
    /**
     *
     * @var MysqlPdoConn
     */
    public $connection;
    /**
     *
     * @var ICache
     */
    public $cache;
    /**
     *
     * @var string
     */
    private $tabname;
    /**
     * 初始值 为[],初始化以后为[
     *    tablename => []
     * ]
     *
     * @var array
     */
    private static $tab_descriptions = [];
    /**
     * 初始值 为[],初始化以后为[
     *    tablename => []
     * ]
     *
     * @var array
     */
    private static $col_descriptions = [];

    public function __construct($tabname, MysqlPdoConn $connection, ICache $cache = null)
    {
        $this->connection = $connection;
        $this->cache = $cache;
        $this->setTableName($tabname);
    }

    public function setTableName($name)
    {
        $this->tabname = $name;
        return $this;
    }

    public function getTableName()
    {
        return $this->tabname;
    }

    public function cacheKeyKeyDesc()
    {
        return 'Tian.MysqlTableReflection.key_descriptions.' . $this->tabname;
    }

    public function cacheKeyDesc()
    {
        return 'Tian.MysqlTableReflection.descriptions.' . $this->tabname;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getPk()
    {
        $this->initTableColDecription();
        $ret = [];
        foreach (self::$col_descriptions [$this->tabname] as $val) {
            if ($val ["Key"] == "PRI")
                $ret [] = $val ["Field"];
        }
        return $ret;
    }

    /**
     * (non-PHPdoc)
     *
     * @see ITableInfo::getColumnNames()
     * @throws \Exception
     */
    public function getColumnNames()
    {
        $this->initTableColDecription();
        return array_keys(self::$col_descriptions [$this->tabname]);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getEngineType()
    {
        $this->initTableDecription();
        return self::$tab_descriptions [$this->tabname] ["Engine"];
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getTableComment()
    {
        $this->initTableDecription();
        return self::$tab_descriptions [$this->tabname] ["Comment"];
    }

    // filed start

    /**
     * 返回像这样set,enum,binary,int
     * @param $field
     * @return mixed
     * @throws \Exception
     */
    public function getType($field)
    {
        $this->initTableColDecription();
        $ret = $this->_split_typelen(self::$col_descriptions [$this->tabname] [$field] ["Type"]);
        return $ret ["type"];
    }

    /**
     * @param $field
     * @return mixed
     * @throws \Exception
     */
    public function getLen($field)
    {
        $this->initTableColDecription();
        $ret = $this->_split_typelen(self::$col_descriptions [$this->tabname] [$field] ["Type"]);
        return $ret ["len"];
    }

    /**
     * @param $field
     * @return bool
     * @throws \Exception
     */
    public function isUnsiged($field)
    {
        $this->initTableColDecription();
        $ret = $this->_split_typelen(self::$col_descriptions [$this->tabname] [$field] ["Type"]);
        return $ret ["unsiged"] === true;
    }

    /**
     * @param $field
     * @return bool
     * @throws \Exception
     */
    public function isNullField($field)
    {
        $this->initTableColDecription();
        return self::$col_descriptions [$this->tabname] [$field] ["Null"] === 'YES';
    }

    public function getDefault($field)
    {
        $this->initTableColDecription();
        return self::$col_descriptions [$this->tabname] [$field] ["Default"];
    }

    /**
     * @param $field
     * @return mixed
     * @throws \Exception
     */
    public function getComment($field)
    {
        $this->initTableColDecription();
        return self::$col_descriptions [$this->tabname] [$field] ["Comment"];
    }

    /**
     * @param $field
     * @return bool
     * @throws \Exception
     */
    public function isPk($field)
    {
        $this->initTableColDecription();
        return self::$col_descriptions [$this->tabname] [$field] ["Key"] === 'PRI';
    }

    /**
     * @param $field
     * @return bool
     * @throws \Exception
     */
    public function isAutoIncrement($field)
    {
        $this->initTableColDecription();
        return self::$col_descriptions [$this->tabname] [$field] ["Extra"] === 'auto_increment';
    }

    /**
     * @param $field
     * @return bool
     * @throws \Exception
     */
    public function isUnique($field)
    {
        $this->initTableColDecription();
        return self::$col_descriptions [$this->tabname] [$field] ["Key"] === 'UNI';
    }

    private function _split_typelen($t)
    {
        if (preg_match("/^[a-z]+$/", $t)) {
            return array(
                'type' => $t,
                'len' => null,
                'unsiged' => null
            );
        } else if (preg_match("/^([a-z]+)\(([^\)]+)\)$/", $t, $matches)) {
            return array(
                'type' => $matches [1],
                'len' => str_replace("'", "", $matches [2]),
                'unsiged' => null
            );
        } else if (preg_match("/^([a-z]+)\(([0-9]+)\) unsigned$/", $t, $matches)) {
            return array(
                'type' => $matches [1],
                'len' => $matches [2],
                'unsiged' => true
            );
        } else {
            return array(
                'type' => null,
                'len' => null,
                'unsiged' => null
            );
        }
    }

    // field end

    /**
     * @throws \Exception
     */
    protected function initTableDecription()
    {
        if (isset (self::$tab_descriptions [$this->tabname]) && is_array(self::$tab_descriptions [$this->tabname])) {
            return;
        }
        if (!is_null($this->cache)) {
            $ret = $this->cache->get($this->cacheKeyDesc());
            if (is_array($ret)) {
                self::$tab_descriptions [$this->tabname] = $ret;
                return;
            }
        }
        $result = $this->connection->fetch("show table status from `" . $this->connection->getDbName() . "` where name=:tablename", [
            "tablename" => $this->tabname
        ]);
        self::$tab_descriptions [$this->tabname] = $result;
        if (!is_null($this->cache)) {
            $this->cache->set($this->cacheKeyDesc(), $result, 0);
        }
        return;
    }

    /**
     * @throws \Exception
     */
    protected function initTableColDecription()
    {
        if (isset (self::$col_descriptions [$this->tabname]) && is_array(self::$col_descriptions [$this->tabname])) {
            return;
        }
        if (!is_null($this->cache)) {
            $ret = $this->cache->get($this->cacheKeyKeyDesc());
            if (is_array($ret)) {
                self::$col_descriptions [$this->tabname] = $ret;
                return;
            }
        }
        $result = $this->connection->fetchAll("SHOW FULL COLUMNS FROM `$this->tabname`");
        if (count($result) == 0) {
            self::$col_descriptions [$this->tabname] = [];
            if (!is_null($this->cache)) {
                $this->cache->set($this->cacheKeyKeyDesc(), [], 0);
            }
            return;
        }

        self::$col_descriptions [$this->tabname] = array_combine(array_column($result, 'Field'), $result);
        if (!is_null($this->cache)) {
            $this->cache->set($this->cacheKeyKeyDesc(), self::$col_descriptions [$this->tabname], 0);
        }
    }

}