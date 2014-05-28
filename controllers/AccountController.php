<?php
/*
 *  
*/

class AccountController extends Controller{
    
    //indexアクションとsignoutアクションをログイン必須にするためのプロパティ
    protected $auth_actions = array('index', 'signout');

        public function signupAction(){
        return $this->render(array(
            '_token' => $this->generateCartToken('account/signup'),
        ));
    }
    
    public function registerAction(){
        
        //リクエストがPOSTかチェックする
        if(!$this->request->isPost()){
            $this->forward404();
        }
        
        //CSRFトークンチェック
        $token = $this->request->getPost('_token');
        if(!$this->checkCsrfToken('account/signup', $token)){
            return $this->redirect('account/signup');
        }
        
        $user_name = $this->request->getPost('user_name');
        var_dump($user_name);
        $password = $this->request->getPost('password');
        
        $errors = array();
        
        if(!strlen($user_name)){
            $errors[] = 'ユーザIDを入力してください';
        }else if(!preg_match('/^\w{3,20}$/', $user_name)){
        //}else if (!preg_match('/^\w{3,20}$/', $user_name)) {
            $errors[] = 'ユーザIDは半角英数字およびアンダースコアを３～２０文字で
                            入力して下さい';
        }else if(!$this->db_manager->get('User')->isUniqueUserName($user_name)){
            $errors[] = 'ユーザIDは既に使用されております';
        }
        
        if(!strlen($password)){
            $errors[] = 'パスワードを入力して下さい';
        }else if(4 > strlen($password) || strlen($password) > 30){
            $errors[] = 'パスワードは４～３０文字以内で入力して下さい';
        }
        
        //エラーがなかったら登録処理
        if(count($errors) === 0){
            $this->db_manager->get('User')->insert($user_name, $password);
            
            $this->session->setAuthenticated(true);
            
            $user = $this->db_manager->get('User')->fetchByUserName($user_name);
            $this->session->set('user', $user);
            
            //ユーザのホームページへリダイレクトさせる
            return $this->redirect('/');
            
        }
        
        //エラーが発生した場合
        //アカウント登録ページに遷移
        return $this->render(array(
            'user_name' => $user_name,
            'password' => $password,
            'errors' => $errors,
            '_token' => $this->generateCartToken('account/signup'),
        ),'signup');
        
    }
    
    //indexアクション
    public function indexAction(){
        $user = $this->session->get('user');
        var_dump($user);
        $followings = $this->db_manager->get('User')
                ->fetchAllFollowingsByUserId($user['id']);
        
        return $this->render(array(
            'user' => $user,
            'followings' => $followings,
            ));
    }
    
    //signinアクション
    public function signinAction(){
        //ログイン状態のチェック
        if($this->session->isAuthenticated()){
            //既にログインしている場合はアカウント情報トップページへリダイレクト
            return $this->redirect('/account');
        }
        
       return $this->render(array(
            'user_name' => '',
            'password' => '',
            '_token' => $this->generateCartToken('account/signin'),
                ));
    }
    
    //ログイン処理を行うアクション
    public function authenticateAction(){
        //ログイン状態かどうかチェック
        if($this->session->isAuthenticated()){
            //ログインしているならアカウントのindexページへリダイレクト
            return $this->redirect('/account');
        }
        //POSTで送信されたかチェック
        if(!$this->request->isPost()){
            $this->forward404();
        }
        //CSRFチェック
        $token= $this->request->getPost('_token');
        if(!$this->checkCsrfToken('account/signin', $token)){
            return $this->redirect('/account/signin');
        }
        
        $user_name = $this->request->getPost('user_name');
        $password = $this->request->getPost('password');
        
        $errors = array();
        
        if(!strlen($user_name)){
            $errors[] = 'ユーザIDを入力してください';
        }
        
        if(!strlen($password)){
            $errors[] = 'パスワードを入力してください';
        }
        
        if(count($errors) === 0){
            
            $user_repoitory = $this->db_manager->get('User');
            $user = $user_repoitory->fetchByUserName($user_name);
            
            //バリデーションのチェック
            if(!$user || ($user['password'] !== $user_repoitory->hashPassword($password))){
                $errors[] = 'ユーザIDかパスワードが不正です';
            }else{
                //認証成功
                $this->session->setAuthenticated(true);
                $this->session->set('user', $user);
                
                return $this->redirect('/');
            }
        }
        
        return $this->render(array(
            'user_name' => $user_name,
            'password' => $password,
            'errors' => $errors,
            '_token' => $this->generateCartToken('account/signin'),
        ),'signin');    
    }
    
    //signoutアクション
    public function signoutAction(){
        $this->session->clear();        //セッション情報を削除
        $this->session->setAuthenticated(false);    //未ログイン状態にする
        
        //ログイン画面へ遷移
        return $this->redirect('/account/signin');
    }
    
     //followアクション
    public function followAction(){
        //POSTされてきたかどうかチェック
        if(!$this->request->isPost()){
            $this->forward404();
        }
        
        //POST情報からフォローするユーザ名の取得
        $following_name = $this->request->getPost('following_name');
        if(!$following_name){
            $this->forward404();
        }
        
        $token = $this->request->getPost('_token');
        if(!$this->checkCsrfToken('account/follow', $token)){
            return $this->redirect('/user/'.$following_name);
        }
        
        $follow_user = $this->db_manager->get('User')
                ->fetchByUserName($following_name);
        if(!$follow_user){
            $this->forward404();
        }
        
        //セッションからユーザ情報を取得
        $user = $this->session->get('user');
        
        $following_repository = $this->db_manager->get('Following');
        if($user['id'] !== $follow_user['id']
                && !$following_repository->isFollowing($user['id'], $follow_user['id'])){
            $following_repository->insert($user['id'], $follow_user['id']);
        }
        
        return $this->redirect('/account');
    }
}


?>
