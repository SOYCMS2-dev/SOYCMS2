<?php

class page_list extends SOYCMS_WebPageBase{
	
	private $page = 1;
	private $limit = 20;
	private $search = array();
	
	function init(){
		if(isset($_GET["page"]))$this->page = $_GET["page"];
		if(isset($_GET["name"]))$this->search["name"] = $_GET["name"];
		if(isset($_GET["userId"]))$this->search["userId"] = $_GET["userId"];
		if(isset($_GET["group"]))$this->search["group"] = $_GET["group"];
		
		if(isset($_GET["csv_download"])){
			$this->limit = null;
			list($res,$total) = $this->doSearch();
			
			$labels = array(
				"id","名前","ログインID","メールアドレス","状態",
				"登録日時","更新日時","グループ",
			);
			$fields = array();
			$_fields = Plus_UserProfile::getFields();
			$setting = Plus_UserProfile::getSettings();
			
			foreach($_fields as $field){
				//CSVに出力しない設定
				if(isset($setting[$field->getFieldId()]) && $setting[$field->getFieldId()] == 2){
					continue;
				}
				$labels[] = $field->getName();
				$fields[$field->getFieldId()] = $field;
			}
			
			$filename = "users.csv";
			header("Cache-Control: public");
			header("Pragma: public");
			header("Content-Type: text/csv;");
			header("Content-Disposition: attachment; filename=" . $filename );
			
			ob_start();
			echo $this->convertToCSV($labels);
			foreach($res as $key => $obj){
				$_array = array(
					(function_exists("plus_user_print_id")) ? plus_user_print_id($obj->getId()) : $obj->getId(),
					$obj->getName(),
					$obj->getLoginId(),
					$obj->getMailAddress(),
					$obj->getStatus(),
					date("Y-m-d H:i:s",$obj->getCreateDate()),
					date("Y-m-d H:i:s",$obj->getUpdateDate()),
					$obj->getGroupIds()
				);
				$profiles = Plus_UserProfile::getProfile($obj->getId());
				foreach($fields as $field){
					$profile = (isset($profiles[$field->getFieldId()])) ? $profiles[$field->getFieldId()] : null;
					$_array[] = ($profile) ? $profiles[$field->getFieldId()]->getValue() : "";
				}
				echo $this->convertToCSV($_array);
			}
			$csv = ob_get_contents();
			ob_end_clean();
			
			echo mb_convert_encoding($csv,"Shift-JIS","UTF-8");
			exit;
		}
	}
	
	function page_list(){
		WebPage::WebPage();
		
		$this->buildPage();
		$this->buildForm();
	}
	
	function buildPage(){
		$this->addLink("create_link",array("link" => soycms_create_link("user/create")));
		
		//検索
		list($result,$total) = $this->doSearch();
		$this->createAdd("user_list","_class.list.UserList",array(
			"list" => $result
		));
		
		
		//ページャ
		$this->addPager("pager",array(
			"start" => ($this->page - 1) * $this->limit + 1,
			"page" => $this->page,
			"total" => $total,
			"limit" => $this->limit,
			"link" => soycms_create_link("/list/?group=".@$this->search["group"]."&page=")
		));
	}
	
	function buildForm(){
		$this->addForm("form",array(
			"method" => "get"
		));
		
		$groups = $this->getGroups();
		
		$this->addSelect("search_group_select",array(
			"name" => "group",
			"options" => $groups,
			"value" => @$this->search["group"]
		));
		
		$this->addInput("search_user_id",array(
			"name" => "userId",
			"value" => @$this->search["userId"]
		));
		
		$this->addInput("search_user_name",array(
			"name" => "name",
			"value" => @$this->search["name"]
		));
	}
	
	function doSearch(){
		$result = array();
		$total = 0;
		
		$query = new SOY2DAO_Query();
		$binds = array();
		$wheres = array();
		$query->prefix = "select";
		$query->sql = "id";
		$query->table = "plus_user_user";
		$query->order = "id desc";
		
		/* 検索条件の追加 */
		if(@$this->search["name"]){
			$binds[":name"] = "%" . $this->search["name"] . "%";
			$wheres[] = "name LIKE :name";
		}
		if(@$this->search["userId"]){
			$binds[":userId"] = "%" . $this->search["userId"] . "%";
			$wheres[] = "login_id LIKE :userId";
		}
		if(@$this->search["group"]){
			$binds[":group"] = "%" . $this->search["group"] . "%";
			$wheres[] = "group_ids LIKE :group";
		}
		
		//退会済みユーザは表示しない
		if(!isset($_GET["include_delete_user"])){
			$wheres[] = "user_status >= 0";
		}
		
		
		
		/* 検索条件の追加ここまで */
		
		if(!empty($wheres)){
			$query->where = implode(" AND ", $wheres);
		}
		
		$dao = SOY2DAOFactory::create("Plus_UserDAO");
		if($this->limit)$dao->setLimit($this->limit);
		if($this->limit)$dao->setOffset(($this->page - 1) * $this->limit);
		
		$res = $dao->executeQuery($query,$binds);
		foreach($res as $row){
			$result[$row["id"]] = $dao->getById($row["id"]);
		}
		
		$totalQuery = clone($query);
		$totalQuery->sql = "count(id) as row_count";
		$dao->setLimit(1);$dao->setOffset(0);
		$total = $dao->executeQuery($totalQuery,$binds);
		$total = $total[0]["row_count"];
		
		return array($result,$total);
	}
	
	/* util */
	
	function getGroups(){
		$_groups = array();
		$groups = SOY2DAO::find("Plus_Group");
		
		foreach($groups as $group){
			$groupId = $group->getGroupId();
			$_groups[$groupId] = $group->getName();
		}
		return $_groups;
	}
	
	function convertToCSV($array){
		$tmp = array();
		
		$quote = '"';
		foreach($array as $key => $value){
			$value = str_replace('"','""',$value);
			$tmp[] = $quote . $value . $quote;
		}
		
		return implode(",", $tmp) . "\n";
	}
	
}
?>