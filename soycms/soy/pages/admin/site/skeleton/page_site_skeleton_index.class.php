<?php
SOY2::import("admin.domain.SOYCMS_Site");

/**
 * @title サイト一覧
 */
class page_site_skeleton_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		if(isset($_POST["skeleton_upload"])){
			$tmpname = $_FILES["skeleton"]["tmp_name"];
			
			if(!file_exists(SOYCMS_ROOT_DIR . "content/skeleton/")){
				soy2_mkdir(SOYCMS_ROOT_DIR . "content/skeleton/");
			}
			
			//skeleton uncompress
			$manager = SOY2Logic::createInstance("site.logic.skeleton.SOYCMS_SkeletonManager");
			$files =  soy2_scandir(SOYCMS_ROOT_DIR . "content/skeleton/");
			$target = sprintf("skeleton-%02d",count($files) + 1);
			
			$manager->uncompress(
				$tmpname,
				SOYCMS_ROOT_DIR . "content/skeleton/" . $target
			);
			
			//copy original file
			move_uploaded_file(
				$tmpname,
				SOYCMS_ROOT_DIR . "content/skeleton/" . $target . "/skeleton.zip"
			);
			
			$this->jump("/site/skeleton/detail/" . $target);
		}
		
		$this->jump("/site/skeleton?failed");
	}

	function page_site_skeleton_index(){
		WebPage::WebPage();
		
		$this->createAdd("skeleton_list","_class.list.SkeletonList",array(
			
		));
		
		$this->addUploadForm("skeleton_upload_form");
		
	}
}