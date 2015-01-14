<?php

class SelfformAction extends CommonAction {

    private $forumlist=null;
     private $xiaoqu_id=null;
    
    public function _initialize(){

        parent::_initialize();

        $this->forumlist=C("FORUM_LIST");

        //当前所在分类
        $navid=$_GET['navid'] ? $_GET["navid"] : $_POST["navid"];
        if(empty($navid)){
            $navid=$this->forumlist["WY_FORM"];
        }
        $this->assign("navid",$navid);

        //查找出登录人员属于的小区
        $at=M("Account");
        $user=$at->where("uid = ".is_login())->find();
        $this->xiaoqu_id=$user['type_xiaoqu'];


    }

	//物业 表单列表
    public function index(){
    	$form=M("Message_type");
    	$id=$this->xiaoqu_id;

        import('ORG.Util.Page');// 导入分页类
        $count= $form->where("xiaoqu_id=%d AND aflag = '%s'",$id,$this->forumlist['WY_FORM'])->count();// 查询满足要求的总记录数
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
    	$result=$form->where("xiaoqu_id='%s' AND is_delete=%d AND aflag = '%s'",$id,0,$this->forumlist['WY_FORM'])->order('xiaoqu_id desc')->limit($Page->firstRow.','.$Page->listRows)->getField('id,name,keyworks,is_status,introduce');
    	//echo $form->getlastsql();
    	$this->assign('result',$result);
		$this->display();

    }

      
    //物业表单添加
    public function form_add(){
        
    	$this->display();
    }

    //添加物业表单
    public function formadd_data(){
        $form=M("MessageType");
        $uid=$this->xiaoqu_id;
        $data['name']=trim($_POST['name']);
        $data['keyworks']=trim($_POST['keyworks']);
        $data['image']=$_POST['image'];
        $data['url']=$_POST['url'];
        $data['introduce']=$_POST['introduce'];
        $data['success_send_msg']=$_POST['success_send_msg'];
        $data['success_info']=$_POST['success_info'];
        $data['faile_info']=$_POST['faile_info'];
        $data['worker_openid']=$_POST['worker_openid'];
        $data['agtime']=$_POST['agtime'];
        $data['accept_openid']=$_POST['accept_openid'];
        $data['details_info']=$_POST['details_info'];
        $data['is_anon']=$_POST['is_anon'] ? 1 : 0;
        $data['is_bind']=$_POST['is_bind'] ? 1 : 0;
        if($_POST['is_status']=='on'){
            $data['is_status']=1;
        }else{
            $data['is_status']=0;
        }
        if($_POST['is_shortcut']=='on'){
            $data['is_shortcut']=1;
        }else{
            $data['is_shortcut']=0;
        }
        $data['xiaoqu_id']=$uid;

        $data['aflag']=$this->forumlist['WY_FORM'];

          
        if(empty($data['name'])||empty($data['keyworks'])||empty($data['aflag'])){
        //$this->error("请填写详细详细");
            $this->ajaxReturn(array('msg'=>'请填写详细详细'),"JSON");
        }else{
            $result=$form->add($data);
            if($result){
                //$this->success('新增成功');
                 $this->ajaxReturn(array('status'=>1,'msg'=>"新增成功"),"JSON");
            }else{
                //$this->error("添加失败");
                 $this->ajaxReturn(array('msg'=>'添加失败'),"JSON");
            }
        }
         
       
    }

    //删除表单
    public function del_form(){
        $id=$_POST['id'];
        if(empty($id)){
            //$this->error("页面出错");
            $this->ajaxReturn(array("msg"=>"页面出错"),"JSON");
        }else{
            $delform=M("Message_type");
            $result=$delform->where("id=%d",$id)->delete();
            if($result){
                //$this->success("删除成功");
                $this->ajaxReturn(array("status"=>1,"msg"=>"删除成功"),"JSON");
            }else{
                $this->ajaxReturn(array("msg"=>"删除失败"),"JSON");
            }
        }
    }

    //表单编辑
    public function edit_form(){
        $id=$_GET['id'];
        if(empty($id)){
            $this->error("页面出错");
        }else{
            $edit_form=M("Message_type");
            $result=$edit_form->where("id=%d",$id)->find();
            $this->assign("result",$result);
            $this->display();
        }
    }

