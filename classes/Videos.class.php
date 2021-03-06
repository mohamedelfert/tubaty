<?php


class Videos extends MysqliConnect{
    private $title;
    private $link;
    private $image;
    private $description;
    private $category;
    private $type;
    private $id;

    public function setVideosInput($title,$link,$image,$description,$category,$type,$id = null){
        $this->title       = $this->filter_string(trim($this->esc($this->html_tags($title))));
        $this->link        = trim($this->esc($this->html_tags($link)));
        $this->image       = $image;
        $this->description = $this->filter_string(trim($this->esc($this->html_tags($description))));
        $this->category    = (int)$category;
        $this->type        = $type;
        $this->id          = (int)$id;
    }

    private function checkSetInput(){
        if (empty($this->title)){
            Messages::setMessage('danger','خطأ','يجب كتابه عنوان للفيديو');
            echo Messages::getMessage();
        }elseif (empty($this->link)){
            Messages::setMessage('danger','خطأ','يجب وضع لينك للفيديو');
            echo Messages::getMessage();
        }elseif (!preg_match("~(?:https?://)?(?:www\.)?youtu(?:be\.com/watch\?(?:.*?&(?:amp;)?)?v=|\.be/)([\w\-]+)(?:&(?:amp;)?[\w\?=]*)?~",$this->link)){
            Messages::setMessage('danger','خطأ','يجب وضع لينك يوتيوب صحيح');
            echo Messages::getMessage();
        }elseif ($this->image === null and $this->type === 'add'){
            Messages::setMessage('danger','خطأ','يجب وضع صوره للفيديو');
            echo Messages::getMessage();
        }elseif (empty($this->description)){
            Messages::setMessage('danger','خطأ','يجب وضع وصف بسيط للفيديو');
            echo Messages::getMessage();
        }elseif (empty($this->category)){
            Messages::setMessage('danger','خطأ','يجب اختيار قسم للفيديو');
            echo Messages::getMessage();
        }else{
            if ($this->type == 'add'){
                $image = new UploadImages($this->image);
                if ($image->Image()){
                    $this->image = $image->uploadImage;
                    return true;
                }
            }elseif ($this->type != 'add' and $this->image != null){
                $image = new UploadImages($this->image);
                if ($image->Image()){
                    $this->image = $image->uploadImage;
                    return true;
                }
            }else{
                return true;
            }
            return false;
        }
    }

    public function displayErrors(){
        if ($this->checkSetInput()){
            $this->checkType();
        }
    }

    private function checkType(){
        if ($this->type == "add"){
            $this->addNewVideo();
        }else{
            $this->editVideo();
        }
    }

    private function addNewVideo(){
        $videoLink = base_convert(microtime(false),10,36);
        $user_id = $_SESSION['user']['id'];
        $this->insert("videos", "`user_id`, `title`, `link`, `image`, `description`, `category`, `videoLink`" ,
                                      "'$user_id','$this->title','$this->link','$this->image','$this->description','$this->category','$videoLink'");
        if ($this->execute()){
            Messages::setMessage('success','رائع','تم رفع الفيديو بنجاح');
            echo Messages::getMessage();
            echo '<meta http-equiv="refresh" content="2; \'tubes.php\'">';
        }
        return FALSE;
    }

    private function editVideo(){
        if ($this->image === null){
            $this->update('videos', "title = '{$this->title}',link = '{$this->link}',description = '{$this->description}',category = '{$this->category}'", 'id', $this->id);
            if ($this->execute()){
                Messages::setMessage('success','رائع','تم التحديث بنجاح');
                echo Messages::getMessage();
                echo '<meta http-equiv="refresh" content="3; \'tubes.php\'">';
            }
        }else{
            $this->update('videos', "title = '{$this->title}',link = '{$this->link}',image = '{$this->image}',description = '{$this->description}',category = '{$this->category}'", 'id', $this->id);
            if ($this->execute()){
                Messages::setMessage('success','رائع','تم التحديث بنجاح');
                echo Messages::getMessage();
                echo '<meta http-equiv="refresh" content="3; \'tubes.php\'">';
            }
        }
    }

    public function deleteVideo($id){
        $this->query('id, image', 'videos', "WHERE id = '{$id}'");
        if ($this->execute() and $this->rowCount() > 0){
            /** this for delete image from path when video deleted **/
            $image = $this->fetch();
            $image_path = __DIR__ . '/../libs/upload/' . $image['image'];
            if (file_exists($image_path)){
                unlink($image_path);
            }
            $this->delete('videos', 'id', $id);
            if ($this->execute()){
                $this->delete("comments",'video_id',$id);
                if ($this->execute()){
                    echo Messages::setMessage('success','رائع','تم الحذف بنجاح') . Messages::getMessage();
                    echo '<meta http-equiv="refresh" content="2; \'tubes.php\'">';
                }
            }else{
                echo Messages::setMessage('danger','خطأ','عفوا خطأ غير متوقع من النظام') . Messages::getMessage();
                echo '<meta http-equiv="refresh" content="2; \'tubes.php\'">';
            }
        }else{
            header("Location: tubes.php");
        }
    }

    public function displayVideos($other = null){
        $this->query('*', 'videos',$other);
        $this->execute();
        if ($this->rowCount() > 0){
            while ($rows = $this->fetch()){
                $rowVideos[] = $rows;
            }
            return $rowVideos;
        }
    }

