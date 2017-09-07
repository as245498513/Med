<?php
/**
 * Created by PhpStorm.
 * User: CMJ
 * Date: 2017/9/5
 * Time: 11:40
 */
namespace Api\Controller;
use Common\Controller\MedbaseController;
use Think\Controller;
use Think\Exception;

class MedController extends MedbaseController{

    /**
     * 获取音频列表
     * @interface 2.1
     * @return array returnMsg
     */
    public function audio_list(){
        //参数
        $type = I('type');
        $albumid = I('albumid');
        $keyword = I('keyword');
        $difficulty = intval(I('difficulty'));
        $page = I('page',1);
        $pagesize = I('pagesize',10);
        $start = ($page - 1) * $pagesize;
        $limit = "{$start},{$pagesize}";

        $user_id = $this->wx_user_id;

        $audio_model = M('med_audio');
        $audio_to_album_model = M('med_audio_to_album');

        $where = array();

        //返回的数据库字段
        $field = "cmf_med_audio_to_album.audio_id,
                  cmf_med_audio_to_album.album_id,

                  cmf_med_audio_album.album_name,

                  cmf_med_audio.audio_difficulty,
                  cmf_med_audio.audio_name,
                  cmf_med_audio.audio_url,
                  cmf_med_audio.audio_cover,
                  cmf_med_audio.audio_description,
                  cmf_med_audio.audio_listen_count,

                  IF(cmf_med_audio_like.user_id <> '',1,0) is_like
                  ";


        //参数检查
        switch($type){
            case 1:
                if(empty($albumid)){
                    $this->returnMsg(-2);
                }else{
                    $where['cmf_med_audio_to_album.album_id'] = $albumid;
                    //分页
                    $count = $audio_to_album_model->where($where)->count();
                    $audio_list = $audio_to_album_model
                        ->field($field)
                        ->join("cmf_med_audio_album ON cmf_med_audio_album.id = cmf_med_audio_to_album.album_id","left")
                        ->join("cmf_med_audio ON cmf_med_audio.id = cmf_med_audio_to_album.audio_id","left")
                        ->join("cmf_med_audio_like ON cmf_med_audio_like.audio_id = cmf_med_audio.id and cmf_med_audio_like.user_id = {$user_id}","left")
                        ->where($where)->limit($limit)->order('cmf_med_audio.createtime desc')->select();
                }
            break;
            case 2:
                if(empty($keyword)){
                    $this->returnMsg(-2);
                }else{
                    $where['cmf_med_audio.audio_name'] = array('like','%'.$keyword.'%');
                }
            break;
            case 3:
                if(empty($difficulty)){
                    $this->returnMsg(-2);
                }else{
                    $where['cmf_med_audio.audio_difficulty'] = $difficulty;
                }
            break;
        }
        if($type==2 || $type==3){
            $count = $audio_model->where($where)->count();
            $audio_list = $audio_model
                ->field($field)
                ->join("cmf_med_audio_to_album ON cmf_med_audio.id = cmf_med_audio_to_album.audio_id","left")
                ->join("cmf_med_audio_album ON cmf_med_audio_album.id = cmf_med_audio_to_album.album_id","left")
                ->join("cmf_med_audio_like ON cmf_med_audio_like.audio_id = cmf_med_audio.id and cmf_med_audio_like.user_id = {$user_id}","left")
                ->where($where)->limit($limit)->order('cmf_med_audio.createtime desc')->select();
        }
        //是否有下一页
        $hasNextPage = 0;
        if($page<($count/$pagesize)){
            $hasNextPage=1;
        }

        foreach($audio_list as $k=>$v ){
            $audio_list[$k]['audio_url'] = $this->handler_audio_url($v['audio_url']);
            $audio_list[$k]['audio_cover'] = $this->handler_img_url($v['audio_cover'],"audio");
        }

        $data = array(
            'list'=>$audio_list,
            'page'=>$page,
            'pagesize'=>$pagesize,
            'count'=>$count,
            'hasNextPage'=>$hasNextPage
        );
        $this->returnMsg(0,"成功",$data);
    }

    /**
     * 获取音频详细信息
     * @interface 2.2
     * @return array returnMsg
     */
     public function audio_info(){
         //参数
         $audio_id = intval(I('audio_id'));
         $user_id = $this->wx_user_id;

         $audio_model = M('med_audio');
         $audio_to_album_model = M('med_audio_to_album');

         //参数检查
         if(empty($audio_id)){
             $this->returnMsg(-2);
         }
         $where = array('cmf_med_audio.id'=>$audio_id);

         //返回的数据库字段
         $field = "cmf_med_audio.id,
                   cmf_med_audio.audio_name,
                   cmf_med_audio.audio_difficulty,
                   cmf_med_audio.audio_listen_count,
                   cmf_med_audio.audio_url,
                   cmf_med_audio.audio_cover,
                   cmf_med_audio.audio_description,
                   IF(cmf_med_audio_like.user_id <> '',1,0) is_like
                  ";
         $audio_info = $audio_model->field($field)
                       ->join("cmf_med_audio_like ON cmf_med_audio_like.audio_id = cmf_med_audio.id and cmf_med_audio_like.user_id = {$user_id}","left")
                       ->where($where)
                       ->find();
         //专辑分类
         $album_list = $audio_to_album_model->field("cmf_med_audio_album.id,cmf_med_audio_album.album_name")
                       ->join("cmf_med_audio_album ON cmf_med_audio_album.id = cmf_med_audio_to_album.album_id","left")
                       ->where(array("cmf_med_audio_to_album.audio_id"=>$audio_id))
                       ->select();
         $audio_info["album_list"] = $album_list;

         $this->returnMsg(0,"成功",$audio_info);
     }