    //修改表单
    public function form_data_save(){
        $form=M("Message_type");
        $uid=$this->xiaoqu_id;
        $id=$_POST['form_id'];
        $data['name']=trim($_POST['name']);
        $data['keyworks']=trim($_POST['keyworks']);
        $data['image']=$_POST['image'];
        $data['url']=$_POST['url'];
        $data['introduce']=$_POST['introduce'];
        $data['success_send_msg']=$_POST['success_send_msg'];
        $data['success_info']=$_POST['success_info'];
        $data['faile_info']=$_POST['faile_info'];
        $data['worker_openid']=$_POST['worker_openid'];
        $data['agtime']=$_POST['agtime'];
        $data['accept_openid']=$_POST['accept_openid'];
        $data['details_info']=$_POST['details_info'];
        $data['is_status']=$_POST['is_status'] ? 1 : 0;
        $data['is_shortcut']=$_POST['is_shortcut'] ? 1 : 0;
        $data['is_bind']=$_POST['is_bind'] ? 1 : 0;
        $data['is_anon']=$_POST['is_anon'] ? 1 : 0;
        $data['xiaoqu_id']=$uid;
        $data['aflag']=$this->forumlist['WY_FORM'];
        if(empty($data['name'])||empty($data['keyworks'])||empty($data['aflag'])){
        //$this->error("请填写详细详细");
            $this->ajaxReturn(array("msg"=>"请填写详细内容"),"JSON");
        }else{
            $result=$form->where("id=%d",$id)->data($data)->save();
            if($result){
                //$this->success('编辑成功');
                $this->ajaxReturn(array("status"=>1,"msg"=>"编辑成功"),"JSON");
            }else{
                //$this->error("编辑失败");
                $this->ajaxReturn(array("msg"=>"编辑失败或没改动内容"),"JSON");
            }
        }
    }

    //测试表单
    public function test2(){
        $this->display();
    }


    //模型选项列表
    public function modelid(){
        $id=$_GET['id'];
        session('modelid',$id);
        $modelid=M("Form");
        $result=$modelid->where("aid='%s' AND is_delete=%d",$id,0)->order("sort , id desc")->select();
        $this->assign("result",$result);
        $this->display();
    }

      public function move_form(){
        $moveid=$_POST["id"];
        $action=$_POST["action"];
        $where['aid']=array("EQ",$_GET['aid']);
        $where['is_delete']=array("EQ",0);
        $move=new MoveAction();
        $flag=$move->move("form",$moveid,"id",$where,"sort",$action);
        if($flag==1){
            $this->ajaxReturn(array("flag"=>1,"msg"=>"移动成功"),"JSON");
        }else{
            $msg="";
            switch ($flag) {
                case 0:
                    $msg="移动失败";
                    break;
                case 2:
                    $msg="已经是第一个";
                    break;
                case 3:
                    $msg="已经是最后一个";
                    break;
                case 4:
                    $msg="请选择要如何操作";
                    break;

                default:
                    $msg="移动失败";
                    break;
            }
            $this->ajaxReturn(array("flag"=>0,"msg"=>$msg),"JSON");
        }

    }

    //新建模型选项UI部分
    public function add_new_model_ui(){
        $id=session("modelid");
        $this->assign("id",$id);
        $this->display();
    }

    //新建模型选项 data部分or 编辑模型
    public function add_new_model(){
       
        $s=$_POST['options'];
        $as=explode("\r", $s);
        //var_dump($as);
        $options=serialize($as);
        $data['id']=$_POST['modelid'];
        $data['name']=$_POST['name'];
        $data['type']=$_POST['type'];
        $data['defaultvalue']=$_POST['defaultvalue'];
        $data['options']=$options;

        $data['selectpicker']=$_POST['selectpicker'];
        $data['introduce']=$_POST['introduce'];
        $data['aid']=$_SESSION['modelid'];
        if($_POST['is_must']=="on"){
            $data['is_must']=1;
        }else{
            $data['is_must']=0;
        }
        if($_POST['is_display']=="on"){
            $data['is_display']=1;
        }else{
            $data['is_display']=0;
        }
        $form_model=M('Form');

        //echo $form_model->getlastsql();exit();
        if(empty($data['id'])){
            $result=$form_model->data($data)->add();
           
            if($result){
                //$this->success("添加成功");
                $this->ajaxReturn(array("status"=>1,"msg"=>"添加成功"),"JSON");
            }else{
                //$this->error("添加失败");
                $this->ajaxReturn(array("msg"=>"添加失败"),"JSON");
            }
        }else{
            $result=$form_model->where("id=%d",$_POST['modelid'])->data($data)->save();
            if($result){
                $this->ajaxReturn(array("status"=>1,"msg"=>"编辑成功"),"JSON");
            }else{
                //$this->error("编辑失败");
                $this->ajaxReturn(array("msg"=>"编辑失败"),"JSON");
            }
        }
    }

    //编辑模型选项
    public function edit_model(){
        $id=$_GET['id'];
        $form_model=M("Form");
        $result=$form_model->where("id=%d",$id)->find();
        $res=unserialize($result['options']);

        $this->assign("res",$res);
        $this->assign("result",$result);

        $this->display();
    }

