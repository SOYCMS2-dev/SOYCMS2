<?php
/**
 * @title クイックメニュー
 */
class page_menu_index extends SOYCMS_WebPageBase{

	private $mode = "list";

	function doPost(){

		$menus = $this->getMenus();

		if(isset($_POST["add"])){
			$menu = $_POST["AddMenu"];
			$key = "menu-" . (count($menus)+1);


			if(isset($_FILES["add_icon"])){
				$img = SOYCMS_ROOT_DIR . $this->getIconPath($key);
				move_uploaded_file($_FILES["add_icon"]["tmp_name"],$img);

			}else{
				$img = SOYCMS_ROOT_DIR . $this->getIconPath($key);
				copy(SOYCMS_ROOT_DIR . "common/menu-icon-01.png",$img);

			}


			$menu["icon"] = basename($this->getIconPath($key));
			$menus[$key] = $menu;
			SOYCMS_DataSets::put("quickmenu",$menus);
		}

		if(isset($_POST["save"]) && isset($_POST["id"]) && isset($menus[$_POST["id"]])){
			$key = $_POST["id"];
			
			$obj = (object)$menus[$key];
			SOY2::cast($obj,$_POST["Menu"]);
			$menus[$key] = (array)$obj;
			
			SOYCMS_DataSets::put("quickmenu",$menus);

			if(isset($_FILES["edit_icon"]) && $_FILES["edit_icon"]["size"] > 0){
				$img = SOYCMS_ROOT_DIR . $this->getIconPath($key);
				$res = move_uploaded_file($_FILES["edit_icon"]["tmp_name"],$img);
			}
		}

		if(isset($_POST["save_order"]) && isset($_POST["orders"])){
			$ids = explode(",",$_POST["orders"]);
			$newMenu = array();
			foreach($ids as $id){
				if(!isset($menus[$id]))continue;
				$newMenu[$id] = $menus[$id];
				unset($menus[$id]);
			}
			foreach($menus as $key => $value){
				$newMenu[$key] = $menus[$key];
			}
			SOYCMS_DataSets::put("quickmenu",$newMenu);

		}

		$this->jump("menu/edit?updated");


	}

	function page_menu_index($args = array()){
		if(count($args)>0 && isset($args[0]))$this->mode = $args[0];
		if($this->mode == "remove"){
			$menus = $this->getMenus();
			unset($menus[$_GET["id"]]);
			SOYCMS_DataSets::put("quickmenu",$menus);
			$this->jump("menu/edit");
		}
		WebPage::WebPage();

		$this->buildAddForm();
		$this->buildMenuList();

		$this->addModel("mode_edit",array(
			"visible" => ($this->mode == "edit")
		));

		$this->addForm("save_order_form");
	}

	function buildMenuList(){
		$this->addUploadForm("edit_form");
		$menus = $this->getMenus();

		$this->createAdd("menu_list","MenuList",array(
			"list" => $menus,
			"mode" => $this->mode
		));

	}

	function buildAddForm(){
		$this->addModel("add_form_area",array("visible"=>$this->mode == "edit"));
		$this->addUploadForm("add_form");

		$this->addInput("add_title",array(
			"name" => "AddMenu[title]",
			"value" => "",
		));

		$this->addInput("add_link",array(
			"name" => "AddMenu[link]",
			"value" => "http://",
		));

		$this->addInput("add_onclick",array(
			"name" => "AddMenu[onclick]",
			"value" => "",
		));

		$this->addCheckbox("add_target_blank",array(
			"elementId" => "add_target_blank",
			"name" => "AddMenu[target]",
			"value" => "1",
			"isBoolean" => true
		));
	}

	function getIconPath($i){
		return "content/" . SOYCMS_LOGIN_SITE_ID . "/" . $i . ".png";
	}

	function getLayout(){
		return ($this->_soy2_parent) ? "blank.php" : "default.php";
	}

	function getMenus(){
		$menus = SOYCMS_DataSets::get("quickmenu",array(
			"dynamic_edit" => array(
				"title" => "ダイナミック編集でデザインを確認しながら変更",
				"link" => soycms_create_link("/") . "?dynamic",
				"icon" => "quick_icon_01.gif",
				"target" => "1"
			),
			"new_entry" => array(
				"title" => "新しいWYSIWYG機能で投稿する",
				"link" => soycms_create_link("/entry/create"),
				"icon" => "quick_icon_02.gif"
			),
			"template" => array(
				"title" => "テンプレートを編集してデザインを変更",
				"link" => soycms_create_link("/page/template"),
				"icon" => "quick_icon_03.gif"
			),
			"dir_edit" => array(
				"title" => "サイトに合わせてディレクトリを変更する",
				"link" => soycms_create_link("/page/list"),
				"icon" => "quick_icon_04.gif"
			),
			"cms2_feedback" => array(
				"title" => "要望や不具合をフィードバックする",
				"link" => "http://www.soycms2.net/feedback/",
				"icon" => "quick_icon_05.gif",
				"target" => "1"
			),
		));
		return $menus;
	}

}


class MenuList extends HTMLList{

	private $mode;
	private $removeLink;

	function init(){
		$this->removeLink = soycms_create_link("menu/remove");
	}

	function populateItem($entity,$key){

		$path = "content/". SOYCMS_LOGIN_SITE_ID . "/" . @$entity["icon"];

		$this->addImage("menu_img",array(
			"src" => (file_exists(SOYCMS_ROOT_DIR . $path)) ?
					SOYCMS_ROOT_URL . $path
				:	SOYCMS_ROOT_URL . "common/img/menu-icon-01.png"
		));

		$this->addLabel("menu_title",array(
			"text" => $entity["title"]
		));

		$this->addLink("menu_link",array(
			"link" => $entity["link"],
			"onclick" => @$entity["onclick"],
			"target" => (@$entity["target"]) ? "_blank" : ""
		));

		$this->addLink("edit_link",array(
			"link" => "#menu_edit_" . $key,
			"visible" => ($this->mode == "edit")
		));

		$this->addLink("remove_link",array(
			"link" => $this->removeLink . "?id=" . $key,
			"onclick" => "return confirm('delete ok?');",
			"visible" => ($this->mode == "edit")
		));

		/* 編集エリア */
		$this->addInput("menu_id",array("name"=>"id","value"=>$key));

		$this->addModel("edit_area",array(
			"visible" => ($this->mode == "edit"),
			"attr:id" => "menu_edit_" . $key
		));

		$this->addInput("edit_title",array(
			"name" => "Menu[title]",
			"value" => @$entity["title"],
		));

		$this->addInput("edit_link_input",array(
			"name" => "Menu[link]",
			"value" => @$entity["link"],
		));

		$this->addInput("edit_onclick",array(
			"name" => "Menu[onclick]",
			"value" => @$entity["onclick"],
		));

		$this->addCheckbox("edit_target_blank",array(
			"label" => "Open in new window",
			"name" => "Menu[target]",
			"value" => "1",
			"isBoolean" => true,
			"selected" => @$entity["target"]
		));

	}

	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
}
?>
