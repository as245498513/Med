<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Common\Lib\UploadHandler;


/**
 * 音频处理控制器
 * User: CMJ
 * Date: 2017/8/16
 * Time: 13:36
 */

class AudioController extends AdminbaseController{

    public function index(){
        $audio_album_model = M('med_audio_album');

        $album_list = $audio_album_model->select();

        $this->assign('album_list',$album_list);
        $this->display();
    }

    /**
     * 上传处理
     */
    public function check_audio_isupload(){
        $audio_name = I("audio_name");

        $audio_model = M('med_audio');

        if($audio_model->where(array('audio_name'=>$audio_name))->count()){
            $return  = array('code'=>1,'message'=>'音频文件已存在!');
        }
        $this->ajaxReturn($return);
    }

    /**
     * 上传处理
     */
    public function uploadHandler(){
        new UploadHandler();
    }

    /**
     * 上传页面显示
     */
    public function audio_upload_index(){
        $this->display("audio_upload_index");
    }

    /**
     * 处理上传信息
     */
    public function audio_info_post(){
        //参数
        $audio_name = I('audio_name');
        $audio_reciter = I('audio_reciter');
        $audio_description = I('audio_description');
        $audio_size = I('audio_size');
        $photos_url = I('photos_url');
        $audio_duration = I('audio_duration');
        $album_ids = I('album_ids');
        $difficlety = I('difficlety');
        $edit_id = I('edit_id');

        $audio_model = M('med_audio');
        $audio2album_model = M('med_audio_to_album');

        //检查参数
        if(empty($difficlety)){
            $this->error("难度系数为必选项!");
        }
        if(empty($audio_name)||$audio_size<=0||$audio_duration<=0||empty($album_ids)){
              $this->error("音频有误,请重新上传!");
        }
        if($photos_url[0]){
            $audio_cover = C('IMG_UPLOAD_PATH').$photos_url[0];
        }else{
            $audio_cover = '';
        }


        if(IS_POST){
            try{
                $audio_model->startTrans();

                $is_exist = $audio_model->where(array('audio_name'=>$audio_name,'audio_size'=>$audio_size,'audio_duration'=>$audio_duration))->count();
                if($is_exist){
                    $this->error("该音频已存在,请勿重复提交!");
                }

                $add = array(
                    'audio_name'=>$audio_name,
                    'audio_size'=>$audio_size,
                    'audio_duration'=>$audio_duration,
                    'audio_description'=>$audio_description,
                    'audio_reciter'=>$audio_reciter,
                    'audio_cover'=>$audio_cover,
                    'audio_url'=>C('AUDIO_UPLOAD_PATH').$audio_name,
                    'audio_difficulty'=>$difficlety,
                    'createtime'=>time()
                );
                //是否编辑
                if($edit_id){
                    $rs = $audio_model->where(array('id'=>$edit_id))->save($add);
                    if($rs){
                        //删除原来所选分类
                        $audio2album_model->where(array('audio_id'=>$edit_id))->delete();

                        foreach($album_ids as $key=>$value){
                            $audio2album_model->add(array('audio_id'=>$edit_id, 'album_id'=>$value,'createtime'=>time()));
                        }
                        $audio_model->commit();
                        $this->success("音频编辑成功!");
                    }else{
                        $this->error("音频编辑失败!");
                    }
                }else{
                    $rs = $audio_model->add($add);
                    if($rs){
                        foreach($album_ids as $key=>$value){
                            $audio2album_model->add(array('audio_id'=>$rs, 'album_id'=>$value,'createtime'=>time()));
                        }
                        $audio_model->commit();
                        $this->success("音频添加成功!");
                    }else{
                        $this->error("音频添加失败!");
                    }
                }
            }catch(Exception $e){
                $audio_model->rollback();
                $this->error("音频添加失败!");
            }
        }else{
            $this->error("提交失败!");
        }

    }
    /**
     * 新增
     * 专辑分类
     * 页面显示
     */
    public function audio_album_add(){
          $this->display();
    }
    /**
     * 编辑
     * 专辑分类
     * 页面显示
     */
    public function audio_album_edit(){
        //参数
        $id = I('id');
        $info = M('med_audio_album')->where(array('id'=>$id))->find();
        if($info){
            $this->assign("info",$info);
        }else{
            $this->error("编辑错误!");
        }
        $this->display("audio_album_add");
    }

