<?php

class Session{
    //セッションがスタートしているかどうかを保持
    protected static $sessionStarted = false;
    //
    protected static $sessionIdRegenerated = false;
    
    public function __construct() {
        if(!self::$sessionStarted){
            //セッションがスタートしていなければスタート
            session_start();
            //
            self::$sessionStarted = true;
        }
    }
    
    //セッションの設定
    public function set($name, $value){
        $_SESSION[$name] = $value;
    }
    
    //セッションの取得
    public function get($name, $default = null){
        if(isset($_SESSION[$name])){
            return $_SESSION[$name];
        }
        return $default;
    }
    
    //指定したセッションを削除
    public function remove($name){
        unset($_SESSION[$name]);
    }
    
    //セッションを空にする
    public function clear(){
        $_SESSION = array();
    }
    
    //セッションIDを新しく発行するため
    //sesssion_regenerate_id()関数を実行
    public function regenarate($destory = true){
        if(!self::$sessionIdRegenerated){
            //$sessionIDRegenetedが存在しない場合
            //session_regenerate_id()関数を実行
            session_regenerate_id($destory);
            
            //
            self::$sessionIdRegenerated = true;
        }
    }
    
    //ユーザがログイン状態を制御する為のメソッド
    public function setAuthenticated($bool){
        //_authenticatedキーでログイン状態の制御を行う
        $this->set('_authenticated', (bool)$bool);
        //
        $this->regenarate();
    }
    
    //
    public function isAuthenticated(){
        //
        return $this->get('_authenticated', false);
    }
    
}


?>
