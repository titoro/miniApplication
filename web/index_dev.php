<?php

//オートローダーの為の読み込み
require '../bootstrap.php';
require '../MiniBlogApplication.php';

//デバッグモードをオンにして生成、実行
$app = new MiniBlogApplication(TRUE);
$app->run();

?>