<?php

class TemplateEditHelper extends SOY2LogicBase{
	
	/**
	 * テンプレートのHTMLからレイアウト情報を判定する
	 */
	function checkLayoutByTemplateContent($template){
		$layout = $template->getLayout();
		$content = $template->loadTemplate();
		
		//keyを全部取得
		if(preg_match_all('/[^\/]?layout:([\S]*)/',$content,$tmp)){
			$keys = array_unique($tmp[1]);
			
			//現在の設定と、テンプレートに書かれている値を比較
			$current_keys = array_keys($layout);
			$diff1 = array_diff($current_keys,$keys);
			$diff2 = array_diff($keys,$current_keys);
			
			if(!empty($diff1) || !empty($diff2)){
				$layout = array();
				foreach($keys as $key){
					$layout[$key] = array(
						"id" => $key,
						"name" => $key,
						"color" => "#CCFFCC"
					);
				}
			}
			
			//順番に並べ直す
			$tmp = array();
			foreach($keys as $key){
				$tmp[$key] = $layout[$key];
			}
			$layout = $tmp;
			
		}else{
			$layout = array();
		}
		
		return $layout;
	}
	
	/**
	 * テンプレートとスケルトンを確認する
	 */
	function checkLayout($template){
		$layout = $template->getLayout();
		$content = $template->loadTemplate();
		
		$res = array();
		foreach($layout as $key => $value){
			$format1 = "layout:" . $key;
			$format2 = "/layout:" . $key;
			if(strpos($content,$format1) === false
				&& strpos($content,$format2) === false
			){
				$res[] = (array)$value;		
			}
		}
		
		return $res;
	}

	/**
	 * 
	 * テンプレートに書かれていない要素を全て取得する
	 * @return array
	 */
	function checkItem($template){
		$res = array();
		$items = $template->getItems();
		$content = $template->loadTemplate();
		
		
		foreach($items as $key => $item){
			
			$format = $item->getFormat();

			$start = strpos($content,$format);
			$format2 = "/" . $format;
			$end = strpos($content,$format2);
			
			//ok
			if($start && $end && $start < $end){
				continue;
			}
				
			//ng
			$res[] = $item;
				
		}
		
		return $res;
	}
	
