<?php

class SystemAction extends CommonAction {

    public function index(){

		$this->display();

    }
    //账户信息
    public function account(){

        $at=M('Account');
        $id=is_login();
        $db_prefix=C('DB_PREFIX');
        import('ORG.Util.Page');// 导入分页类
        $count= $at->where("fid_a3={$id}")->count();// 查询满足要求的总记录数
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $mtp=M('Account_type');
        $result=$at->where("fid_a3=%d",$id)->limit($Page->firstRow.','.$Page->listRows)->select();

        $res=$mtp->select();
        $this->assign("result",$result);
        $this->assign('res',$res);
        $this->display();
    }
    //添加账户
    public function account_add(){

        $at=M('AccountType');
        $account=M("Account");
        $aid=is_login();
        $wuye=$at->where("aid={$aid} and fid=0")->order('id desc')->select();
        $xiaoqu=$at->where("aid={$aid} and fid=1")->order('id desc')->select();
        if(IS_POST){
            if(empty($_POST['nickname'])){
                $this->ajaxReturn(array('msg'=>'真实姓名不能为空!'),'JSON');
            }else if(empty($_POST['email'])){
                $this->ajaxReturn(array('msg'=>'邮箱不能为空'),"JSON");
            }else if($_POST['password']!=$_POST['repassword']){
                $this->ajaxReturn(array('msg'=>'两次输入密码不一致'),"JSON");
            }else{
               $data['username']=$_POST['username'];
               if($_POST['group']==1){//添加的是小区管理员
                    $data['group']='A3';
                    $data['ctype']=1;
               }else{//添加的是小区员工
                    $data['group']='A3';
                    $data['ctype']=2;
               }
              if($_POST['status']==1){
                    $data['status']=1;
              }else{
                    $data['status']=0;
              }
               $data['fid_a3']=$aid;
               $data['type_wuye']=$_POST['type_wuye'];
               $data['type_xiaoqu']=$_POST['type_xiaoqu'];
               $data['password']=MD5(C('WMD5').$_POST['password']);
               $data['nickname']=$_POST['nickname'];
               $data['tel']=$_POST['tel'];
               $data['email']=$_POST['email'];
               $data['remark']=$_POST['remark'];
               if(empty($_POST['id'])){//添加
                    $result=$account->data($data)->add();
                    if($result){
                        $this->ajaxReturn(array('status'=>1,'msg'=>'操作成功'),'JSON');
                    }else{
                        $this->ajaxReturn(array('status'=>0,'msg'=>'添加失败'),'JSON');
                    }
               }else{//编辑
                    $result=$account->where('id=%d',$_POST['id'])->data($data)->save();
                    if($result){
                        $this->ajaxReturn(array('status'=>1,'msg'=>'操作成功'),'JSON');
                    }else{
                        $this->ajaxReturn(array('status'=>0,'msg'=>'编辑失败'),'JSON');
                    }
               }
            }
        }
        $this->assign('wuye',$wuye);
        $this->assign('xiaoqu',$xiaoqu);
        $this->display();

    }
    //物业列表
    public function wuye(){

    	$at=M('AccountType');
    	$aid=is_login();

    	$wuye=$at->where("aid={$aid} and fid=0")->order('id desc')->select();

    	$this->assign('wuye',$wuye);
		$this->display();

    }
    //添加 编辑 物业
    public function wuye_add(){

        $at=M('AccountType');
        $aid=is_login();

    	if(IS_POST){

	    	if(empty($_POST['name'])){$this->ajaxReturn(array('msg'=>'物业公司名称不能为空'),'JSON');}
	    	
	    	$at->create();
	    	$at->aid=$aid;
            if($_POST['new_account']==1){
                $at->new_account=1;
            }else{
                 $at->new_account=0;
            }
            if($_POST['status']==1){
                $at->status=1;
            }else{
                 $at->status=0;
            }
            if(empty($_POST['id'])){
                $at->add();//新增
            }else{
                $at->save();//更新
            }

	    	$this->ajaxReturn(array('status'=>1,'msg'=>'操作成功'),'JSON');
	    	

    	}

        if(isset($_GET['id'])){

            $wuye=$at->where("aid={$aid} and id={$_GET[id]}")->find();
            $this->assign('wuye',$wuye);
            $this->assign('title','编辑物业');
        }else{
            $this->assign('title','添加物业');
        }
		$this->display();

    }
    //小区列表
    public function xiaoqu(){

        $fid=$_GET['fid'];
        $aid=is_login();
        $at=M('AccountType');
        //获取所属物业
        $name=$at->where("aid={$aid} and id={$fid}")->getfield('name');
        $this->assign('name',$name);

        
        $xiaoqu=$at->where("aid={$aid} and fid={$fid}")->order('id desc')->select();

        $this->assign('xiaoqu',$xiaoqu);
        $this->display();

    }
    //添加小区
    public function xiaoqu_add(){

        $fid=$_GET['fid'];
        $aid=is_login();
        $at=M('AccountType');
        //获取所属物业
        $name=$at->where("aid={$aid} and id={$fid}")->getfield('name');
        $this->assign('name',$name);

        if(IS_POST){

            if(empty($_POST['name'])){$this->ajaxReturn(array('msg'=>'小区名称不能为空'),'JSON');}
            $at->create();
            $at->aid=$aid;
            if($_POST['status']==1){
                $at->status=1;
            }else{
                 $at->status=0;
            }
            if(empty($_POST['id'])){
                $at->add();//新增
            }else{
                $at->save();//更新
            }

            $this->ajaxReturn(array('status'=>1,'msg'=>'操作成功'),'JSON');
            

        }

        if(isset($_GET['id'])){

            $xiaoqu=$at->where("aid={$aid} and id={$_GET[id]}")->find();
            $this->assign('xiaoqu',$xiaoqu);

            $this->assign('title','编辑小区');
        }else{
            $this->assign('title','添加小区');
        }
        $this->display();

    }

