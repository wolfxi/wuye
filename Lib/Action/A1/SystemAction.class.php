<?php

class SystemAction extends CommonAction {

    public function index(){

		$this->display();

    }



    //账户信息
    public function account(){

        $at=M('Account');
        //$db_prefix=C('DB_PREFIX');
        import('ORG.Util.Page');// 导入分页类

        $data['group']='A2';
        $name=trim($_POST[account]);
        $nickname=trim($_POST[nickname]);
        if($_POST['account']){
            $data['username']=array('Like',"%{$name}%");
        }
        if($_POST['nickname']){
            $data['nickname']=array('LIKE',"%{$nickname}%");
        }
        $count= $at->where($data)->count();// 查询满足要求的总记录数

        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $result=$at->where($data)->order('status desc,uid desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        //echo $at->getlastsql();

        $this->assign("result",$result);
        $this->display();
    }
    //添加账户
    public function account_add(){

        $account=M("Account");
        if(IS_POST){
            if(empty($_POST['username'])){
                $this->ajaxReturn(array('msg'=>'登录账号不能为空!'),'JSON');
            }
            if($account->where("username='{$_POST[username]}'")->find()){
                $this->ajaxReturn(array('msg'=>'该账号已存在，请您更换账户名，重新建立'),'JSON');
            }

            else if(empty($_POST['nickname'])){
                $this->ajaxReturn(array('msg'=>'真实姓名不能为空!'),'JSON');
            }else if(empty($_POST['email'])){
                $this->ajaxReturn(array('msg'=>'邮箱不能为空'),"JSON");
            }else if($_POST['password']!=$_POST['repassword']){
                $this->ajaxReturn(array('msg'=>'两次输入密码不一致'),"JSON");
            }else{
                $wechat_id=$account->order('wechat_id desc')->getfield('wechat_id');
                $data['wechat_id']=$wechat_id+1;//微信ID

                $data['group']="A2";

                $data['username']=trim($_POST['username']);
                $data['nickname']=trim($_POST['nickname']);
                $data['email']=trim($_POST['email']);
                if($_POST['tel']){$data['tel']=$_POST['tel'];}
                if($_POST['status']){$data['status']=$_POST['status'];}else{$data['status']=0;}
                $data['password']=MD5(C('WMD5').$_POST['password']);
                if($_POST['remark']){$data['remark']=$_POST['remark'];}
                $result=$account->data($data)->add();
                if($result){
                    $w_data['id']=$data['wechat_id'];M('Wechat')->add($w_data);//添加微信
                    $this->ajaxReturn(array("status"=>1,"msg"=>"添加成功"));
                }else{
                    $this->ajaxReturn(array("msg"=>"添加失败".M()->getlastsql()));
                }
            }
        }
        $this->display();


    }

    //编辑账号信息ui
    public function account_edit(){
        $uid=$_GET['uid'];
        $aid=is_login();
        $at=M("Account");
        $mtp=M('Account_type');
        $result=$at->where("uid=%d",$uid)->find();
        $res=$mtp->where("id=%d",$result['type_wuye'])->find();
        $res1=$mtp->where("id=%d",$result['type_xiaoqu'])->find();
        $this->assign("res",$res);
        $this->assign("res1",$res1);
        $this->assign("result",$result);
        $this->display();
    }

    //删除账号
    public function account_del(){
        $id=$_POST['id'];
        $data['status']='-1';
        $at=M("Account");
        $result=$at->where("uid={$id}")->data($data)->save();
        if($result){
            $this->ajaxReturn(array("status"=>1,"msg"=>"删除成功"));
        }else{
            $this->ajaxReturn(array("msg"=>"删除失败"));
        }
    }
    //恢复账号
    public function account_huifu(){
        $id=$_POST['id'];
        $data['status']='1';
        $at=M("Account");
        $result=$at->where("uid={$id}")->data($data)->save();
        if($result){
            $this->ajaxReturn(array("status"=>1,"msg"=>"恢复成功"));
        }else{
            $this->ajaxReturn(array("msg"=>"恢复失败"));
        }
    }

    //编辑账号信息data
    public function account_edit_data(){
        $at=M("Account");
        if(IS_POST){
            $id=$_POST['id'];
            $password=$_POST['password'];
            $repassword=$_POST['repassword'];
            $data['tel']=$_POST['tel'];
            $data['status']=$_POST['status'];
            $data['email']=$_POST['email'];
            $data['remark']=$_POST['remark'];
            $data['nickname']=$_POST['nickname'];
            if(empty($_POST['nickname'])){
                $this->ajaxReturn(array('msg'=>"真实姓名不能为空"),"JSON");
            }else if(empty($_POST['email'])){
                $this->ajaxReturn(array('msg'=>'邮箱不能为空'),"JSON");
            }else if(empty($password)){
                $result=$at->where("uid=%d",$id)->data($data)->save();
                if($result){
                    $this->ajaxReturn(array('status'=>1,'msg'=>'编辑成功'),"JSON");
                }else{
                     $this->ajaxReturn(array('msg'=>'编辑失败'),"JSON");
                }
            }else if(!empty($password)){
                if(empty($repassword)){
                    $this->ajaxReturn(array('msg'=>"确认密码不能为空"),"JSON");
                }else if($password!=$repassword){
                    $this->ajaxReturn(array('msg'=>"两次输入密码不一致"),"JSON");
                }else{
                    $data['password']=MD5(C('WMD5').$_POST['password']);
                    $result=$at->where('uid=%d',$id)->data($data)->save();
                    $this->ajaxReturn(array('status'=>1,'msg'=>"编辑成功"),"JSON");
                }
            }
        }
    }

    //权限
    public function auth(){

        $uid=$_GET['uid'];
        $at=M("Account");
        $account=$at->where("uid=%d",$uid)->find();
        $this->assign("account",$account);

        //读取全部权限
        $ag=M('AuthGroup');
        $group_data=$ag->where('status=1 AND `group`="A2"')->group('type')->order('id asc')->field('type')->select();
        foreach ($group_data as $k=>$v) {
            if(!empty($v['type'])){
                $group_list[$k]['name']=$v['type'];
                $group_list[$k]['list']=$ag->where("type='{$v[type]}' and status=1 AND `group`='A2'")->order('id asc')->select();
            }
        }
        $this->assign('group_list',$group_list);

        //读取权限列表
        $aga=M('AuthGroupAccess');
        $group_all=$aga->where("uid={$account[uid]}")->Field('group_id')->select();//获得用户组
        foreach ($group_all as $v) {
            $group[]=$v['group_id'];
        }
        $this->assign('group',$group);

        $this->display();
    }
    //权限分配
    public function auth_updata(){

        $data['uid']=$_POST['uid'];
        $aga=M('AuthGroupAccess');
        $aga->where("uid={$data[uid]}")->delete();
        foreach ($_POST['menu'] as $v) {
            $data['group_id']=$v;
            $aga->add($data);
        }

        $this->ajaxReturn(array('status'=>1,'msg'=>"操作成功"),"JSON");

    }



}