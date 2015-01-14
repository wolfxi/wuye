<?php

class IndexAction extends CommonAction {
    public function index(){

		$this->display();

    }
	
	

 //修改资料
    public function user_edit(){
        $id=$_GET['id'];
        $mt=M("Account");
        $result=$mt->where("uid={$id}")->find();

        $this->assign("result",$result);
        $this->display();
    }

    //修改资料data
    public function user_edit_data(){
        $at=M("Account");
        if(IS_POST){
            $id=$_POST['id'];
            $password=$_POST['password'];
            $repassword=$_POST['repassword'];
            $data['tel']=$_POST['tel'];
            $data['email']=$_POST['email'];
            $data['remark']=$_POST['remark'];
            $data['status']=$_POST['status'];
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


}
