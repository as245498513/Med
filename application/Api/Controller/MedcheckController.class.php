<?php
/**
 * Created by PhpStorm.
 * User: CMJ
 * Date: 2017/9/5
 * Time: 11:40
 */
namespace Api\Controller;
use Think\Controller;

class MedcheckController extends Controller{

    /**
     * 检查微信用户是否已经授权
     * @interface 3.1
     * @return array returnMsg
     */
    public function wx_user_is_auth(){
        //参数
        $wechat_id = I('wechat_id');
        if(empty($wechat_id)){
            $this->returnMsg(-2);
        }

        $wx_users_model = M('med_wx_users');

        $where['wx_wechat_id'] = $wechat_id;
        $user_id = $wx_users_model->field('id')->where($where)->find();

        if($user_id['id']){
            $rs = $wx_users_model->where(array('id'=>$user_id['id']))->save(array('last_login_time'=>time()));
            if($rs){
                $this->returnMsg(0,"已授权");
            }else{
                $this->returnMsg(-3,"数据库错误");
            }

        }else{
            $this->returnMsg(-1,"未授权");
        }

    }

    /**
     * 微信用户授权
     * @interface 3.2
     * @return array returnMsg
     */
    public function wx_user_auth(){
        //参数
        $wechat_id = I('wechat_id');
        $open_id = I('open_id');
        $nickname = I('nickname');

        $wx_users_model = M('med_wx_users');

        if(empty($wechat_id)||empty($nickname)||empty($open_id)){
            $this->returnMsg(-2);
        }

        $user_id = $wx_users_model->field('id')->where(array('wx_wechat_id'=>$wechat_id))->find();
        if($user_id){
            $this->returnMsg(-1,"请勿重复授权");
        }else{
            $now_time = time();
            $add = array(
                'wx_wechat_id'=>$wechat_id,
                'wx_openid'=>$open_id,
                'wx_nickname'=>$nickname,
                'last_login_time'=>$now_time,
                'createtime'=>$now_time
            );
            $rs = $wx_users_model->add($add);
            if($rs){
                $this->returnMsg(0,"授权成功");
            }else{
                $this->returnMsg(-1,"授权失败");
            }
        }

    }

    public function returnMsg($code,$message=null,$data=null){
        //常用
        switch ($code) {
            case -2:
                $d_message = "参数错误";
                break;
            case -1:
                $d_message = "失败";
                break;
            case 0:
                $d_message = "成功";
                break;

            default:
                # code...
                break;
        }
        $message = $message ? $message : $d_message;

        $return = array(
            'code'=>$code,
            'message'=>$message,
            'data'=>$data,
        );
        $this->ajaxReturn($return);
    }




}