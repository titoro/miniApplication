<?php

/*
 * 接続情報を管理する
 */

class DbManager{
    //PDOクラスのインスタンスを配列で格納
    protected $connections = array();
    protected $repository_connection_map = array();
    protected $repositories = array();
    
    //接続を行う
    public function connect($name, $params){
        //$name     $connection配列のキー
        //$params   PDOクラスのコンストラクタに渡す情報
        
        //$params配列から値を取り出す時にキーが存在するかのチェックを行わないため
        //array_mergeで配列を作成
        $params = array_merge(array(
            'dsn' => null,          
            'user' => '',           
            'password' => '',       
            'options' => array(),   
        ),$params);
        
        //接続情報を保持
        $con = new PDO(
                $params['dsn'],
                $params['user'],
                $params['password'],
                $params['options']
                
       );
       
        //PDOでエラー発生時、例外を発生させる
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //接続を行うための情報を格納
        $this->connections[$name] = $con;
        
    }
    
     /**
     * コネクションを取得
     *
     * @string $name
     * @return PDO
     */
    public function getConnection($name = null){
        //接続のためのキー（データベース名など）が存在するか
        if(is_null($name)){
            //存在した場合、そのデータベースの接続情報を取得
            return current($this->connections);
        }
        //存在しなかった場合、初めに登録されているデータべーつへの接続情報を取得
        return $this->connections[$name];
    }
    
    /*DBRepositoryクラスとの関連付け（管理）*/
    //テーブル毎のRepositoryクラスと接続名の対応を格納
    //protected $repository_connection_map = array();
    
    //リポジトリの接続の設定を行う
    public function setRespositoryConnectionMap($repository_name, $name){
        $this->repository_connection_map[$repository_name] = $name;
    }
    
    //リポジトリクラスの対応を考慮して、接続情報を取得する
    public function getConnectionForRepository($repository_name){
        //リポジトリクラスの対応が設定されているか？
        if(isset($this->repository_connection_map[$repository_name])){
          //設定されているならば、getConnectionに接続する情報を指定、
          $name = $this->repository_connection_map[$repository_name];
          $con = $this->getConnection($name);
      } else{
          //設定されていなければ、最初に指定したもの取得する
          $con = $this->getConnection();
      }
      return $con;
    }
    
    /*  Repositoryクラスの管理  */
    //protected $repositories = array();
    
    //リポジトリクラスのインスタンスを生成
    public function get($repository_name){
        //var_dump($repository_name);
        if(!isset($this->repositories[$repository_name])){
            /*
            $repository_class = $repository_name. 'Repository';
            //var_dump($repository_class);
            //var_dump($repository_name);
            //コネクションの取得
            $con = $this->getConnectionForRepository($repository_name);
            
            //変数をクラス名に指定して動的にクラスを生成
            $repository = new $repository_class($con);
            //作成したRepositoryクラスのインスタンスを格納
            $this->repositories['repository_name'] = $repository;
            */
             
            /* 完成からのコピー　一時的にこちらを使用している */
            $repository_class = $repository_name . 'Repository';
            $con = $this->getConnectionForRepository($repository_name);

            $repository = new $repository_class($con);

            $this->repositories[$repository_name] = $repository;
        }
        //Repositoryクラスのインスタンスを返す
        return $this->repositories[$repository_name];
    }
    
    /*  データベースとの接続を解放する処理 */
    //インスタンスが破棄されたときに自動的に呼び出される
    public function __destruct() {
        //リポジトリの参照を先に破棄
        foreach ($this->repositories as $repository){
            unset($repository);
        }
        //接続を破棄
        foreach ($this->connections as $con){
            unset($con);
        }
    }
}

?>

