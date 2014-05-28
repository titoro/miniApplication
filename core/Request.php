<?php

//リクエスト情報を制御するクラス

class Request{
    
    //リクエスト(HTTPメソッド)がPOSTかどうか調べて結果を返す
    public function isPost(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            return TRUE;
        }
        return FALSE;
    }
    
    //リクエストがGETの場合、値を返す
    public function getGet($name, $default = null){
        if(isset($_GET[$name])){
            return $_GET[$name];
        }
        return $default;
    }
    
    //POSTの値を返す
    public function getPost($name, $default = null){
        if(isset($_POST[$name])){
            return $_POST[$name];
        }
        return $default;
    }
    
    //ホスト名を返す
    //リダイレクトを行う処理の中で使用
    public function getHost(){
        if(!empty($_SERVER['HTTP_HOST'])){
            return $_SERVER['HTTP_HOST'];
        }
        return $_SERVER['SERVER_NAME'];
    }
    
    //SSLの接続かどうか判定し、結果を返す
    //今回は実装しないが、拡張性のため記述
    public function isSsl(){
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
            return TRUE;
        }
        return FALSE;
    }
    
    //ホスト部分以降の値を返す
    public function getRequestUri(){
        //リクエストされたURLの情報をそのまま返す
        return $_SERVER['REQUEST_URI'];
    }
    
    //ベースURL（ホスト部分以降フロントコントローラーまでのパス）を返す
 /*
    public function getBaseUrl(){
        //フロントコントローラーまでのパスを取得
        $script_name = $_SERVER['SCRIPT_NAME'];
        
        //ホスト部分以降のURIを取得
        $request_uri = $this->getRequestUri();
        
        //ベースURLを取得
        if(0 === strpos($request_uri, $script_name)){   //フロントコントローラーの位置を調べる
            //フロントコントローラーがURLに含まれていた場合（フロントコントローラーから始まっていた場合
            //そのまま返す
            //var_dump($script_name);
            return $script_name;
        }else if(0 === strpos($request_uri, dirname($script_name))){    //親ディレクトリを取得、フロントコントローラーまでに含まれている場合
            //フロントコントローラーが省略されている場合
            //var_dump($script_name);
            return rtrim(dirname($script_name),'/');
            
        }
        //その他（パスの指定が間違えている場合、空白を返す
        return '';
    }
  
  */
    public function getBaseUrl()
    {
        $script_name = $_SERVER['SCRIPT_NAME'];

        $request_uri = $this->getRequestUri();

        if (0 === strpos($request_uri, $script_name)) {
            return $script_name;
        } else if (0 === strpos($request_uri, dirname($script_name))) {
            return rtrim(dirname($script_name), '/');
        }

        return '';
    }
/*    
    //PATH_INFOを返す
    public function getPathInfo(){
        //ベースURLを取得
        $base_url = $this->getBaseUrl();
        //ホスト部分以降のURIを取得
        $request_uri = $this->getRequestUri();
        
        //ホスト部分以降のURIからゲットパラメーターを取り除く
        if(false !== ($pos = strpos($request_uri, '?'))){
            //?以降のパラメーターを取り除くため
            //$pos ?が最初に現れる位置を格納している。
            $request_uri = substr($request_uri, 0, $pos);
        }
        
        //RECQUEST_URIからホスト部分までを取り除いて$path_infoに格納
        $path_info = (string)  substr($request_uri, strlen($base_url));
        
        return $path_info;
    }
*/  
        public function getPathInfo()
    {
        $base_url = $this->getBaseUrl();
        $request_uri = $this->getRequestUri();
        
        var_dump($request_uri);

        if (false !== ($pos = strpos($request_uri, '?'))) {
            $request_uri = substr($request_uri, 0, $pos);
        }

        $path_info = (string)substr($request_uri, strlen($base_url));
        var_dump($path_info);

        return $path_info;
    }
}

?>
