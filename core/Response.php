<?php

/*
 * HTMLヘッダとHTMLなどを返すクラス
 * 
 */
class Response{
    protected $content;                 //クライアントに返す内容を格納
    protected $status_code = 200;
    protected $status_text = 'OK';
    protected $http_headers = array();  //HTTPヘッダを格納
    
    //レスポンスの送信を行う
    public function send(){
        //ステータスコードの指定
        header('HTTP/1.1 '.$this->status_code . ' '. $this->status_text);
        
        //http_headerプロパティにHTTPレスポンスヘッダへの指定があれば送信
        foreach ($this->http_headers as $name => $value){
            header($name. ': '.$value);
        }
        
        //レスポンスの内容を送信
        //echoで出力を行うだけで送信される
        echo $this->content;
    }
    
    //コンテントセッタ
    public function setContent($content){
        $this->content = $content;
    }
    
    //ステータスコードセッタ
    public function setStatusCode($status_code, $status_text=''){
        $this->status_code = $status_code;  //ステータスコード
        $this->status_text = $status_text;  //ステータステキスト
    }
    
    //ヘッダセッタ
    public function setHttpHeader($name, $value){
        //ヘッダの名前をキーに値を連想配列でセット
        $this->http_headers[$name] = $value;
    }
}


?>
