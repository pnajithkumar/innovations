<?php
require_once ('../config/db.php');

class DB extends PDO
{
    protected static $instances = [];
    public static function get()
    {
        $type = 'mysql';
        $host = MYDBHOST;
        $name = MYDBNAME;
        $user = MYDBUSER;
        $pass = MYDBPASS;
        
        $id = "$type.$host.$name.$user.$pass";
        
        if (isset(self::$instances[$id])) {
            return self::$instances[$id];
        }
        
        try {
            $instance = new self("$type:host=$host;dbname=$name;charset=utf8", $user, $pass);
            $instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instances[$id] = $instance;
            return $instance;
        } catch (PDOException $e) {
            echo "<pre>";
            echo "DB Connection Error";
            echo "</pre>";
        }
    }
}