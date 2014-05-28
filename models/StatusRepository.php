<?php

/*
 * statusテーブルへのアクセスを制御する
 * 
 */

class StatusRepository extends DbRepository{
    
    //statusテーブルへ登録
    public function insert($user_id, $body){
        date_default_timezone_set('Asia/Tokyo');
        $now = new DateTime();
        
        $sql = "INSERT INTO status(user_id, body, created_at)
                        VALUES(:user_id, :body, :created_at)";
        
        $stmt = $this->execute($sql, array(
            ':user_id' => $user_id,
            ':body' => $body,
            ':created_at' => $now->format('Y-m-d H:i:s'),
        ));
    }
    
    //現在ログインしているユーザに関する投稿を取得する
    //現在自分の情報のみ取得
    //加える変更：フォロー中のユーザの投稿も取得するよう変更
    public function fetchAllPersonalArchivesByUserID($user_id){

        /*
        $sql = "
    }
                SELECT a.*, u.user_name
                    FROM status a
                        LEFT JOIN user u ON a.user_id = u.id
                    WHERE u.id = :user_id
                    ORDER BY a.created_at DESC";
        */
        $sql = "
               SELECT a.*, u.user_name
                  FROM status a
                        LEFT JOIN user u on a.user_id = u.id
                        LEFT JOIN following f on f.following_id = a.user_id
                            AND f.user_id = :user_id
                  WHERE f.user_id = :user_id OR u.id = :user_id
                  ORDER BY a.created_at DESC";
                
        
        return $this->fetchAll($sql, array(':user_id' => $user_id));
    }
    
    //ユーザIDからデータを取得する
    public function fetchAllByUserId($user_id){
        $sql = "SELECT a.* , u.user_name
                    FROM status a 
                         LEFT JOIN user u ON a.user_id = u.id
                    WHERE u.id = :user_id
                    ORDER BY a.created_at DESC";
        return $this->fetchAll($sql, array(':user_id' => $user_id));
    }
    
    //投稿IDとユーザのIDに合致するレコードを一見取得する
    public function fetchByIdAndUserName($id, $user_name){
        $sql = "SELECT a.* , u.user_name
                    FROM status a 
                         LEFT JOIN user u ON u.id = a.user_id
                    WHERE a.id = :id
                         AND u.user_name = :user_name";
        
        return $this->fetch($sql, array(
            ':id' => $id,
            ':user_name' => $user_name,
        ));
    }
}

?>
