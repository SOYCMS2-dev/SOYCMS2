<?php

class page_page_label_create extends SOYCMS_WebPageBase{
	
	function doPost(){
		$label = SOY2::cast("SOYCMS_Label",(object)$_POST["Label"]);
		
		try{
			if($label->check()){
				$dao = SOY2DAOFactory::create("SOYCMS_LabelDAO");
				
				if(isset($_POST["insert_last"])){
					$count = $dao->countByDirectory($label->getDirectory());
					$count++;
					$label->setOrder($count);
				}
				
				$count = 0;
				$alias = $label->getAlias();
				while(true){
					try{
						$_label = $dao->getByAlias($label->getAlias());
						$label->setAlias($alias . "_" . $count);
						$count++;
					}catch(Exception $e){
						break;
					}
				}
				
				$count = 0;
				$name = $label->getName();
				while(true){
					try{
						$_label = $dao->getByParam($label->getName(),$label->getDirectory());
						$label->setName($name . "_" . $count);
						$count++;
					}catch(Exception $e){
						break;
					}
				}
				
				$id = $dao->insert($label);	//先頭に入力されるようにしたい
				
				$this->jump("/page/label/detail/$id?created");	
			}
		}catch(Exception $e){
			
		}
		
		
	}
	
	function init(){
		$this->label = new SOYCMS_Label();
		
	}
	
	function page_page_label_create(){
		WebPage::WebPage();
		
		$label = $this->label;

		$this->createAdd("create_form","HTMLForm");
		
		$this->createAdd("label_name","HTMLInput",array(
			"name" => "Label[name]",
			"value" => $label->getName(true),
		));
		
		$this->createAdd("label_alias","HTMLInput",array(
			"name" => "Label[alias]",
			"value" => $label->getAlias(),
		));
		
		$this->createAdd("label_tree","_class.list.LabelTreeComponent",array(
			"checkboxName" => "Label[directory]", 
			"selected" => $label->getDirectory()
		));
	}
}


?>