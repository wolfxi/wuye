<?php

/**
 * 该类用于上下移动分类
 */

class MoveAction extends Action {

	//用于操作的表
	private $table=null;

	//该表的主键
	private $id=null;

	//移动分类的主键 id值
	private $moveid=null;

	//限制条件  
	private $where=array();

	//排序依据的字段
	private $order=null;

	//移动操作名 枚举值  上移：UP，下移：DOWN
	private $action=null;


	//需要移动的数据集的key值  
	private $movekey=null;


	//构造函数
	public function _initset($table,$moveid,$id,$where,$order,$action){
		$this->table=$table;
		$this->id=$id;
		$this->moveid=$moveid;
		$this->id=$id;
		$this->where=$where;
		$this->order=$order;
		$this->action=$action;
	}

	//获取要排序的数据原始数据
	//return array  二维数组
	private function _getData(){
		$tableModel=M();
		$data=$tableModel->table(C("DB_PREFIX").$this->table)->where($this->where)->order($this->order.", ".$this->id." desc")->select();
		if($data){
			return $data;
		}else{
			return array();
		}
	}

	//处理原始数据 
	//返回要修改更新的数据
	//return array 二位数组
	private function _dealData(){
		$data=$this->_getData();
		if(is_array($data) && count($data)>1){
			//只返回该主键和排序字段
			$readyData=array();
			foreach($data as $key=>$one){
				$temp[$this->id]=$one[$this->id];//获取主键
				$temp[$this->order]=$key+1;//获取排序值
				if($one[$this->id]==$this->moveid){
					$this->movekey=$key;
				}
				array_push($readyData,$temp);
				unset($temp);
			}
			return $readyData;
		}else{
			return $data;
		}
	}


	//上移处理
	//$data 要处理的数据集
	private function _updown(){
		$data=$this->_dealData();
		
		if(is_array($data) && count($data)>1){
			if($this->action=="UP"){
				//上移操作
				if($this->movekey!=0){
					//对上一个操作
					$prekey=$this->movekey-1;
					if(array_key_exists($prekey,$data)){
						$predata=$data[$prekey];
						$movedata=$data[$this->movekey];
						$presort=$predata[$this->order];
						$movesort=$movedata[$this->order];
						//对换两个排序的位置
						$data[$this->movekey][$this->order]=$presort;
						$data[$prekey][$this->order]=$movesort;
					}
					return $data;
				}else{
					return false;
				}
			}elseif($this->action=="DOWN"){
				//下移操作
					$nextkey=$this->movekey+1;
					if(array_key_exists($nextkey,$data)){
						$nextdata=$data[$nextkey];
						$movedata=$data[$this->movekey];	

						$nextsort=$nextdata[$this->order];
						$movesort=$movedata[$this->order];
						//对换两个排序的位置
						$data[$this->movekey][$this->order]=$nextsort;
						$data[$nextkey][$this->order]=$movesort;
					}else{
						return false;
					}
					return $data;
			}else{
				return $data;
			}
		}else{
			return false;
		}
	}


	//更新数据库
	//return true or false
	private function _updateDB($data){
		$tableModel=M();
		$tableModel->startTrans();
		$flag=true;
		foreach($data as $one){
			$flag=$tableModel->table(C("DB_PREFIX").$this->table)->where($this->id."= %d",$one[$this->id])->setField($this->order,$one[$this->order]);	
		}
		$tableModel->commit();
		return true;
	}

	//移动操作 
	public function move($table,$moveid,$id,$where,$order,$action){
		$this->_initset($table,$moveid,$id,$where,$order,$action);
		$data=$this->_updown();	
		if(is_array($data) && $data){
			$flag=$this->_updateDB($data);
			if($flag){
				return 1;//"移动成功"
			}else{
				return 0;//"移动失败"
			}
		}else{
			if($this->action=="UP"){
				return 2;//"已经是第一个";
			}elseif($this->action=="DOWN"){
				return 3;//"已经是最后一个";
			}else{
				return 4;//"请选择要如何操作";
			}
		}
	}

























}

