<?php
/**
 * Created by 大师兄
 * 派系: 神秘剑派
 * 技能: zxc秒杀
 * Date: 2018/6/6
 * Time: 16:20
 * QQ:  997823131 
 */
/*
 * mongodb 数据库操作
 */
class Mgdb {
    protected $database    = '';
    protected $mo;

    /**
     * 构造方法
     */
    public function __construct() {
        $server = MONGODB_SERVER;
        $user = MONGODB_USER;
        $password = MONGODB_PASS;
        $port = MONGODB_PORT;
        $database = MONGODB_NAME;
        $mongo = $this->getInstance($server, $user, $password, $port);
        $this->database = $mongo->$database;
    }

    /**
     * 数据库单例方法
     * @param $server
     * @param $user
     * @param $password
     * @param $port
     * @return Mongo
     */
    public function getInstance($server, $user, $password, $port) {
        if (isset($this->mo)) {
            return $this->mo;
        } else {
            if (!empty($server)) {
                if (!empty($port)) {
                    if (!empty($user) && !empty($password)) {

                        $this->mo = new MongoClient("mongodb://{$user}:{$password}@{$server}:{$port}");
                    } else {
                        $this->mo = new MongoClient("mongodb://{$server}:{$port}");
                    }
                } else {
                    $this->mo = new MongoClient("mongodb://{$server}");

                }
            } else {
                $this->mo = new MongoClient();
            }
            return $this->mo;
        }
    }

    /**
     * 查询表中所有数据
     * @param $table
     * @param array $where
     * @param array $sort
     * @param string $limit
     * @param string $skip
     * @return array|int
     */
    public function getAll($table, $where = array(), $sort = array(), $limit = '', $skip = '') {
        if (!empty($where)) {
            $data = $this->database->$table->find($where);
        } else {
            $data = $this->database->$table->find();
        }

        if (!empty($sort)) {
            $data = $data->sort($sort);
        }

        if (!empty($limit)) {
            $data = $data->limit($limit);
        }

        if (!empty($skip)) {
            $data = $data->skip($skip);
        }

        $newData = array();
        while ($data->hasNext()) {
            $newData[] = $data->getNext();
        }
        if (count($newData) == 0) {
            return 0;
        }
        return $newData;
    }

    /**
     * 查询指定一条数据
     * @param $table
     * @param array $where
     * @return int
     */
    public function getOne($table, $where = array()) {
        if (!empty($where)) {
            $data = $this->database->$table->findOne($where);
        } else {
            $data = $this->database->$table->findOne();
        }
        return $data;
    }

    /**
     * 统计个数
     * @param $table
     * @param array $where
     * @return mixed
     */
    public function getCount($table, $where = array()) {
        if (!empty($where)) {
            $data = $this->database->$table->find($where)->count();
        } else {
            $data = $this->database->$table->find()->count();
        }
        return $data;
    }

    /**
     * 直接执行mongo命令
     * @param $sql
     * @return array
     */
    public function toExcute($sql) {
        $result = $this->database->execute($sql);
        return $result;
    }

    /**
     * 分组统计个数
     * @param $table
     * @param $where
     * @param $field
     */
    public function groupCount($table, $where, $field) {
        $cond = array(
            array(
                '$match' => $where,
            ),
            array(
                '$group' => array(
                    '_id' => '$' . $field,
                    'count' => array('$sum' => 1),
                ),
            ),
            array(
                '$sort' => array("count" => -1),
            ),
        );
        $this->database->$table->aggregate($cond);
    }

    /**
     * 删除数据
     * @param $table
     * @param $where
     * @return array|bool
     */
    public function toDelete($table, $where) {
        $re = $this->database->$table->remove($where);
        return $re;
    }

    /**
     * 插入数据
     * @param $table
     * @param $data
     * @return array|bool
     */
    public function toInsert($table, $data) {
        $re = $this->database->$table->insert($data);
        return $re;
    }

    /**
     * 更新数据
     * @param $table
     * @param $where
     * @param $data
     * @return bool
     */
    public function toUpdate($table, $where, $data,$upsert=false,$multiple=true) {
        $re = $this->database->$table->update($where, array('$set' => $data),array("upsert"=>$upsert,"multiple"=>$multiple));
        return $re;
    }

    /**
     * 获取唯一数据
     * @param $table
     * @param $key
     * @return array
     */
    public function distinctData($table, $key, $query = array()) {
        if (!empty($query)) {
            $where = array('distinct' => $table, 'key' => $key, 'query' => $query);
        } else {
            $where = array('distinct' => $table, 'key' => $key);
        }

        $data = $this->database->command($where);
        return $data['values'];
    }
    /*
     * 列出现有的数据库
     */
    public function get_db_list(){
        $data = $this->mo->listDBs();
        return $data;
    }
    /*
     * 列出某个数据库中的所有集合
     */
    public function get_table_list($db=''){
        if(!empty($db)){
            $db = $db;
        }else{
            $db = MONGODB_NAME;
        }
        $data = $this->mo->$db->getCollectionNames();
        return $data;
    }
}
?>