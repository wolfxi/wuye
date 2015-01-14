<?php

class CommonAction extends Action {

    public function _initialize(){

    	if(isset($_GET['openid'])){



            $u=M('User');
            $user=$u->where(array('openid'=>$_GET['openid']))->Field('id,openid,xiaoqu_id,propertyid,openidWX')->find();

            if(!$user){
                $weixin=M('Wechat')->where("wid='{$_GET[openidWX]}'")->find();

                import('COM.ThinkWechat');
                C('WECHAT_TOKEN', $weixin['wtoken']);
                C('WECHAT_APPID',$weixin['wappid']);
                C('WECHAT_APPSECRET',$weixin['wsecret']);
                $weixin = new ThinkWechat ();
                $data=$weixin->user($_GET['openid']);
                dump($data);
                
                if($data['subscribe']==1){//有关注，则添加到数据库里
                    $user['openid']=$_GET['openid'];
                    $user['openidWX']=$_GET['openidWX'];
                    $user['weixin_name']=$data['nickname'];
                    $user['weixin_from']=$data['country'].'_'.$data['province'].'_'.$data['city'];
                    $user['weixin_sex']=$data['sex'];
                    $user['status']=1;
                    $user['addtime']=$data['subscribe_time'];
                    $user['id']=$u->add($user);
                }else{
                    header("Content-Type: text/html; charset=UTF-8");
                    echo '您尚未关注本账户，请重新关注本公众号。';exit;
                }
                
            }

            session("WY_UID",$user['id']);//用户ID
            session("WY_XID",$user['xiaoqu_id']);//小区ID
            session("WY_FID",$user['propertyid']);//房号ID
            session("WY_OPENID",$user['openid']);//房号ID

            $wechat_id=M('Wechat')->where("wid='{$user[openidWX]}'")->getfield('id');

            session("WY_WID",$wechat_id);//微信ID
            session("WY_DID",M('Account')->where("wechat_id={$wechat_id} and `group`='A2'")->getfield('uid'));//代理商ID

            //检查是否选择小区
            if(empty($user['xiaoqu_id'])){
                $this->redirect('User/xiaoqu_select');
            }

            /*
            //检查是否绑定房号
            $arr=array('usercenter','news','repair','jianyi','introduce','interaction','tejia','fuwehome','yezhu','commitrepair','toushu');
            if(empty($user['propertyid']) AND in_array(ACTION_NAME, $arr)){
                $this->redirect(MODULE_NAME.'/chosexiaoqu');
            }
            */


            $this->redirect(MODULE_NAME.'/'.ACTION_NAME);
    	}

    }

    public function wechat_send($openid,$msg){

        $wid=$_SESSION['WY_WID'];
        $weixn=M('Wechat')->where("id={$wid}")->find();

        import('COM.ThinkWechat');

        C('WECHAT_TOKEN', $weixn['wtoken']);
        C('WECHAT_APPID',$weixn['wappid']);
        C('WECHAT_APPSECRET',$weixn['wsecret']);
        $weixin = new ThinkWechat ();
        if(empty($openid)){
            return array('status'=>0,'msg'=>'请选择要发送的微信用户');
        }
        if(empty($msg)){
            return array('status'=>0,'msg'=>'请输入要发送的内容');
        }
        $msg=$weixin->sendMsg($msg,$openid);
        return json_decode($msg,true);

    }

/**
     * ajax 图片上传 
     */
    public function ajaxupload(){
        if(IS_POST){
            $fileresult=$this->upload_img();
            if($fileresult && count($fileresult)>0){
                $message['flag']=1;
                $message['msg']=$fileresult[0]['savepath'].$fileresult[0]['savename'];
            }else{
                $message['flag']=0;
                $message['msg']="图片上传失败！！！";
            }
            $this->ajaxReturn($message);    
        }else{
            exit();
        }
    }



/**返回上传图片的信息
     * 返回的是一个关联数组
     * 包含了文件名和文件的路劲               
     */
    public function upload_img(){
        $file=$_FILES;
        if($file && count($file)>0){
            import('ORG.Net.UploadFile');
            $upload = new UploadFile();// 实例化上传类
            $upload->maxSize  = 3145728  ;// 设置附件上传大小150kb
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath  =     C("UPLOADIMG_DIR");; // 设置附件上传目录
            $upload->rootPath  =     '';
            // 上传文件 
            $result   =   $upload->upload();
            if(!$result) {// 上传错误提示错误信息
                return false;
            }else{// 上传成功 获取上传文件信息
                return $upload->getUploadFileInfo();
            }
        }else{
            $file=array();
            return $file;
        }

    }



    

}