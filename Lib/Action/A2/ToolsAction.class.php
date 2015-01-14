<?php

class ToolsAction extends CommonAction {

    public function index(){

		$this->display();

    }

    //常用电话
    public function commmontel(){

        $id=is_login();
        $at=M("Account_type");
        //$res=$at->where("aid='%d'",$id)->getField('id',true); 

        $where['aid']=$id;
        $t=M("Tool");
        if($_GET['id']){
            $tel_edit=$t->where("id=%d",$_GET['id'])->find();
            $this->assign("tel",$tel_edit);
        }
        //$where['atid']=array("IN",$res);
        $where['type']=1;
        $name=trim($_POST['account']);
        if($_POST['account']){
           $where['name']=array('Like',"%{$name}%");
        }
        if($_POST['xiaoquselect']){
            if($_POST['xiaoquselect']!= -1){
                  $where['atid']=array("EQ",$_POST['xiaoquselect']);
            }        
        }
        if(isset($_GET['xiaoquselect'])){
           if($_GET['xiaoquselect']!= -1){
             $where['atid']=array("EQ",$_GET['xiaoquselect']);
           }
        }
         import('ORG.Util.Page');// 导入分页类
        
        $count= $t->where($where)->count();// 查询满足要求的总记录数
        $Page= new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $result=$t->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('top_if desc,tel desc,id desc')->select();
   
        $xiaoqu=$at->where("aid='{$id}' AND fid !=0")->select();
        $this->assign("xiaoqu",$xiaoqu);
        $this->assign("result",$result);
       $this->display();
    }

     //常用电话
    public function tel_edit(){
        $id=is_login();
        $at=M("Account_type");
        $t=M("Tool");
        if($_GET['id']){
            $tel_edit=$t->where("id=%d",$_GET['id'])->find();
            $result=$at->where("id=%d",$tel_edit['atid'])->getField("name");
            $this->assign("name",$result);
            $this->assign("tel",$tel_edit);
        }
        $xiaoqu=$at->where("aid='{$id}' AND fid !=0")->select();
        $this->assign("xiaoqu",$xiaoqu);
       $this->display();
    }
    //常用电话添加
    public function tel_add(){
        $id=is_login();
        $at=M("Account_type");
        if(IS_POST){
            $t=M("Tool");
            $data['name']=$_POST['name'];
            $data['tel']=$_POST['tel'];//排序
            if($_POST['atid']!= -1){
                 $data['atid']=$_POST['atid'];
             }else{
                $this->ajaxReturn(array("msg"=>"请选择小区"),"JSON");
             }
           $data['aid']=$id;
            $data['img']=$_POST['image'];
            $data['number']=$_POST['number'];
            $data['content']=trim($_POST['details_info']);
            $data['status']=trim($_POST['status']);
            $data['top_if']=trim($_POST['top_if']);
            $data['type']=$_POST['type'];
           $data['url']=$_POST['url'];
            $result=$t->data($data)->add();
            if($result){
                $this->ajaxReturn(array("status"=>1,"msg"=>"添加成功"));
            }else{
                $this->ajaxReturn(array("msg"=>"添加失败"));
            }
            }
            $xiaoqu=$at->where("aid='{$id}' AND fid !=0")->select();
            $this->assign("result",$xiaoqu);
            $this->display();
    }



    //常用电话编辑
    public function tel_save(){
        $id=is_login();
        $at=M("Account_type");
        if(IS_POST){
            $t=M("Tool");
            $id=$_POST['toolid'];
            $data['name']=$_POST['name'];
            $data['tel']=$_POST['tel'];//排序
            $data['number']=$_POST['number'];
            if($_POST['atid']!= -1){
                 $data['atid']=$_POST['atid'];
            }
           $data['aid']=$id;
            $data['img']=$_POST['image'];
            $data['content']=trim($_POST['details_info']);
           $data['status']=trim($_POST['status']);
            $data['top_if']=trim($_POST['top_if']);
           
           $data['url']=$_POST['url'];

            $result=$t->where("id={$id}")->data($data)->save();

          if($result){
                $this->ajaxReturn(array("status"=>1,"msg"=>"编辑成功"));
            }else{
                $this->ajaxReturn(array("msg"=>"没有操作选项或编辑失败"));
            }
        }
            $xiaoqu=$at->where("aid='{$id}' AND fid !=0")->select();
            $this->assign("result",$xiaoqu);
            $this->display();
    }


        //批量删除数据列表内容
    public function delete_one_model(){
        $ids=$_POST['id'];
        $ids=explode(',',$ids);
        $me=M("Tool");
        $where['id']=array("IN",$ids);
        $flag=$me->where($where)->delete();
        if($flag){
            $this->ajaxReturn(array("status"=>1,"msg"=>"删除成功"),"JSON");
        }else{
            $this->ajaxReturn(array("msg"=>"删除失败"),"JSON");
        }
        $this->ajaxReturn($message);
    }