    /**
     * 音频收藏与取消收藏
     * @interface 2.3
     * @return array returnMsg
     */
    public function audio_like(){
        //参数
        $is_cancel = I('is_cancel');
        $audio_id = I('audio_id');
        $user_id = $this->wx_user_id;

        $audio_like_model = M('med_audio_like');

        if(empty($audio_id)){
            $this->returnMsg(-2);
        }

        $where = array(
            'user_id'=>$user_id,
            'audio_id'=>$audio_id
        );
        $audio_like_info = $audio_like_model->field('id')->where($where)->find();
        $is_exist = $audio_like_model->where(array('id'=>$audio_id))->count();
        if(!$is_exist){
            $this->returnMsg(-1,"操作失败,该音频不存在!");
        }

        //取消收藏
        if($is_cancel==1){
            if($audio_like_info){
                $rs = $audio_like_model->where($where)->delete();
                if($rs){
                    $this->returnMsg(0,"取消收藏成功!");
                }else{
                    $this->returnMsg(-1,"取消收藏失败!");
                }
            }else{
                $this->returnMsg(-1,"请勿重复取消收藏!");
            }
        }else{
            if(!empty($audio_like_info)){
                $this->returnMsg(-1,"请勿重复收藏");
            }else{
                $add = array(
                    'audio_id'=>$audio_id,
                    'user_id'=>$user_id,
                    'createtime'=>time()
                );
                $rs = $audio_like_model->add($add);
                if($rs){
                    $this->returnMsg(0,"收藏成功!");
                }else{
                    $this->returnMsg(-1,"收藏失败!");
                }
            }
        }

    }

    /**
     * 用户点击量增加
     * @interface 2.4
     * @return array returnMsg
     */
    public function audio_click(){
        //参数
        $audio_id = I('audio_id');

        $user_id = $this->wx_user_id;

        $audio_model = M('med_audio');
        $audio_history_model = M('med_listen_history');
        $album_history_model = M('med_album_listen_history');
        $audio_to_album_model = M('med_audio_to_album');

        //检查参数
        if(empty($audio_id)){
            $this->returnMsg(-2);
        }
        try{
            $audio_history_model->startTrans();
            //该用户是否之前收听过该音频
            $where = array(
                'user_id'=>$user_id,
                'audio_id'=>$audio_id
            );
            $rs1 = $audio_history_model->where($where)->count();
            $album_list = $audio_to_album_model->field('album_id')->where(array('audio_id'=>$audio_id))->select();
            //有收听过
            if($rs1){
                //增加本人音频听数
                $audio_data['createtime'] = time();
                $audio_data['my_listen_count'] = ['exp','my_listen_count+1']; //音频收听数加1
                $rs2 = $audio_history_model->where($where)->save($audio_data);
                if($rs2){
                  //增加专辑收听次数
                    foreach($album_list as $key=>$value){
                        if($album_history_model->where(array("user_id"=>$user_id,"album_id"=>$value['album_id']))->count()){
                            $album_history_data['createtime'] = time();
                            $album_history_data['my_album_listen_count'] = ['exp','my_album_listen_count+1']; //专辑收听数加1
                            $album_history_model->where(array("user_id"=>$user_id,"album_id"=>$value['album_id']))->save($album_history_data);
                        }else{
                            $album_history_add = array(
                                'user_id'=>$user_id,
                                'album_id'=>$value['album_id'],
                                'createtime'=>time()
                            );
                            $album_history_model->add($album_history_add);
                        }
                    }
                }else{
                    $audio_history_model->rollback();
                    $this->returnMsg(-1,"音频播放次数增加失败!");
                }
            }else{
                $audio_history_add = array(
                    'audio_id'=>$audio_id,
                    'user_id'=>$user_id,
                    'createtime'=>time()
                );
                $rs3 = $audio_history_model->where($where)->add($audio_history_add);
                if($rs3){
                    //增加专辑收听次数
                    foreach($album_list as $key=>$value){
                        if($album_history_model->where(array("user_id"=>$user_id,"album_id"=>$value['album_id']))->count()){
                            $album_history_data['createtime'] = time();
                            $album_history_data['my_album_listen_count'] = ['exp','my_album_listen_count+1']; //专辑收听数加1
                            $album_history_model->where(array("user_id"=>$user_id,"album_id"=>$value['album_id']))->save($album_history_data);
                        }else{
                            $album_history_add = array(
                                'user_id'=>$user_id,
                                'album_id'=>$value['album_id'],
                                'createtime'=>time()
                            );
                            $album_history_model->add($album_history_add);
                        }
                    }
                }else{
                    $audio_history_model->rollback();
                    $this->returnMsg(-1,"音频播放次数增加失败!");
                }
            }
            $audio_model->where(array('id'=>$audio_id))->setInc("audio_listen_count");
            $audio_history_model->commit();
            $this->returnMsg(0,"成功!");
        }catch(Exception $e){
            $audio_history_model->rollback();
            $this->returnMsg(-1,"音频播放次数增加失败!");
        }
    }

