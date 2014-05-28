<?php

class FollowingRepository extends DbRepository{
    
    //follwingテーブルへインサート（フォロー登録）
    public function insert($user_id, $followwing_id){
        $sql = "INSERT INTO following VALUES(:user_id, :following_id)";
        
        $stmt = $this->execute($sql, array(
            ':user_id' => $user_id,
            ':following_id' => $followwing_id,
        ));
    }
    
    //フォロー中かチェックする
    public function isFollowing($user_id, $following_id){
        $sql = "SELECT COUNT(user_id) as count
                    FROM following
                    WHERE user_id = :user_id
                        AND following_id = :following_id";
        
        $row = $this->fetch($sql, array(
            ':user_id' => $user_id,
            ':following_id' => $following_id,
        ));
        
        if($row['count'] !== '0'){
            return TRUE;
        }
        
        return false;
    }
}