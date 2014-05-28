<?php

/* 
 * Applicationクラスの子クラス
 */

class MiniBlogApplication extends Application
{
    protected $login_action = array('account', 'signin');
    
    //ルートディレクトリを返す
    public function getRootDir() {
        //一つ上のディレクトリ（web)へのパスを返す
        return dirname(__FILE__);
    }
    
    //ルーティング定義配列を返す
    protected function registerRoutes() {
        return array( 
            '/'
                =>array('controller' => 'status', 'action' => 'index'),
            '/status/post'
                =>array('controller' => 'status', 'action' => 'post'),
            
            //StatusControllerの他のルーティング
            '/user/:user_name'
                =>array('controller' => 'status', 'action' => 'user'),
            '/user/:user_name/status/:id'
                =>array('controller' => 'status', 'action' => 'show'),
            //followアクションへのルーティング
            '/follow'
                =>array('controller' => 'account', 'action' => 'follow'),
            '/account'
                => array('controller' => 'account', 'action' => 'index'),
            '/account/:action'
                => array('controller' => 'account'),
            
        );
    }
    
    //アプリケーションの設定を行う
    protected function configure() {
        //データベースへの接続情報を設定
        $this->db_manager->connect('master', array(
            'dsn' => 'mysql:dbname=mini_blog;host=localhost',
            'user' => 'root',
            'password' => ''
        ));
    }
    
}

?>