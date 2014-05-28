<?php

//オートローダーの為の読み込み
require '../bootstrap.php';
require '../MiniBlogApplication.php';

//
$app = new MiniBlogApplication(false);
$app->run();

?>