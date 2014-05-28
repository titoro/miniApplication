<?php

//ルーティングを行うクラス
class Router{
    
    //ルーティング定義配列の動的パラメータ指定格納？
    protected $routes;
    
    //初期化
    //
    public function __construct($definitions) {
        $this->routes = $this->compileRoutes($definitions);
    }
    
    /* 渡されたルーティング定義配列のそれぞれのキーに含まれる
     * 動的パラメータを正規表現でキャプチャできる形式に変換
     */
 /*
    public function compileRoutes($definitions){ 
       //変換済の値を格納
        $routes = array();
        
        foreach ($definitions as $uri => $params){
            //explodeメソッドで/で区切った文字列を$token配列に格納
            //最後の/は取り除く
            $tokens = explode('/', ltrim($uri, '/'));
            
            foreach ($tokens as $i => $token){
                //分割した値の中に:で始まる値があった場合
                //正規表現の形式に変換
                if(0 === strpos($token, ':')){
                    //:を$nameに
                    $name = substr($token, 1);
                    //
                    $token = '(?P<' . $name . '>[^/]';
                }
                $tokens[$i] = $token;   
            }
            // implodeメソッド
            // それぞれの要素を/で再び繋ぎ直し /要素/ として格納
            $pattern = '/'. implode('/', $tokens);
            //
            $routes[$pattern] = $params;

       }
       return $routes;
    }
*/
        public function compileRoutes($definitions)
    {
        $routes = array();

        foreach ($definitions as $url => $params) {
            $tokens = explode('/', ltrim($url, '/'));
            foreach ($tokens as $i => $token) {
                if (0 === strpos($token, ':')) {
                    $name = substr($token, 1);
                    $token = '(?P<' . $name . '>[^/]+)';
                }
                $tokens[$i] = $token;
            }

            $pattern = '/' . implode('/', $tokens);
            $routes[$pattern] = $params;
        }

        return $routes;
    }

    /*
     * マッチングを行う
     */
    //引数としてPATH_INFOを受け取る
/*
    public function resolve($path_info){
        //先頭に/がない場合/を付与
        if('/' !== substr($path_info, 0,1)){
                $path_info = '/' . $path_info;   
        }
        
        var_dump($path_info);
        
        foreach ($this->routes as $pattern => $params){
            if(preg_match('#~'. $pattern .'$#', $path_info, $matches)){
                $params = array_merge($params,$matches);
                
                return $params;
            }
        }
        
        return FALSE;
    }
 * 
 */
    
    public function resolve($path_info)
    {
        if ('/' !== substr($path_info, 0, 1)) {
            $path_info = '/' . $path_info;
        }

        foreach ($this->routes as $pattern => $params) {
            if (preg_match('#^' . $pattern . '$#', $path_info, $matches)) {
                $params = array_merge($params, $matches);
                //var_dump($params);

                return $params;
            }
        }

        return false;
    }
}


?>
