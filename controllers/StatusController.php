<?php

/*
 *  status情報のコントローラクラス
 */

class StatusController extends Controller{
    
    //indexアクションとpostアクションをログイン必須にする為のプロパティ
    protected $auth_actions = array('index','post');


    public function indexAction(){
        $user = $this->session->get('user');
        $statususes = $this->db_manager->get('Status')
                ->fetchAllPersonalArchivesByUserId($user['id']);
        
        return $this->render(array(
           'statuses' => $statususes,
            'body' => '',
            '_token' => $this->generateCartToken('status/post'),
        ));
    }
    
    //post処理
    public function postAction(){
        if(!$this->request->isPost()){
            $this->forward404();
        }
        
        $token = $this->request->getPost('_token');
        if(!$this->checkCsrfToken('status/post', $token)){
            return $this->redirect('/');
        }
        
        //アクセスチェック
        $body = $this->request->getPost('body');
        
        $errors = array();
        
        //バリデーション
        //投稿内容は200文字以内
        if(!strlen($body)){
            $errors[] = 'ひとことを入力して下さい';
        }else if(mb_strlen($body) > 200 ){
            $errors[] = 'ひとことは200文字以内で入力して下さい';
        }
        
        if(count($errors) === 0){
            //レコードの生成
            $user = $this->session->get('user');    //セッションからユーザ情報を取得
            //ユーザIDと投稿データをStatusRepositoryクラスのinsert()メソッドで登録
            $this->db_manager->get('Status')->insert($user['id'], $body);
            
            //ホームページへリダイレクト
            return $this->redirect('/');
        }
        
        //
        $user = $this->session->get('user');
        $statuses = $this->db_manager->get('Status')
                        ->fetchAllPersonalArchivesByUserId($user['id']);
        
        return $this->render(array(
            'errors' => $errors,
            'body' => $body,
            'statuses' => $statuses,
            '_token' => $this->generateCartToken('status/post'),
        ));
    }
    
    //userアクション
    public function userAction($params){
        //ユーザの存在チェック
        $user = $this->db_manager->get('User')
                ->fetchByUserName($params['user_name']);
        var_dump($user);
        if(!$user){
            $this->forward404();
        }
        
        //フォロー関連の処理
        $following = null;
        if($this->session->isAuthenticated()){
            //ログインしている場合
            $my = $this->session->get('user');
            var_dump('ログインユーザの名前');
            var_dump($my);
            //アクセスしているのが自分自身かチェック
            if($my['id'] !== $user['id']){
                $following = $this->db_manager->get('Following')
                        ->isFollowing($my['id'], $user['id']);
            }
        }
        
        //ユーザの投稿一覧の取得
        $statuses = $this->db_manager->get('Status')
                ->fetchAllByUserId($user['id']);
        
        return $this->render(array(
            'user' => $user,
            'statuses' => $statuses,
            'following'=> $following,
            '_token' => $this->generateCartToken('account/follow'),
        ));       
    }
    
    //showアクション
    public function showAction($params){
        //情報の取得
        $status = $this->db_manager->get('Status')
                ->fetchByIdAndUserName($params['id'], $params['user_name']);
        
        if(!$status){
            $this->forward404();
        }
        
        return $this->render(array('status' => $status));
    }
}

?>