    //删除数据
    public function teldel(){
        $id=$_POST['id'];
        $me=M("Tool");
        $result=$me->where("id='{$id}'")->delete();
        if($result){
            $this->ajaxReturn(array("status"=>1,"msg"=>"操作成功"));
        }else{
            $this->ajaxReturn(array("msg"=>"操作失败"));
        }
    }


    //口袋通
    public function mall(){
        $id=is_login();
       // $at=M("Account_type");
        


        $t=M("Tool");
        //$res=$t->where("aid='%d'",$id)->getField('aid',true); 
        if($_GET['id']){
            $tel_edit=$t->where("id=%d",$_GET['id'])->find();
            $this->assign("tel",$tel_edit);
        }
        $where['aid']=$id;
        $where['type']=2;
        $name=trim($_POST['name']);
        if($_POST['name']){
           $where['name']=array('Like',"%{$name}%");
        }
        import('ORG.Util.Page');// 导入分页类
        $count= $t->where($where)->count();// 查询满足要求的总记录数
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $result=$t->where($where)->order("top_if desc,tel desc,id desc")->limit($Page->firstRow.','.$Page->listRows)->select();

        //$xiaoqu=$at->where("aid='{$id}' AND fid !=0")->select();
        //$this->assign("xiaoqu",$xiaoqu);
        $this->assign("result",$result);
       $this->display();
    }


       //常用口袋通
    public function mall_edit(){

        $id=is_login();
        $at=M("Account_type");
        $res=$at->where("aid='%d'",$id)->getField('id',true); 


        $t=M("Tool");
        if($_GET['id']){
            $tel_edit=$t->where("id=%d",$_GET['id'])->find();

            $this->assign("tel",$tel_edit);
        }


       // $where['atid']=array("IN",$res);
        if($_POST['account']){
           $where['name']=array('Like',"%$_POST[account]%");
        }
         import('ORG.Util.Page');// 导入分页类
        $count= $t->where($where)->count();// 查询满足要求的总记录数
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $result=$t->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

        $xiaoqu=$at->where("aid='{$id}' AND fid !=0")->select();
        $this->assign("xiaoqu",$xiaoqu);
        $this->assign("result",$result);
       $this->display();
    }


 //常用电话添加
    public function mall_add(){
        $id=is_login();
        $at=M("Account_type");
        if(IS_POST){
            $t=M("Tool");
            $data['name']=$_POST['name'];
            $data['tel']=$_POST['tel'];//排序
            $data['aid']=$id;
            $data['img']=$_POST['image'];
            $data['content']=trim($_POST['details_info']);
           $data['status']=trim($_POST['status']);
            $data['top_if']=trim($_POST['top_if']);
            $data['type']=$_POST['type'];
           $data['url']=$_POST['url'];
            $result=$t->data($data)->add();
            if($result){
                $this->ajaxReturn(array("status"=>1,"msg"=>"添加成功"));
            }else{
                $this->ajaxReturn(array("msg"=>"添加失败"));
            }
            }
            $xiaoqu=$at->where("aid='{$id}' AND fid !=0")->select();
            $this->assign("result",$xiaoqu);
            $this->display();
    }

      //常用电话编辑
    public function mall_save(){
        $id=is_login();
        $at=M("Account_type");
        if(IS_POST){
            $t=M("Tool");
            $id=$_POST['toolid'];
            $data['name']=$_POST['name'];
            $data['tel']=$_POST['tel'];//排序
            $data['img']=$_POST['image'];
            $data['content']=trim($_POST['details_info']);
           $data['status']=trim($_POST['status']);
            $data['top_if']=trim($_POST['top_if']);
           
           $data['url']=$_POST['url'];
            $result=$t->where("id={$id}")->data($data)->save();
    
          if($result){
                $this->ajaxReturn(array("status"=>1,"msg"=>"编辑成功"));
            }else{
                $this->ajaxReturn(array("msg"=>"没有操作选项编辑失败"));
            }
        }
            $xiaoqu=$at->where("aid='{$id}' AND fid !=0")->select();
            $this->assign("result",$xiaoqu);
            $this->display();
    }




        //批量删除数据列表内容
    public function delete_mall_model(){
        $ids=$_POST['id'];
        $ids=explode(',',$ids);
        $me=M("Tool");
        $where['id']=array("IN",$ids);
        $flag=$me->where($where)->delete();
        if($flag){
            $this->ajaxReturn(array("status"=>1,"msg"=>"删除成功"),"JSON");
        }else{
            $this->ajaxReturn(array("msg"=>"删除失败"),"JSON");
        }
        $this->ajaxReturn($message);
    }

    //删除数据
    public function malldel(){
        $id=$_POST['id'];
        $me=M("Tool");
        $result=$me->where("id='{$id}'")->delete();
        if($result){
            $this->ajaxReturn(array("status"=>1,"msg"=>"操作成功"));
        }else{
            $this->ajaxReturn(array("msg"=>"操作失败"));
        }
    }











}