    //编辑账号信息ui
    public function user_edit(){
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

    //编辑账号信息data
    public function user_edit_data(){
        $at=M("Account");
        if(IS_POST){
            $id=$_POST['id'];
            if($_POST[''])
            $password=$_POST['password'];
            $repassword=$_POST['repassword'];
            $data['tel']=$_POST['tel'];
             if($_POST['group']==1){//添加的是小区管理员
                    $data['group']='A3';
                    $data['ctype']=1;
               }else{//添加的是小区员工
                    $data['group']='A3';
                    $data['ctype']=2;
               }
              if($_POST['status']==1){
                    $data['status']=1;
              }else{
                    $data['status']=0;
              }
            $data['email']=$_POST['email'];
            $data['remark']=$_POST['remark'];
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

    //归属物业表单
    public function wuyeselform(){
        $mt=M("Message_type");
        $form=C('FORUM_LIST.WY_FORM');
        $aid=is_login();



        $atp=M("Account");
        //所属a3的小区管理员
        $where3['fid_a3']=array("IN",$aid);
        $aid4=$atp->where($where3)->getField("uid");

        //条件
        $wheres['uid']=array("IN",$aid4);
        $wheres['aflag']=array("EQ",$form);


        import('ORG.Util.Page');// 导入分页类
        $count= $mt->where($wheres)->count();// 查询满足要求的总记录数
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出



        $res=$mt->where($wheres)->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach ($res as $key => $one) {
            $res[$key]['account']=$atp->where("uid = %d",$one['uid'])->find();
        }

        $this->assign('res',$res);
        $this->display();
    }

    //归属物业表单数据列表
    public function wuyeformmodiled(){
        $id=$_GET['id'];
        $modelist=M("Message");

        import('ORG.Util.Page');// 导入分页类
        $count= $modelist->where("aid={$id}")->count();// 查询满足要求的总记录数
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $result=$modelist->where("aid='%d' AND is_delete=%d ",$id,0)->limit($Page->firstRow.','.$Page->listRows)->select();

        foreach ($result as $key => $value) {
            $result[$key]['form_content']=unserialize($value['form_content']);
        }

        $res=$modelist->where("aid='%d' AND is_delete=%d ",$id,0)->getField("form_content");
        $for=unserialize($res);

        $formdisplay=M("Form");
        $formresult=$formdisplay->where("aid='%d' AND is_display=%d",$id,1)->select();
        foreach ($formresult as $key => $value) {
            $formresult[$key]['options']=unserialize($value['options']);
        }
        //这里还差模型选项的内容，当显示选项列表的时候出现的内容

        $this->assign("formresult",$formresult);
        $this->assign("for",$for);
        $this->assign("result",$result);
        $this->display();

    }

     //编辑物业表单
    public function elseModelEditUi(){
        $id=$_GET['id'];
        $eidtmodel=M("Message");
        $aid=$_SESSION['modelidlistid'];
        $result=$eidtmodel->where("id=%d",$id)->find();
        $formmoeld=M("form");
        $form_model=$formmoeld->where("aid = %d",$aid)->select();
        foreach ($form_model as $key => $value) {
            $form_model[$key]['options']=unserialize($value['options']);
        }
        $result1=$result['form_content'];
        $form_content=unserialize($result1);
        $this->assign("form_model",$form_model);
        $this->assign("form_content",$form_content);
        $this->assign("result",$result);
        $this->display('elseModelEdit');
    }

     //编辑选项模型data
    public function editelseModel(){
        $mid=$_POST['mid'];
        $s=$_POST['model'];
        $options=serialize($s);
        $id=$_SESSION['modelidlistid'];
        $data['account']="匿名";
        $data['handle_status']=$_POST['handle_status'];
        $data['assess']=$_POST['assess'];
        $data['remark']=$_POST['remark'];
        $data['post_time']=date("Y-m-d H:m:s",time());
        $data['aid']= $id;
        $data['id']=$mid;
        $data['form_content']=$options;
        $message=M("Message");
        $result=$message->where("id=%d",$mid)->data($data)->save();
        if($result){
            $this->success("编辑成功");
        }else{
            $this->error("编辑失败");
        }
    }


     //删除选项
    public function del_elseModel(){
        $id=$_GET['id'];
        $delmodel=M("Message");
        $result=$delmodel->where("id=%d",$id)->delete();
        if($result){
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }

    }





 //修改资料
    public function simplify(){
        $id=$_GET['id'];
        $mt=M("Account");
        $result=$mt->where("uid={$id}")->find();

        $this->assign("result",$result);
        $this->display();
    }


    //修改资料data
    public function simpledata(){
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