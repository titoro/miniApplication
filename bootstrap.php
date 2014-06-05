<?php
//オートロードに登録するためのクラス

//ここのみ明示的に読み込み
//以降はClassLoaderクラスを使用してオートローダーで読み込み
require 'core/ClassLoader.php';

//読み込むディレクトリの登録
$loader = new ClassLoader();
$loader->registerDir(dirname(__FILE__).'/core');
//echo dirname(__FILE__.'/core');
$loader->registerDir(dirname(__FILE__).'/models');
$loader->registerDir(dirname(__FILE__)."/framework");
//echo dirname(__FILE__.'/models');
//オートローダー登録処理を呼び出す
$loader->register();

?>
