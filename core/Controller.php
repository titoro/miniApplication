<?php

/* コントローラクラス */
abstract class Controller{
    protected $controller_name;
    protected $action_name;
    protected $application;
    protected $request;
    protected $response;
    protected $session;
    protected $db_manager;
    
    public function __construct($application) {
        //
        $this->controller_name = strtolower(substr(get_class($this), 0, -10));
        
        $this->application = $application;
        $this->request = $application->getRequest();
        $this->response = $application->getResponse();
        $this->session = $application->getSession();
        $this->db_manager = $application->getDbManager();
    }
    
     /**
     * ログインが必要なアクションの判定　run()メソッド内で行う
     */
    
    //ログインが必要なアクションを格納
    //TRUEを指定するとすべてのアクションがログイン必要と判断
    protected $auth_actions = array();
    
    //Applicationクラスから呼び出される
    //アクションのメソッド名は　アクション名＋Action()
    public function run($action, $params = array()){
        $this->action_name = $action;
        
        //アクションメソッド名を格納
        $action_method = $action. 'Action';
        //var_dump($action_method);
        //メソッドの存在チェック
        if(!method_exists($this, $action_method)){
            //クラスメソッドが存在しない場合、404エラー画面へ
            //var_dump($this);
            //var_dump($action_method);
            $this->forward404();            //後で実装
        }
        
        //ログインが必要なアクションかつ、未ログインの場合
        //ログイン画面へ遷移
        if($this->needsAuthentication($action) && !$this->session->isAuthenticated()){
            //例外を発生させる
            throw new UnauthorizedActionException();  
        }
        
        
        //可変変数でアクションメソッドの呼び出し
        //引数は$param
        //ルーティングパラメータを指定
        $content = $this->$action_method($params);
        
        //実行結果（コンテンツ）を返す
        return $content;
    }
    
    //ログインが必要なアクションか判定
    protected function needsAuthentication($action){
        //TRUEが指定されていればログイン必須
        //ログイン必須のアクションに指定したアクションが含まれているか判定
        if($this->auth_actions === TRUE || (in_array($action, $this->auth_actions))){
            return true;
        }
        return false;
    }

    //ビュークラスの読み込み処理をラッピング
    //第１引数　
    //第２引数　
    //第３引数　
    protected function render($variables = array(), $template = null, $layout = 'layout'){
        //連想配列でRequstオブジェクト、ベースURL、Sessionオブジェクトを格納
        $defaults = array(
                'request' => $this->request,
                'base_url' => $this->request->getBaseUrl(),
                'session' => $this->session,
        );
        
        //Viewクラスインスタンスの生成
        //ビューファイルへのディレクトリパスと
        //Requestオブジェクト、ベースURL、Sessonオブジェクトを引数に生成
        $view = new View($this->application->getViewDir(), $defaults);
        
        //テンプレートの指定チェック
        if(is_null($template)){
          //指定がない場合、アクション名をテンプレートファイル名に設定
          $template = $this->action_name;  
        }
        
        //コントローラ名＋テンプレートファイル名でパスを設定
        $path = $this->controller_name. '/'. $template;
        
        //renderメソッドの実行
        //ビューファイルの読み込み
        return $view->render($path, $variables, $layout);
        
    }
    
    
    //404画面へリダイレクト
    protected function forward404(){
        throw new HttpNotFoundException('Forwarded 404 page from '.
                $this->controller_name. '/'. $this->action_name);
    }
    
    //任意のURLへリダイレクト
    //Responseオブジェクトにリダイレクトする
    //引数　
    protected function redirect($url){
        //
        if(!preg_match('#https?://#', $url)){
          //プロトコルの判定（https or http)
          $protocol = $this->request->isSsl() ? 'https:///' :  'http://'; 
          //ホスト部分の取得
          $host = $this->request->getHost();
          //ベールURLの取得
          $base_url = $this->request->getBaseUrl();
          
          //絶対URLを生成
          $url = $protocol. $host. $base_url. $url;
        }
        
        //HTTPステータスコード 302 リダイレクト先のファイル発見
        $this->response->setStatusCode(302, 'Found');
        //リダイレクト先のURLを設定
        $this->response->setHttpHeader('Location', $url);
    }
    
    /*CSRF対策
     * generateCartToken()メソッド
     * checkCsrfToken()メソッド
     */
    
    
    //ワンタイムトークンの生成（フォーム毎に生成）
    //セッションに格納後トークンを返す
    //引数　フォーム名
    protected function generateCartToken($form_name){
        $key = 'csrf_tokens/' . $form_name;
        
        $tokens = $this->session->get($key, array());
        
        //同一アクションを複数画面から行った時に対応する
        //保持しているトークンが10個を超えているかチェック
        if(count($tokens) >= 10){
            //array_shift関数で古いトークンから一つ削除
            array_shift($tokens);
        }
        //トークンの生成
        //sha1ハッシュを用いて作成
        $token = sha1($form_name . session_id() . microtime());
        $tokens[] = $token;
        
        //セッションにハッシュ値を設定
        $this->session->set($key, $tokens);
        
        //トークンを返す
        return $token;
    }
    
    //リクエストされてきたトークンとセッションに格納されたトークンを比較
    protected function checkCsrfToken($form_name, $token){
        $key = 'csrf_tokens/' . $form_name;
        //セッションからトークンの取得
        $tokens = $this->session->get($key, array());
        
        //var_dump($tokens);
        
        //保持しているトークンがリクエストに含まれているかチェック
        //$pos トークンのキーを取得、格納
        if(false !== ($pos = array_search($token, $tokens,true))){
            //トークンが一致した（見つかった場合）
            unset($tokens[$pos]);                    //一度使用したトークンは削除
            $this->session->set($key, $tokens);     //セッションにトークンを設定
            
            //見つかった場合、TRUE
            return true;
        }
        //見つからなかった場合、FALSE
        return false;
    }

}


?>
