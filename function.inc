<?php
// 
// TODO: capture logs and session_debug messages
function debug($string){
    debug_print_backtrace();
}

function debugc($string){
    //var_dump($string);
    if(is_array($string)){
        $string = implode(',',$string);
    }
    echo "<script>console.log('".htmlentities($string, ENT_QUOTES, "UTF-8")."');</script>";
}

function lg($file){
    openlog("myScriptLog", LOG_PID | LOG_PERROR, LOG_LOCAL0);
    syslog(LOG_WARNING, "Unauthorized client: $access {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");
    closelog();
}

function q($query){
    $output = array();
    $db = new SQLite3("db/test.db"); //NOTE: fix this for performance + close()
    $regex="/^insert|^update/i";
    if(preg_match($regex,$query,$out)){
        $q = $db->exec("$query",);
        debugc("exec . - ".htmlentities($query, ENT_QUOTES));
        if($db->lastErrorCode()){
            $_SESSION["query_error"]="error:".$db->lastErrorCode.":".$db->lastErrorMsg();
        }
    }else{
        $q = $db->query("$query");
        debugc("query. - ".htmlentities($query, ENT_QUOTES));
        if($db->lastErrorCode()){
            $_SESSION["query_error"]="error:".$db->lastErrorCode.":".$db->lastErrorMsg();
            lg("log");
        }else{
            while($row = $q->fetchArray(SQLITE3_ASSOC)){
                $output[]=$row;
            }
        }
    }
    $db->close();
    return $output;
}
/*
// SQL
class q{
    public $db = new SQLite3('db/test.db');
    public $dbfile;
    public $console;
    public $query;
    private $output;

    public function query($query){
       $output = $db->query($this->query); 
       if($output){
           debug($db->lastErrorMsg());
       }else{
        return $output
       }
    }
}

class Q
{
    // Properties
    public $var = 'a default value';
    public $name;
    public $color;

    // Methods
    public function display(){
        echo $this->var;
    }

    function set_name($name){
        $this->name = $name;
    }

    function get_name(){
        return $this->name;
    }

}


//
// $a = new Q();
// $b = new Q();
// $a->set_name('Apple');
// $b->set_name('Banana');
// echo $a->get_name();

 */
?>