	/**
	 * 自動追加
	 * テンプレートに書かれているが、存在しない要素は自動的に追加する
	 */
	function autoAppend($template,$options = array()){
		$content = $template->getTemplate();
		$items = $template->getItems();
		$defaultBlocks = (method_exists($template,"getDefaultBlocks")) ? $template->getDefaultBlocks() : array();
		
		$newItemClassName = get_class($template) . "Item";
		
		//navi
		$regex = '/cms:navigation="([^"]+)"/';
		preg_match_all($regex,$content,$tmp);
		$tmp[1] = array_unique($tmp[1]);
		foreach($tmp[1] as $_id){
			
			$start = '/(<.*cms:navigation="'.$_id.'"[^>]*?>)/';
			$end = '/(<.*\/cms:navigation="'.$_id.'"[^>]*?>)/';
			$html = "";
			
			//囲まれている箇所から自動生成する
			if(preg_match($start,$content,$tmp1,PREG_OFFSET_CAPTURE) && preg_match($end,$content,$tmp2,PREG_OFFSET_CAPTURE)){
				$startOffset = $tmp1[0][1] + strlen($tmp1[0][0]);
				$endOffset =  $tmp2[0][1];
				if($endOffset > $startOffset){
					$html = substr($content, $startOffset, $endOffset-$startOffset);
					$content = substr($content, 0, $startOffset) . substr($content, $endOffset);	//ナビゲーションの中が自動追加されないように
				}
			}
			
			
			//既に追加されている場合
			if(isset($items["navigation:" . $_id])){
				//ナビゲーションを上書きするオプション
				if(@$options["is_overwrite"]){
					$navigation = SOYCMS_Navigation::load($_id);
					if($navigation){
						$navigation->setTemplate($html);
						$navigation->setItems(array());
						$navigation->save();
						
						$this->autoAppend($navigation,$options);
						$this->updateItemOrder($navigation);
						$navigation->save();
					}
				}
				continue;
			}
			
			$item = new $newItemClassName();
			$item->setId($_id);
			$item->setType("navigation");
			$items["navigation:" . $_id] = $item;
			
			$navigation = SOYCMS_Navigation::load($_id);
			if(!$navigation){
				
				$navigation = new SOYCMS_Navigation();
				$navigation->setId($_id);
				$navigation->setName($_id);
				$navigation->setTemplate($html);
				$navigation->save();
				
				//ナビゲーションの中でも自動生成がされるようにする
				$this->autoAppend($navigation);
				$this->updateItemOrder($navigation);
				$navigation->save();
				
			//ナビゲーションを上書きする場合
			}else if(@$options["is_overwrite"]){
				$navigation->setTemplate($html);
				$navigation->setItems(array());
				$navigation->save();
				
				$this->autoAppend($navigation,$options);
				$this->updateItemOrder($navigation);
				$navigation->save();
				
			}
		}
		
		//library
		$regex = '/cms:include="([^"]+)"/';
		preg_match_all($regex,$content,$tmp);
		foreach($tmp[1] as $_id){
			$start = '/<.*cms:include="'.$_id.'"[^>]*>/';
			$end = '/<.*\/cms:include="'.$_id.'"[^>]*>/';
			$html = "";
			
			//囲まれている箇所から自動生成する
			if(preg_match($start,$content,$tmp1,PREG_OFFSET_CAPTURE) && preg_match($end,$content,$tmp2,PREG_OFFSET_CAPTURE)){
				$startOffset = $tmp1[0][1] + strlen($tmp1[0][0]);
				$endOffset =  $tmp2[0][1];
				$html = substr($content, $startOffset, $endOffset-$startOffset);
			}
			
			if(isset($items["library:" . $_id])){
				//ナビゲーションを上書きするオプション
				if(@$options["is_overwrite"]){
					$library = SOYCMS_Library::load($_id);
					if($library){
						$library->setContent($html);
						$library->save();
					}
					
				}
				continue;
			}
			
			$item = new $newItemClassName();
			$item->setId($_id);
			$item->setType("library");
			$items["library:" . $_id] = $item;
			
			$library = SOYCMS_Library::load($_id);
			if(!$library){
				$library = new SOYCMS_Library();
				$library->setId($_id);
				$library->setName($_id);
				$library->setContent($html);
				$library->save();
			}else if(@$options["is_overwrite"]){
				$library->setContent($html);
				$library->save();
			}
		}
		
		//block
		$regex = '/block:id="([^"]+)"/';
		preg_match_all($regex,$content,$tmp);
		foreach($tmp[1] as $_id){
			if(isset($items["block:" . $_id]))continue;
			if(isset($defaultBlocks["default:" . $_id])){
				if(isset($items["default:" . $_id]))continue;
				
				$item = new $newItemClassName();
				$item->setId($_id);
				$item->setType("default");
				$items["default:" . $_id] = $item;	
				
				continue;
			}
			
			$item = new $newItemClassName();
			$item->setId($_id);
			$item->setType("block");
			$items["block:" . $_id] = $item;
		}
		
		$template->setItems($items);
	}
	
	/**
	 * 要素のポジションをレイアウトの中かどうか判定して、レイアウトを自動的に設定する
	 * updateItemLayoutとの違いは、レイアウトの設定部分がついていること。
	 * 並び替えだけでいい場合はupdateItemLayoutの方が高速
	 */
	function updateItemLayout($template){
		$layout = $template->getLayout();
		$content = $template->loadTemplate();
		
		//場所を判定する
		$positions = array();
		foreach($layout as $key => $value){
			$start = 0;
			$end = 0;
			
			if(preg_match("/<!--\s+layout:" . $key. "\s+[\S]*-->/",$content,$tmp,PREG_OFFSET_CAPTURE)){
				$start = $tmp[0][1];
			}
			if(preg_match("/<!--\s+\/layout:" . $key. "\s+[\S]*-->/",$content,$tmp,PREG_OFFSET_CAPTURE)){
				$end = $tmp[0][1];
			}
			
			$positions[$key] = array(
				$start,
				$end
			);	
			
		}
		
		$items = $template->getItems();
		
		foreach($items as $item){
			$position = strpos($content,$item->getFormat());
			foreach($positions as $key => $array){
				$item->setOrder($position);
				
				if($array[0] <= $position && $position <= $array[1]){
					$item->setLayout($key);
					break;
				}
			}
		}
		
		$template->setItems($items);
	}
	
