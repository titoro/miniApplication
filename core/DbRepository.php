<?php
/* データベースへの接続を行う抽象クラス  */

abstract class DbRepository{
    protected $con;
    
    //初期化
    //PDOクラスのインスタンスを受け取って設定
    public function __construct($con) {
        $this->setCorrection($con);
    }
    
    //PDOクラスのインスタンスを設定
    /**
     * コネクションを設定
     *
     * @param PDO $con
     */
    public function setCorrection($con){
        var_dump($con);
        $this->con = $con;
    }
    
    //プレースホルダを使ってSQL文を実行
    public function execute($sql, $params = array()){
        $stmt = $this->con->prepare($sql);
        var_dump($stmt);
        var_dump($params);
        $stmt->execute($params);
        
        return $stmt;
    }
    
    //実行結果（PDOStatmentクラスのインスタンス）からfetchを実行
    public function fetch($sql, $params = array()){
        return $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }
    
    //実行結果（PDOStatmentクラスのインスタンス）からfetch_Allを実行
    public function fetchAll($sql, $params = array()){
        return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }
}




?>
