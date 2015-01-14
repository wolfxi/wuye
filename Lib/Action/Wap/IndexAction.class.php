<?php

class IndexAction extends CommonAction {

    public function index(){

    	$m=M("Message_type");
    	$res=$m->where("uid=2")->select();
    	$this->assign("result",$res);
    	$this->display();

	}

}