    /**
     * 新增提交
     * 编辑提交
     * 专辑分类
     */
    public function audio_album_post(){
        //参数
        $album_name = trim(I('album_name'));
        $photos_url = I('photos_url');
        $audio_album_model = M('med_audio_album');

        $edit_id = I('edit_id');

        if(IS_POST){
            if(empty($album_name)){
                $this->error("专辑名称不能为空!");
            }
            if($photos_url){
                $photos_url = C('IMG_UPLOAD_PATH').$photos_url[0];
            }

            $add = array(
                'album_name'=>$album_name,
                'album_cover'=>$photos_url,
                'createtime'=>time()
            );
            //编辑
            if($edit_id){
                $rs = $audio_album_model->where(array('id'=>$edit_id))->save($add);
                if($rs){
                    $this->success("专辑编辑成功!");
                }else{
                    $this->error("专辑编辑失败!");
                }
            }else{//添加
                $rs = $audio_album_model->add($add);
                if($rs){
                    $this->success("专辑添加成功!");
                }else{
                    $this->error("专辑添加失败!");
                }
            }
        }else{
            $this->error("提交失败!");
        }
    }

    /**
     * 删除
     * 专辑分类
     */
    public function audio_album_del(){
         //参数
        $album_id = I('id');
        $audio_album_model = M('med_audio_album');
        $audio_to_album_model = M('med_audio_to_album');
        $user_like_album_model = M('med_user_like_album');
        $album_listen_history_model = M('med_album_listen_history');


        if(empty($album_id)){
            $return  = array('code'=>-1,'message'=>'参数错误');
        }else{
            $cover_url = $audio_album_model->where(array('id'=>$album_id))->getField('album_cover');
            //判断专辑是否有关联,有则不允许删除专辑名称
            $is_exist1 = $audio_to_album_model->where(array('album_id'=>$album_id))->count();
            $is_exist2 = $user_like_album_model->where(array('album_id'=>$album_id))->count();
            $is_exist3 = $album_listen_history_model->where(array('album_id'=>$album_id))->count();
            if($is_exist1||$is_exist2||$is_exist3){
                $return  = array('code'=>-3,'message'=>'删除的专辑分类存在关联,不允许删除!');
            }else{
                //删除数据库信息
                $rs = $audio_album_model->where(array('id'=>$album_id))->delete();
                //删除图片
                if($rs){
                    if($cover_url){
                        unlink(".".$cover_url);
                    }
                    $return  = array('code'=>0,'message'=>'删除成功','data'=>$rs);
                }else{
                    $return  = array('code'=>-1,'message'=>'删除失败');
                }
            }
        }
        $this->ajaxReturn($return);
    }

    /**
     * 列表
     * 专辑分类
     */
    public function audio_album_list(){
        //参数
        $album_name = I('album_name');
        $start_time = I('start_time');
        $end_time   = I('end_time');
        $audio_album_model = M('med_audio_album');

        if($album_name){
            $where['album_name'] = array('like','%'.$album_name.'%');
        }

        //时间筛选
        if($start_time){
            $start_time_st =  strtotime($start_time);
            $where['createtime'] = array('egt',$start_time_st);
        }
        if($end_time){
            $end_time_st =  strtotime($end_time)+60;
            $where['createtime'] = array('elt',$end_time_st);
        }
        if($start_time&&$end_time){
            $start_time_st =  strtotime($start_time);
            $end_time_st =  strtotime($end_time)+60;
            $where['createtime'] = array('between',array($start_time_st,$end_time_st));
        }

        //分页
        $count = $audio_album_model->where($where)->count();
        $page = $this->page($count, 15);

        $list = $audio_album_model->where($where)->limit($page->firstRow,$page->listRows)->order('createtime desc')->select();

        $this->assign('list',$list);
        $this->assign("page", $page->show('Admin'));
        $this->assign("start_time",$start_time);
        $this->assign("end_time",$end_time?$end_time:date('Y-m-d H:i',NOW_TIME));
        $this->assign("album_name",$album_name);
        $this->display();
    }