    /**
     * 音频收听历史记录
     * @interface 2.5
     * @return array returnMsg
     */
    public function audio_history(){
        //参数
        $duration = I('duration',10);//需要查询多少天内的收听记录,默认10天
        $page = I('page',1);
        $pagesize = I('pagesize',10);
        $start = ($page - 1) * $pagesize;
        $limit = "{$start},{$pagesize}";
        $user_id = $this->wx_user_id;

        $listen_history_model = M('med_listen_history');


        //计算duration以前的时间戳
        $time_ago = time()-$duration*24*60*60;

        $where['cmf_med_listen_history.user_id'] = $user_id;
        $where['cmf_med_listen_history.createtime'] = array('egt',$time_ago);

        //返回的数据库字段
        $field = "
             cmf_med_audio.id audio_id,
             cmf_med_audio.audio_name,
             cmf_med_audio.audio_url,
             cmf_med_audio.audio_listen_count,
             IF(cmf_med_audio_like.user_id <> '',1,0) is_like
        ";
        $count = $listen_history_model->where($where)->count();

        $audio_list = $listen_history_model
            ->field($field)
            ->join("cmf_med_audio ON cmf_med_audio.id = cmf_med_listen_history.audio_id")
            ->join("cmf_med_audio_like ON cmf_med_audio_like.audio_id = cmf_med_audio.id and cmf_med_audio_like.user_id = {$user_id}","left")
            ->where($where)
            ->limit($limit)
            ->order("cmf_med_listen_history.createtime desc")
            ->select();

        //是否有下一页
        $hasNextPage = 0;
        if($page<($count/$pagesize)){
            $hasNextPage=1;
        }
        $data = array(
            'list'=>$audio_list,
            'page'=>$page,
            'pagesize'=>$pagesize,
            'count'=>$count,
            'hasNextPage'=>$hasNextPage
        );
        $this->returnMsg(0,"成功",$data);
    }

    /**
     * 专辑关注与取消关注
     * @interface 2.6
     * @return array returnMsg
     */
    public function album_like(){
        //参数
        $is_cancel = I('is_cancel');
        $album_id = I('album_id');
        $user_id = $this->wx_user_id;

        $album_like_model = M('med_user_like_album');
        $album_model = M('med_audio_album');

        if(empty($album_id)){
            $this->returnMsg(-2);
        }

        $where = array(
            'user_id'=>$user_id,
            'album_id'=>$album_id
        );
        $album_like_info = $album_like_model->field('album_id')->where($where)->find();
        $is_exist = $album_model->where(array('id'=>$album_id))->count();

        if(!$is_exist){
            $this->returnMsg(-1,"操作失败,该专辑不存在!");
        }

        //取消收藏
        if($is_cancel==1){
            if($album_like_info){
                $rs = $album_like_model->where($where)->delete();
                if($rs){
                    $this->returnMsg(0,"取消关注成功!");
                }else{
                    $this->returnMsg(-1,"取消关注失败!");
                }
            }else{
                $this->returnMsg(-1,"请勿重复取消关注!");
            }
        }else{
            if(!empty($album_like_info)){
                $this->returnMsg(-1,"请勿重复关注");
            }else{
                $add = array(
                    'album_id'=>$album_id,
                    'user_id'=>$user_id,
                    'createtime'=>time()
                );
                $rs = $album_like_model->add($add);
                if($rs){
                    $this->returnMsg(0,"关注成功!");
                }else{
                    $this->returnMsg(-1,"关注失败!");
                }
            }
        }

    }

    //处理音频路径
    public function handler_audio_url($audio_url){
        return C('HTTP_HEAD').$_SERVER['HTTP_HOST'].$audio_url;
    }

    //处理图片路径
    public function handler_img_url($img_url,$type){
        if($img_url){
            return C('HTTP_HEAD').$_SERVER['HTTP_HOST'].$img_url;
        }else{//如果没有,则显示默认图片
           if($type == "audio"){
               return C('HTTP_HEAD').$_SERVER['HTTP_HOST'].C('AUDIO_COVER_DEFAULT');
           }else if($type == "album"){
               return C('HTTP_HEAD').$_SERVER['HTTP_HOST'].C('ALBUM_COVER_DEFAULT');
           }
        }
    }




}