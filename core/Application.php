<?php

/* フレームワークの中心となるクラス
 * 様々なクラスのオブジェクト、ディレクトリへのパスの管理
 * ルーティングの定義
 * コントローラの実行
 * レスポンスの送信
 *  */


abstract class Application{
    //変数の初期化
    protected $debug = false;
    protected $request;
    protected $response;
    protected $session;
    protected $db_manager;
   
    public function __construct($debug=false) {
        $this->setDebugMode($debug);    //
        $this->initialize();            //
        $this->configure();             //
    }
    
    protected function setDebugMode($debug){
        if($debug){
            $this->debug = true;
            //
            ini_set('display_errors', 1);
            //
            error_reporting(-1);
        }
    }
    
    //それぞれのクラスのインスタンス生成
    protected function initialize(){
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->db_manager = new DbManager();
        //ルーティング定義配列を渡してインスタンスを生成
        $this->router = new Router($this->registerRoutes());
    }
    
    //定義のみ
    //個別のアプリケーションで設定を行う
    protected function configure(){
        
    }
    
    //・・・の抽象メソッド
    abstract public function getRootDir();
    //・・・の抽象メソッド
    abstract protected function registerRoutes();
    
    public function isDebugMode(){
        return $this->debug;
    }
    
    public function getRequest(){
        return $this->request;
    }
    
    public function getResponse(){
        return $this->response;
    }
    
    public function getSession(){
        return $this->session;
    }
    
    public function getDbManager(){
        return $this->db_manager;
    }
    
    //ディレクトリパス（コントローラ）を取得
    public function getControllerDir(){
        return $this->getRootDir(). '/controllers';
    }
    //ディレクトリパス（ビュー）を取得
    public function getViewDir(){
        return $this->getRootDir(). '/views';
    }
    
    //ディレクトリパス（モデル）を取得
    public function  getModelDir(){
        return $this->getRootDir(). '/models';
    }
    //ディレクトリパス（web）を取得
    public function getWebDir(){
        return $this->getRootDir(). 'web';
    }
    
    //ログインアクションを格納
    //コンローラー名とアクション名を格納
    protected $login_action = array();

    //Routerからコントローラを特定してレスポンスの返信を行う
    public function run(){
        //Routerクラスのresolveメソッドを呼び出す
        //ルーティングパラメータを取得
        //コントローラー名とアクション名を特定する
        try{
            $params = $this->router->resolve($this->request->getPathInfo());
            
            //$params にはコントローラ名とアクション名が連想配列で入っている
            if($params === false){
                // todo-A
                //取得できなかった場合の処理を記述 例外を投げる
                throw new HttpNotFoundException('No route found for'. $this->request->getPathInfo());
            }
        
            //取得した$paramsからコントローラ名とアクション名を取り出す
            $controller = $params['controller'];
            $action = $params['action'];
            //var_dump($action);
            //特定したコントローラー名、アクション名をもとにrunActionメソッドを呼び出す
            $this->runAction($controller, $action, $params);
        } catch (HttpNotFoundException $e){
            //例外が発生したら404ページを表示させる
            $this->render404Page($e);
        } catch (UnauthorizedActionException $e){
            //ログイン画面に遷移させる
            list($controller, $action) = $this->login_action;
            //ログイン画面のコントローラ名とアクション名を指定してrunAction()メソッドを実行
            $this->runAction($controller, $action);
            
        }
        //レスポンスを送信する
        $this->response->send();
    }

        protected function render404Page($e){
        //
        $this->response->setStatusCode(404, 'Not Found');
        $message = $this->isDebugMode() ? $e->getMessage() : 'Page not found.';
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        
        $this->response->setContent(<<<EOF
<!DOCTYPE html PUBLIC "~//W3C//DTD XHTML 1.0 trasitinal//EN"
"http://www.w3.org/TR/xhtml 1/DTD/xhtml1-transitinal.dtd">
<html>
<head>
<meta http-equiv="Cotente-Type" content="text/html; charset=utf-8" />
<title>404</title>
</head>
<body>
            {$message}
</body>
</html>
EOF
            );
    }
    
     //アクションを実行する
    //規則：　コントローラのクラス名　コントローラ名＋Controller
    public function runAction($controller_name, $action, $params = array()){
        //コントローラ名の最初を大文字に変換
        $controller_class = ucfirst($controller_name). 'Controller';
        
        //コントローラクラス生成メソッド呼び出し
        $controller = $this->findController($controller_class);
        
        //コントローラクラスの生成、取得出来なかった場合
        if($controller === false){
            // todo-B
            //例外を投げる
            throw new HttpNotFoundException($controller_class. 'controller is not found.');
        }
        //var_dump($action);
        //アクション実行
        $content= $controller->run($action, $params);
        //レスポンスの取得
        $this->response->setContent($content);
    }
    
    //コントローラークラスを生成する
    //コントローラクラスが読み込まれていない場合、クラスファイルの読み込みを行う
    protected function findController($controller_class){
        //クラスが定義されているかチェック
        if(!class_exists($controller_class)){
            //クラスが定義されていない場合、クラスの定義を行う
            //$controlller_file 該当クラスへのディレクトリパスを格納
            $controller_file = $this->getControllerDir(). '/'. $controller_class. '.php';
            //ファイルの存在、読み込み可能チェック
            if(!is_readable($controller_file)){
                //読み込めない場合
                return false;
            }else{
                //読み込めた場合
                //ファイルの読み込みとクラスの存在をチェック
                require_once $controller_file;
            
                if(!class_exists($controller_class)){
                    return false;
                }
            }
        }
        
        //コントローラクラスの読み込み後、コントローラクラス生成して返す
        //$this：コンストラクタにApplicationクラス自身を渡して生成
        return new $controller_class($this);
    
    }
    
}
                    
?>