    //删除模型
    public function del_model(){
        $id=$_POST['id'];
        $form_model=M("Form");
        $result=$form_model->where("id='%d'",$id)->delete();
        if($result){
            //$this->success("删除成功");
            $this->ajaxReturn(array("status"=>1,"msg"=>"删除成功"),"JSON");
        }else{
            $this->ajaxReturn(array("msg"=>"删除失败"),"JSON");
        }
    }

    //模型下移
    public function move_down(){
        $id=$_GET['id'];
        $form_model=M("Form");
        //$result=$form_model->where("id='%d' AND $")
    }
    //2014.12.2
    //模型选项处理列表
    public function elsemodellist(){
         //$id为每种表单的类型
        $id=$_GET['id'];
        session('modelidlistid',$id);
        $modelist=M("Message");
        $this->assign('id',$id);

        $data['aid']=$id;
        $data['is_delete']=0;
         if($_POST['type']){
            switch ($_POST['type']) {
                case 'type0':
                    $data['handle_status']=array("eq","0");//未处理
                    break;
                case 'type1':
                    $data['handle_status']=array("eq","1");//已处理
                    break;
                case 'type2':
                    $data['handle_status']=array("eq","2");//处理中
                    break;
            }

        }
        
        import('ORG.Util.Page');// 导入分页类
        $count= $modelist->where("aid={$id}")->count();// 查询满足要求的总记录数
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $result=$modelist->where($data)->limit($Page->firstRow.','.$Page->listRows)->select();

        foreach ($result as $key => $value) {
            $result[$key]['form_content']=unserialize($value['form_content']);
        }

    
       
	       //now type
        $mt=M("message_type");
        $aresult=$mt->where("id = %d",$id)->find();
        $this->assign("aresult",$aresult);

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

    //选项模型处理添加
    public function elsemodellistaddui(){
        //$id为每种表单的类型
        $id=$_SESSION['modelidlistid'];
        $modelui=M("Form");
        $result=$modelui->where("aid=%d  ",$id)->select();
        foreach ($result as $key=>$one) {
            $result[$key]['options']=unserialize($one['options']);
        }
        $this->assign("result",$result);
        $this->display();

    }

    //选项模型数据添加 编辑
    public function elsemodellistadd(){
        $s=$_POST['model'];
        $options=serialize($s);
        $id=$_SESSION['modelidlistid'];
        $data['account']="匿名";
        $data['handle_status']=$_POST['handle_status'];
        $data['assess']=$_POST['assess'];
        $data['remark']=$_POST['remark'];
        $data['post_time']=date("Y-m-d H:m:s",time());
        $data['aid']= $id;
        $data['form_content']=$options;
        $message=M("Message");

        $fm=M("form");
        $fresult=$fm->where("aid = %d",$id)->select();

        foreach ($fresult as $key => $one) {
            if($one['is_must']){
                if(empty($s[$one['id']])){
                     $this->error($one['name']."不可以为空");
                }
            }
            if($one['selectpicker']==1){//验证邮件
                if(!ereg("^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+",$s[$one['id']])){
                    $this->error($one['name']."必须为邮件格式");
                }
            }else if($one['selectpicker']==2){//验证手机
                if(!preg_match("/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/",$s[$one['id']])){
                    $this->error($one['name']."必须为手机号码");
                }

            }else if($one['selectpicker']==7){//验证邮编
                if(!preg_match("/[0-9]{6}/",$s[$one['id']])){
                    $this->error($one['name']."必须为邮编格式");
                }
            }else if($one['selectpicker']==4){//验证qq
                if(!preg_match('/^[1-9][0-9]{4,9}$/', $s[$one['id']])){
                    $this->error($one['name']."必须为qq格式");
                }

            }else if($one['selectpicker']==3){//验证网址
                if(!ereg("^http://[_a-zA-Z0-9-]+(.[_a-zA-Z0-9-]+)*$", $s[$one['id']])){
                    $this->error($one['name']."必须为网址格式");
                }
            }else if($one['selectpicker']==6){//验证数字
                if(!preg_match("^[0-9]*$/",$s[$one['id']])){
                    $this->error($one['name']."必须为数字格式");
                }
            }else if($one['selectpicker']==5){//验证字母
                if(!preg_match("^[a-zA-Z]+$", $s[$one['id']])){
                    $this->error($one['name']."必须为字母格式");
                }
            }

        }


        $result=$message->data($data)->add();
        if($result){
            $this->success("添加成功");
        }else{
            $this->error("添加失败");
        }
        

    }

    //编辑选项模型
    public function elsemodeleditui(){
        $id=$_GET['id'];

        $eidtmodel=M("Message");
        $aid=$_SESSION['modelidlistid'];
         $this->assign("id",$aid);
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


        $this->display();
    }

    //编辑选项模型data
    public function editelsemodel(){
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
           // $this->success("编辑成功");
            $this->ajaxReturn(array("status"=>1,'msg'=>"编辑成功"));
        }else{
            $this->ajaxReturn(array('msg'=>"编辑成功"));
        }
    }



