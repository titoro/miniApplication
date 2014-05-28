<?php

//オートローダーを記述しているクラス
class ClassLoader
{
    protected $dirs;
    
    //オートローダークラスを登録する
    public function register(){
        //オートローダーの登録
        spl_autoload_register(array($this,'loadClass'));
    }
    
    //ディレクトリを登録する
    //引数
    //$dir オートロード対象のディレクトリへのフルパス
    //ディレクトリを複数登録できるように配列で保持
    public function registerDir($dir){
        $this->dirs[] = $dir;
    }
    
 
    
    //ファイルのインクルード処理
    //オートロード時に自動的に呼ばれる
    //引数
    //$class クラス名（オートロード対象）
    public function loadClass($class){
        
        foreach ($this->dirs as $dir){
            //読み込むファイル名をパスでセット
            $file = $dir . '/' . $class . '.php';
            var_dump($file);
            if(is_readable($file)){
                //ファイルの読み込み
                require $file;
                
                return;
            }
        }
    }
}


?>