    public function checkVideoUrl($id){
        $id = $this->esc($this->html_tags($id));
        $this->query('id', 'videos', "WHERE `videoLink` = '{$id}'");
        if ($this->execute() and $this->rowCount() > 0){
            $id = $this->fetch();
            return $id['id'];
        }else{
            header("Location: index.php");
        }
    }

    public function likeVideos($title,$category,$id){
        $this->query("`title`, `videoLink`, `image`", 'videos', "WHERE title LIKE '%$title%' AND id != '$id' ORDER BY RAND() LIMIT 4");
        if ($this->execute() and $this->rowCount() > 0){
            while ($videos = $this->fetch()){
                $videoLike[] = $videos;
            }
            return $videoLike;
        }else{
            $this->query("`title`, `videoLink`, `image`", 'videos', "WHERE category = '$category' AND id != '$id' ORDER BY RAND() LIMIT 4");
            if ($this->execute() and $this->rowCount() > 0){
                while ($videos = $this->fetch()){
                    $videoLike[] = $videos;
                }
                return $videoLike;
            }
        }
    }

    public function updateVideoViews($id){
        $this->query('views', 'videos', "WHERE id = '$id'");
        if ($this->execute() and $this->rowCount() > 0){
            $views = $this->fetch();
            $newView = $views['views'] + 1;
            $this->update('videos', "views = '$newView'", 'id',$id);
            $this->execute();
        }
    }

    public function addNewComment($videoId,$comment,$dir){
        $videoId = (int)$videoId;
        $comment = $this->filter_string($this->esc($this->html_tags($comment)));
        $userId  = $_SESSION['user']['id'];
        $this->insert('comments', "`user_id`, `video_id`, `comment`", "'$userId','$videoId','$comment'");
        if ($this->execute()){
            header("Location: video.php?v=$dir");
        }
    }

    public function getVideoComments($id){
        $id = (int)$id;
        $this->query('*', 'comments', "WHERE `video_id` = '$id' ORDER BY id DESC");
        if ($this->execute() and $this->rowCount() > 0){
            while ($comments = $this->fetch()){
                $comment[] = $comments;
            }
            return $comment;
        }else{
            return null;
        }
    }

    public function getAllVideoComments($otehr = null){
        $this->query('*', 'comments', $otehr);
        if ($this->execute() and $this->rowCount() > 0){
            while ($comments = $this->fetch()){
                $comment[] = $comments;
            }
            return $comment;
        }else{
            return null;
        }
    }

    public function getVideoById($id){
        $id = (int)$id;
        $this->query("`videoLink`", 'videos', "WHERE id = '$id'");
        if ($this->execute() and $this->rowCount() > 0){
            $videoLink = $this->fetch();
            return $videoLink['videoLink'];
        }
        return null;
    }

    public function getUserNameById($id){
        $id = (int)$id;
        $this->query("`first_name`,`last_name`", 'users', "WHERE id = '$id'");
        if ($this->execute() and $this->rowCount() > 0){
            $user = $this->fetch();
            return $user['first_name'] . ' ' . $user['last_name'];
        }
        return null;
    }

/**
 * this method i use when url contain delete i use $_GET to check delete variable and then start this function to delete comment
 */
    public function deleteVideoComment($id,$dir,$other = null){
        $this->delete('comments', 'id',$id,$other);
        if ($this->execute()){
            header("Location: video.php?v=$dir");
        }else{
            Messages::setMessage('danger','خطأ','غير متوقع أثناء الحذف');
            echo Messages::getMessage();
        }
    }

/**
 * this method i use when use ajax
 */
    public function deleteCommentWithAjax($id){
        $this->delete('comments', 'id',$id);
        if ($this->execute()){
            return true;
        }
        return false;
    }

    public function getCountViews($id){
        $this->query('views', "videos", "WHERE id = '$id'");
        if ($this->execute() and $this->rowCount() > 0){
            $views = $this->fetch();
            return $views['views'];
        }else{
            return 0;
        }
    }

    public function getCountComments($id){
        $this->query('id', "comments", "WHERE `video_id` = '$id'");
        if ($this->execute() and $this->rowCount() > 0){
            return $this->rowCount();
        }else{
            return 0;
        }
    }

    public function countComments(){
        $this->query('id', "comments");
        if ($this->execute() and $this->rowCount() > 0){
            return $this->rowCount();
        }else{
            return 0;
        }
    }

    public function countVideos($other = null){
        $this->query('id', "Videos", $other);
        if ($this->execute() and $this->rowCount() > 0){
            return $this->rowCount();
        }else{
            return 0;
        }
    }

    public function deleteAnyComment($id){
        $id = (int)$this->esc($id);
        $this->query('*', "comments", "WHERE id = '{$id}'");
        if ($this->execute() and $this->rowCount() > 0){
            $this->delete('comments', 'id',$id);
            if ($this->execute()){
                echo Messages::setMessage('success','رائع','تم حذف التعليق بنجاح') . Messages::getMessage();
                echo '<meta http-equiv="refresh" content="2; \'comments.php\'">';
            }else{
                echo Messages::setMessage('danger','خطأ','غير متوقع الرجاء المحاوله مره اخري') . Messages::getMessage();
            }
        }else{
            header("Location: comments.php");
        }
    }
}