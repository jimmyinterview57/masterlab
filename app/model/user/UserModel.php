<?php

namespace main\app\model\user;

use main\app\model\DbModel;


/**
 *
 * User model
 * @author Sven
 */
class UserModel extends DbModel
{
    public $prefix = 'user_';

    public $table = 'main';

    public $fields = ' * ';

    public $primaryKey = 'uid';


    const  DATA_KEY = 'user/';

    const  REG_RETURN_CODE_OK = 1;
    const  REG_RETURN_CODE_EXIST = 2;
    const  REG_RETURN_CODE_ERROR = 3;

    /**
     * 登录成功
     */
    const  LOGIN_CODE_OK = 1;

    /**
     * 已经登录过了
     */
    const  LOGIN_CODE_EXIST = 2;


    /**
     * 登录失败
     */
    const  LOGIN_CODE_ERROR = 3;

    /**
     * 登录需要验证码
     */
    const  LOGIN_REQUIRE_VERIFY_CODE = 4;

    /**
     * 验证码错误
     */
    const  LOGIN_VERIFY_CODE_ERROR = 5;

    const  STATUS_PENDING_APPROVAL = 0;
    const  STATUS_NORMAL = 1;
    const  STATUS_DISABLED = 2;
    static public $status = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLED => '禁用'
    ];


    public $uid = '';
    public $master_id = null;
    public $is_master = null;


    /**
     * 用于实现单例模式
     * @var self
     */
    protected static $instance;


    /**
     * 创建一个自身的单例对象
     * @param array $dbConfig
     * @param bool $persistent
     * @throws PDOException
     * @return self
     */
    public static function getInstance($uid = '', $persistent = false)
    {
        $index = $uid . strval(intval($persistent));
        if (!isset(self::$instance[$index]) || !is_object(self::$instance[$index])) {
            self::$instance[$index] = new self($uid, $persistent);
        }
        return self::$instance[$index];
    }

    public function __construct($uid = '', $persistent = false)
    {
        parent::__construct($uid, $persistent);
        $this->uid = $uid;
    }


    /**
     * 取得一个用户的基本信息
     * @return array
     */
    public function getUser()
    {
        $fields = '*';
        $conditions = array('uid' => $this->uid);
        $finally = $this->getRow($fields, $conditions);
        return $finally;
    }

    /**
     * @param $uid
     * @return array
     */
    public function getByUid($uid)
    {
        $fields = '*';
        $where = array('uid' => $uid);
        $finally = $this->getRow($fields, $where);
        return $finally;
    }

    public function getUsers()
    {
        $sql = "select * from " . $this->getTable() . " limit 10";
        $rows = $this->db->getRows($sql);
        return $rows;
    }

    public function getByOpenid($openid)
    {
        $fields = "*,{$this->primaryKey} as k";
        //$where	=	" Where `openid`='$openid'   ";
        $where = ['openid' => trim($openid)];
        $user = $this->getRow($fields, $where);
        return $user;
    }

    public function getByPhone($phone)
    {
        $fields = "*,{$this->primaryKey} as k";
        $where = ['phone' => trim($phone)];
        $user = $this->getRow($fields, $where);
        return $user;
    }

    public function getByEmail($email)
    {
        $fields = "*,{$this->primaryKey} as k";
        $where = ['email' => trim($email)];
        $user = $this->getRow($fields, $where);
        return $user;
    }

    public function getUsersByIds($uids)
    {
        if (empty($uids)) {
            return [];
        }
        $params['uids'] = implode(',', $uids);
        $sql = "select * from " . $this->getTable() . " where uid in(:uids)";
        $rows = $this->db->getRows($sql, $params);
        return $rows;
    }

    public function getFieldByIds($field, $user_ids)
    {
        if (empty($user_ids)) {
            return [];
        }

        $params['user_ids'] = $user_ids = implode(',', $user_ids);
        $sql = "select uid,{$field}  from " . $this->getTable() . " where uid in({$user_ids})";
        $rows = $this->db->getRows($sql, $params);

        $ret = [];
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $ret[] = $row[$field];
            }
        }
        return $ret;
    }

    public function getByUsername($username)
    {
        $fields = "*,{$this->primaryKey} as k";
        $where = ['username' => trim($username)];
        $user = $this->getRow($fields, $where);
        return $user;
    }


    /**
     * 添加用户
     * @param array $userinfo 提交的用户信息
     * @return array
     */
    public function addUser($userinfo)
    {
        if (empty($userinfo)) {
            return array(self::REG_RETURN_CODE_ERROR, array());
        }
        $flag = $this->insert($userinfo);

        if ($flag) {
            $uid = $this->lastInsertId();
            $this->uid = $uid;
            $user = $this->getUser(true);
            return array(self::REG_RETURN_CODE_OK, $user);
        } else {
            return array(self::REG_RETURN_CODE_ERROR, []);
        }
    }

    /**
     * 更新用户的信息
     * @param $updateInfo
     * @param $uid
     * @return array
     */
    public function updateUserById($updateInfo, $uid)
    {
        if (empty($updateInfo)) {
            return [false, __CLASS__ . __METHOD__ . '参数$update_info不能为空'];
        }
        if (!is_array($updateInfo)) {
            return [false, __CLASS__ . __METHOD__ . '参数$update_info必须是数组'];
        }
        if (!$uid) {
            return [false, __CLASS__ . __METHOD__ . '参数$uid不能为空'];
        }
        // $key = self::DATA_KEY . 'uid/' . $uid;
        $where = ['uid' => $uid];
        $ret = $this->update($updateInfo, $where);
        return $ret;
    }

    /**
     * 更新一个用户的信息
     * @param $updateInfo
     * @return array
     */
    public function updateUser($updateInfo)
    {
        if (empty($updateInfo)) {
            return [false,'update info is empty'];
        }
        if (!is_array($updateInfo)) {
            return [false,'update info is not array'];
        }
        $uid = $this->uid;
        // $key = self::DATA_KEY . 'uid/' . $uid;
        $where = ['uid' => $uid];//"  where `uid`='$uid'";
        $ret = $this->update($updateInfo, $where);
        return $ret;
    }
}