    //删除选项
    public function del_elsemodel(){
        $id=$_GET['id'];
        $delmodel=M("Message");
        $result=$delmodel->where("id=%d",$id)->delete();
        if($result){
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }

    }

    
    //批量删除数据列表内容
    public function delete_one_model(){
        $ids=$_POST['id'];
        $ids=explode(',',$ids);
        $me=M("message");
        $where['id']=array("IN",$ids);
        $flag=$me->where($where)->delete();
        if($flag){
            $this->ajaxReturn(array("status"=>1,"msg"=>"删除成功"),"JSON");
        }else{
            $this->ajaxReturn(array("msg"=>"删除失败"),"JSON");
        }
        $this->ajaxReturn($message);
    }





//导出表单数据 
    public function export_form(){

        $id=$_GET['id'];
        $modelist=M("Message");

        $result=$modelist->where("aid='%d' AND is_delete=%d ",$id,0)->select();

        foreach ($result as $key => $value) {
            $result[$key]['form_content']=unserialize($value['form_content']);
        }
       
        //now type
        $mt=M("message_type");
        $aresult=$mt->where("id = %d",$id)->find();
        $this->assign("aresult",$aresult);

        $formdisplay=M("Form");
        $formresult=$formdisplay->where("aid='%d' AND is_display=%d",$id,1)->select();
        foreach ($formresult as $key => $value) {
            $formresult[$key]['options']=unserialize($value['options']);
        }


        //if($result && is_array($result)){
            Vendor('PhpExcel.PHPExcel');
            Vendor("PhpExcel.PHPExcel.Style");
            Vendor("PHPExcel.PHPExcel.Style.Alignment");
            Vendor("PHPExcel.PHPExcel.Style.Fill");

            $objPHPExcel= new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                ->setLastModifiedBy("Maarten Balliauw")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("This document for Office 2007 XLSX.")
                ->setKeywords("office 2007 ")
                ->setCategory("office 2007");
            
            //设置默认行高
            $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
            $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);



            //设置列宽
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('12');
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('12');
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('12');
            $coll0=ord("D");
            foreach ($formresult as $key => $value) {
                $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension(chr($coll0))->setWidth('12');
                $coll0++;
            }

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension(chr($coll0+1))->setWidth('12');
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension(chr($coll0+2))->setWidth('12');
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension(chr($coll0+3))->setWidth('12');
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension(chr($coll0+4))->setWidth('12');


            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue("A1","序号")
                ->setCellValue("B1","微信信息")
                ->setCellValue("C1","物业信息");

            $coll1=ord("D");
            foreach ($formresult as $key => $value) {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue(chr($coll1)."1",$value['name']);
                $coll1++;
            }

             $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue(chr($coll1+1)."1","时间")
                    ->setCellValue(chr($coll1+2)."1","处理状态")
                    ->setCellValue(chr($coll1+3)."1","点评")
                    ->setCellValue(chr($coll1+4)."1","备注");
             
            //填充数据
            $counter=2;
            foreach($result as $one){

                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue("A".$counter,$one['id'])
                    ->setCellValue("B".$counter,$one['account'])
                    ->setCellValue("C".$counter,"物业编号[".$one['title ']."],物业名称[".$one['details']."]");
                $coll2=ord("D");
                foreach ($formresult as $key => $value) {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($coll2).$counter,$one['form_content'][$value['id']]);
                    $coll2++;
                }

                $chulizhuangtai="处理中";
                $dianping="未点评";
                if($one['handle_status']=="0"){
                    $chulizhuangtai="未处理";
                }elseif($one['handle_status']=="1" && !empty($one['assess'])){
                    if($one['assess']=="1"){
                        $dianping="好评";
                    }else{
                        $dianping="差评";
                    }
                    $chulizhuangtai="已处理";
                }else{
                    $chulizhuangtai="已处理";
                }

                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue(chr($coll2+1).$counter,$one['post_time'])
                    ->setCellValue(chr($coll2+2).$counter,$chulizhuangtai)
                    ->setCellValue(chr($coll2+3).$counter,$dianping)
                    ->setCellValue(chr($coll2+4).$counter,$one['remark']);



                $counter++;
            }
            $objPHPExcel->getActiveSheet()->setTitle("表单列表");
            $filename="表单列表.xls";
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output'); //文件通过浏览器下载

        //}else{
        //    $this->error("没有数据");
        //}
    }




























}