	/**
	 * 要素の中での記述を判断して、ソート順を変更する
	 */
	function updateItemOrder($template){
		$content = $template->loadTemplate();
		
		$items = $template->getItems();
		$pos = array();
		foreach($items as $key => $item){
			$position = strpos($content,$item->getFormat());
			$item->setOrder($position);
		}
		
		$template->setItems($items);
		
		//layoutも並び替え
		$layout = $template->getLayout();
		if(preg_match_all('/[^\/]?layout:([\S]*)/',$content,$tmp)){
			$keys = array_unique($tmp[1]);
			$tmp = array();
			foreach($keys as $key){
				$tmp[$key] = $layout[$key];
			}
			$layout = $tmp;
		}
		
		if(method_exists($template,"setLayout")){
			$template->setLayout($layout);
		}
	}
	
	/**
	 * ソート順に応じてHTMLを変更する
	 */
	function convertTemplateByOrder($template,$newKeys){
		$content = $template->loadTemplate();
		$items = $template->getItems();
		uasort($items,create_function('$a,$b','return ($a->getOrder() >= $b->getOrder());'));
		
		$oldKeys = array_keys($items);
		
		//not changed
		if($oldKeys == $newKeys){
			return ;
		}
		
		
		//新しい方のcontent
		$newContent = $content;
		$lastOffset = 0;
		
		foreach($newKeys as $number => $key){
			$oldKey = $oldKeys[$number];
			
			//同じは変更しない
			if($key == $oldKey)continue;
			
			$oldItem = $items[$oldKey];
			$format = $oldItem->getFormat();
			$start_regex = '/<!--\s*('.$format.')\s*-->/';
			$end_regex = '/<!--\s*\/('.$format.')\s*-->/';
			
			if(preg_match($start_regex,$newContent,$tmp1,PREG_OFFSET_CAPTURE,$lastOffset)
			&& preg_match($end_regex,$newContent,$tmp2,PREG_OFFSET_CAPTURE,$lastOffset)
			){
				$old_start = $tmp1[0][1];
				$old_end = $tmp2[0][1] + strlen($tmp2[0][0]);
				
				$item = $items[$key];
				$format = $item->getFormat();
				$start_regex = '/<!--\s*('.$format.')\s*-->/';
				$end_regex = '/<!--\s*\/('.$format.')\s*-->/';
				
				if(preg_match($start_regex,$content,$tmp1,PREG_OFFSET_CAPTURE)
				&& preg_match($end_regex,$content,$tmp2,PREG_OFFSET_CAPTURE)
				){
					$start = $tmp1[0][1];
					$end = $tmp2[0][1] + strlen($tmp2[0][0]);
					$newInner = substr($content,$start, ($end - $start));
					
					$newContent = substr($newContent, 0, $old_start)
								. $newInner 
								. substr($newContent, $old_end);
					
					$lastOffset = $old_start + strlen($newInner);
				}
			
			}
		}
		
		$template->setTemplate($newContent);
		$template->save();
	}
	
	/**
	 * 要素の一括表示切り替え
	 */
	function toggleItemConfig($templateId,$itemId,$toggle){
		$pages = SOY2DAOFactory::create("SOYCMS_PageDAO")->getByTemplate($templateId);
		
		foreach($pages as $page){
			$config = $page->loadItemConfig();
			if(!isset($config[$itemId]) || is_array($config[$itemId]))$config[$itemId] = array();
			$config[$itemId]["hidden"] = ($toggle) ? 0 : 1;
			$page->saveItemConfig($config);
			unset($page);
		}
		
	} 
}
?>