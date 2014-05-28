<?php

/* ビュークラス
 * ビューファイルの読み込み、ビューファイルに渡す変数の制御を行う
 *  */

class View{
    protected $base_dir;        //Viewクラスファイルへのディレクトリパス
    protected $defaults;        //Viewクラスへ渡す変数
    //renderメソッド内でレイアウトファイルの読み込みを行う際使用する
    protected $layout_variables = array();     
    
    //変数の初期化
    public function __construct($base_dir, $defaults = array()) {
        $this->base_dir = $base_dir;
        $this->defaults = $defaults;
    }
    
    //
    public function setLayoutVar($name, $value){
        $this->layout_variables[$name] = $value; 
    }
    
    //ビュークラス（ファイルの読み込み）
    //それぞれのビューでファイルを指定して読み込む
    //第１引数　ビューファイルへのパス
    //第２引数　ビューファイルに渡す変数
    //第３引数　レイアウトファイル名を指定
    public function render($_path, $_variables = array(), $_layout = false){
        //ファイルのパスを取得
        $_file = $this->base_dir. '/'. $_path. '.php';
        
        //defultsプロパティで使用する変数をすべて格納
        //引数の連想配列をキー名、値の変数に変換
        extract(array_merge($this->defaults, $_variables));
        
        //アウトバッファリング開始
        ob_start();
        //自動フラッシュをオフに設定
        ob_implicit_flush(0);
        
        //ビューファイルの読み込み
        require $_file;
        
        //アウトバッファリングの内容を取得、呼び出しの後バッファのクリア
        $content = ob_get_clean();
        
        //レイアウトファイルの指定のチェック
        if($_layout){
            //再度renderメソッド呼び出し
            //第1引数　$_layout　レイアウト指定
            //第2引数　レイアウトファイルの情報の連想配列
            //第3引数  アウトバッファリングの内容
            $content = $this->render($_layout,
            array_merge($this->layout_variables, array(
                '_content' => $content
                )
            ));
        }
        
        //ビューファイルの内容が文字列として格納されているの返す
        return $content;
        
    }
    
    //エスケープ処理
    public function escape($string){
      return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');  
    }
}


?>