    /**
     * 列表
     * 音频上传
     */
    public function audio_upload_list(){
        //参数
        $audio_name = I('audio_name');
        $album_name = I('album_name');
        $audio_reciter = I('audio_reciter');
        $start_time = I('start_time');
        $end_time   = I('end_time');

        $med_audio_to_album_model = M('med_audio_to_album');
        $audio_model = M('med_audio');

        if($audio_name){
            $where['audio_name'] = array('like','%'.$audio_name.'%');
        }

        if($audio_reciter){
            $where['audio_reciter'] = array('like','%'.$audio_reciter.'%');
        }
        if($album_name){
            $join_where["cmf_med_audio_album.album_name"] = array('like','%'.$album_name.'%');
            $audio_id_list = $med_audio_to_album_model
                             ->field("cmf_med_audio_to_album.audio_id")
                             ->join("cmf_med_audio_album ON cmf_med_audio_album.id = cmf_med_audio_to_album.album_id","left")
                             ->where($join_where)
                             ->group("cmf_med_audio_to_album.audio_id")
                             ->select();
            $len = count($audio_id_list);
            if($len==0){
                $where['id'] = 0;
            }else if($len==1){
                $where['id'] = intval($audio_id_list[0]['audio_id']);
            }else{
                $in_str = array();
                foreach($audio_id_list as $key=>$value){
                    $in_str[] = $value['audio_id'];
                }
                $where['id'] = array('in',implode(',',$in_str));
            }
        }

        //时间筛选
        if($start_time){
            $start_time_st =  strtotime($start_time);
            $where['createtime'] = array('egt',$start_time_st);
        }
        if($end_time){
            $end_time_st =  strtotime($end_time)+60;
            $where['createtime'] = array('elt',$end_time_st);
        }
        if($start_time&&$end_time){
            $start_time_st =  strtotime($start_time);
            $end_time_st =  strtotime($end_time)+60;
            $where['createtime'] = array('between',array($start_time_st,$end_time_st));
        }

        //分页
        $count = $audio_model->where($where)->count();
        $page = $this->page($count, 15);
        $list = $audio_model->where($where)->limit($page->firstRow,$page->listRows)->order('createtime desc')->select();
        foreach($list as $key=>$value){
            $album_list = $med_audio_to_album_model
                          ->field("cmf_med_audio_album.album_name")
                          ->join("cmf_med_audio_album ON cmf_med_audio_album.id = cmf_med_audio_to_album.album_id")
                          ->where(array("cmf_med_audio_to_album.audio_id"=>$value['id']))
                          ->select();
            $list[$key]["album_list"] = $album_list;
        }
        $this->assign('list',$list);
        $this->assign("page", $page->show('Admin'));
        $this->assign("start_time",$start_time);
        $this->assign("end_time",$end_time?$end_time:date('Y-m-d H:i',NOW_TIME));
        $this->assign("audio_name",$audio_name);
        $this->assign("audio_reciter",$audio_reciter);
        $this->assign("album_name",$album_name);
        $this->display();
    }

    /**
     * 编辑
     * 音频信息
     * 页面显示
     */
    public function audio_info_edit(){
        //参数
        $id = I('id');
        $med_audio_to_album_model = M('med_audio_to_album');
        $audio_album_model = M('med_audio_album');


        $info = M('med_audio')->where(array('id'=>$id))->find();

        if($info){
            //音频长度转化显示
            if($info['audio_duration']>60){
                 $min = floor($info['audio_duration']/60); //分
                 $sec = $info['audio_duration']%60; //秒
                $info['audio_duration_str'] = $min."分".$sec."秒";
                }else{
                $info['audio_duration_str'] = $info['audio_duration']+"秒";
            }
            //已选择的专辑分类
            $album_ids_checekd = $med_audio_to_album_model
                ->field("cmf_med_audio_album.id")
                ->join("cmf_med_audio_album ON cmf_med_audio_album.id = cmf_med_audio_to_album.album_id")
                ->where(array("cmf_med_audio_to_album.audio_id"=>$info['id']))
                ->select();
            $ids_arr = array();
            foreach($album_ids_checekd as $key=>$value){
                $ids_arr[] = $value['id'];
            }
            $info["album_ids_checekd"] = implode(",",$ids_arr);
            $info["album_list"] = $audio_album_model->field("id,album_name")->select();
            $this->assign("info",$info);
        }else{
            $this->error("编辑错误!");
        }
        $this->display("index");
    }

    /**
     * 删除
     * 专辑分类
     */
    public function audio_info_del(){
        //参数
        $audio_id = I('id');
        $audio_model = M('med_audio');
        $med_audio_to_album_model = M('med_audio_to_album');
        if(empty($audio_id)){
            $this->error("参数错误");
        }else{
            try{
                $audio_model->startTrans();
                $info = $audio_model->field("audio_cover,audio_url")->where(array('id'=>$audio_id))->find();

                //删除数据库信息
                $rs = $audio_model->where(array('id'=>$audio_id))->delete();

                //删除图片和音频
                $audio_url = iconv('utf-8', 'gbk', $info['audio_url']);

                if($rs&&unlink(".".$audio_url)){
                    //如果封面存在则将他删除
                    if($info['audio_cover']){
                        unlink(".".$info['audio_cover']);
                    }
                    $med_audio_to_album_model->where(array('audio_id'=>$audio_id))->delete();
                    $audio_model->commit();
                    $return  = array('code'=>0,'message'=>'删除成功','data'=>$rs);
                }else{
                    $audio_model->rollback();
                    $return  = array('code'=>-1,'message'=>'删除失败','data'=>$rs);
                }
            }catch(Exception $e){
               $audio_model->rollback();
                    $return  = array('code'=>-1,'message'=>'删除失败','data'=>$rs);
            }finally{
                $this->ajaxReturn($return);
            }
        }
    }


}