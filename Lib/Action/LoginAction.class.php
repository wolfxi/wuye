<?php

class LoginAction extends Action {
    
    public function login(){

        if(IS_POST or isset($_GET['autologin'])){

            $au=M('Account');

            
            if(isset($_GET['autologin'])){//自动登录

                $user=$au->where("md5(Concat(username,password))='$_GET[autologin]'")->field('uid,username,status,group,fid_a2,type_wuye,type_xiaoqu')->find();

            }else{//平常登录
                if(empty($_POST['username'])) {
                    $this->error('请输入账户');
                }elseif (empty($_POST['password'])){
                    $this->error('请输入密码');
                }

                $data['username']=array('eq',$_POST['username']);
                $data['password']=array('eq',MD5(C('WMD5').$_POST['password']));
                $user=$au->where($data)->field('uid,username,status,group,fid_a2,type_wuye,type_xiaoqu')->find();
            }

            if(!$user){
                $this->error('账户或密码错误');
            }

            if($user['group']!=GROUP_NAME){
                $this->error('您无权限');
            }
            if($user['status']!=1){
                $this->error('您的账户已被锁定或删除');
            }
            //检查上级是否被删除或锁定
            if($user['group']=='A3' or $user['group']=='A4'){
                if(!$au->where("uid={$user[fid_a2]} and status=1")->find()){
                    $this->error('您的上级代理商已被锁定或删除');
                }
            }

            if(isset($_GET['autologin']) and GROUP_NAME=='A4'){//自动登录
                session('QUERY_LOGIN',1);
            }else{
                session('QUERY_LOGIN',null);
            }

            session(C('SysUserSessionUid'),$user['uid']);
            session(C('SysUserSessionName'),$user['username']);
            session(C('SysUserAuth'),data_auth_sign($user['uid']));
            if($user['type_wuye']){
                session('WY_ADMIN_WID',$user['type_wuye']);
            }
            if($user['type_xiaoqu']){
                session('WY_ADMIN_XID',$user['type_xiaoqu']);
            }

            if(is_login()){
                redirect(U('Index/index'));
            }else{
                $this->error('登陆失败');
            }
        }
        $this->display();

    }

    public function logout() {
        if(isset($_SESSION[C('SysUserSessionName')])) {
            session_destroy();
            $this->redirect('Public/login');
        }
    }
    
    Public function verify(){

        $type=isset($_GET['type'])?$_GET['type']:'gif';
        import("ORG.Util.Image");
        Image::buildImageVerify(4,1,$type);

    }


}
