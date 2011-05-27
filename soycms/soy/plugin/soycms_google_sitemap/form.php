<?php
$pages = SOY2DAO::find("SOYCMS_Page");
$entryDAO = SOY2DAOFactory::create("SOYCMS_EntryDAO");
$config = array();
?>
<div class="section">
	<div class="title">
		<h3>XMLサイトマップの条件を設定してください</h3>
	</div>
	<div class="content">
	
		<form method="post">
	
		<?php if(file_exists(SOYCMS_SITE_DIRECTORY . "sitemap.xml")){ ?>
			<div class="form-section">
				<div class="label">
					<h4>XMLサイトマップのURL</h4>
				</div>
				<div class="item">
					<input class="m-area liq-area" onclick="$(this).select();" value="<?php echo SOYCMS_SITE_URL . "sitemap.xml"; ?>" />
				</div>
			</div>
			
			<p>最終更新時刻：<?php echo date("Y-m-d H:i:s",filemtime(SOYCMS_SITE_DIRECTORY . "sitemap.xml"))?></p>
			
			<?php
				if(file_exists(SOYCMS_SITE_DIRECTORY . ".plugin/sitemap.conf")){
					$config = soy2_unserialize(file_get_contents(SOYCMS_SITE_DIRECTORY . ".plugin/sitemap.conf"));
					
				}else{
					$xml = simplexml_load_file(SOYCMS_SITE_DIRECTORY . "sitemap.xml");
					$urlset = $xml->children("http://www.sitemaps.org/schemas/sitemap/0.9");
					
					foreach($urlset->url as $url){
						$config[(string)$url->loc] = $url;
					}
				}
			?>
		<?php }else{ ?>
			<p class="intro xl">XMLサイトマップは現在作成されていません。「作成」を押してXMLサイトマップを作成してください。</p>
		<?php } ?>
		
		<table class="list-table lbreak">
			<thead>
				<tr>
					<th>名前</th>
					<th>URL</th>
					<th>最終更新時刻</th>
					<th width="100">更新頻度<br />(クローラーの訪問頻度)</th>
					<th>優先度</th>
					<th>表示</th>
				</tr>
			</thead>
			<tbody>
			<?php 
				
				//homeのデフォルト
				if(!isset($config[soycms_get_page_url("_home")])){
					$config[soycms_get_page_url("_home")] = array(
						"changefreq" => "daily",
						"priority" => 1
					);
				}
			
			foreach($pages as $id => $page){
				$pageUrl = soycms_get_page_url($page->getUri());
				$pageUrl = preg_replace('/index.html$/',"",$pageUrl);
				
				$pageConfig = (isset($config[$pageUrl])) ? (array)$config[$pageUrl] : array(
					"changefreq" => "weekly",
					"priority" => 0.5,
					"visible" => 1
				);
				
				$type = $page->getType();
				if($type[0] == ".")continue;	//hide feed
				if($type == "search")continue;	//hide search
				if($page->getConfigParam("public") != 1)continue;
				if(!$page->isDirectory()){
				
			?>
			<tr>
				<td>
					<?php echo $page->getName(); ?>
					<br />
					<span class="s"><?php echo $page->getUri(); ?></span>
				</td>
				<td>
					<input type="text" class="s-area liq-area" name="urlset[<?php echo $id; ?>][url]" value="<?php echo $pageUrl; ?>" />
				</td>
				<td>
					<input type="text" class="s-area liq-area" name="urlset[<?php echo $id; ?>][udate]" value="<?php echo date("c",$page->getUpdateDate()); ?>" />
				</td>
				<td>
					<select name="urlset[<?php echo $id; ?>][freq]">
						<?php 
							if(!@$pageConfig["changefreq"])@$pageConfig["changefreq"] = "weekly";
							foreach(array("always","hourly","daily","weekly","monthly","yearly","never") as $value){
								$checked = ($value == (string)@$pageConfig["changefreq"]) ? "selected" : "";
								echo "<option $checked>$value</option>";
							}
						?>
					</select>
				</td>
				<td>
					<select name="urlset[<?php echo $id; ?>][priority]">
						<?php 
							foreach(range(0,10) as $value){
								$value = $value / 10;
								$checked = ($value == $pageConfig["priority"]) ? "selected" : "";
								echo "<option $checked>$value</option>";
							}
						?>
					</select>
				</td>
				<td class="ce mid">
					<input type="hidden" name="urlset[<?php echo $id; ?>][visible]" value="0" />
					<input type="checkbox" name="urlset[<?php echo $id; ?>][visible]"" value="1" <?php if(!isset($pageConfig["visible"]) || $pageConfig["visible"] == 1){ ?>checked<?php } ?> />
				</td>
			</tr>
			<?php
				} // end is not directory
				
				if($page->isDirectory()){
					$entries = $entryDAO->getByDirectory($page->getId());
					
					foreach($entries as $entryId => $entry){
						if(!$entry->isOpen())continue;
						
						$entryUrl = soycms_get_page_url($page->getUri(),$entry->getUri());
						$entryConfig = (isset($config[$pageUrl])) ? (array)$config[$pageUrl] : array(
							"changefreq" => "weekly",
							"priority" => 0.5,
							"visible" => 1
						);
						?>
						
						<tr>
						<td>
							<?php echo $entry->getTitle(); ?>
							<br />
							<span class="s"><?php echo soycms_union_uri($page->getUri(),$entry->getUri()); ?></span>
						</td>
						<td>
							<input type="text" class="s-area liq-area" name="urlset[<?php echo $id . "_" . $entryId; ?>][url]" value="<?php echo $entryUrl; ?>" />
						</td>
						<td>
							<input type="text" class="s-area liq-area" name="urlset[<?php echo $id. "_" . $entryId; ?>][udate]" value="<?php echo date("c",$entry->getCreateDate()); ?>" />
						</td>
						<td>
							<select name="urlset[<?php echo $id . "_" . $entryId; ?>][freq]">
								<?php 
									if(!@$entryConfig["changefreq"])@$entryConfig["changefreq"] = "weekly";
									foreach(array("always","hourly","daily","weekly","monthly","yearly","never") as $value){
										$checked = ($value == (string)@$pageConfig["changefreq"]) ? "selected" : "";
										echo "<option $checked>$value</option>";
									}
								?>
							</select>
						</td>
						<td>
							<select name="urlset[<?php echo $id . "_" . $entryId; ?>][priority]">
								<?php 
									foreach(range(0,10) as $value){
										$value = $value / 10;
										$checked = ($value == $entryConfig["priority"]) ? "selected" : "";
										echo "<option $checked>$value</option>";
									}
								?>
							</select>
						</td>
						<td class="ce mid">
							<input type="hidden" name="urlset[<?php echo $id ."_" . $entryId; ?>][visible]" value="0" />
							<input type="checkbox" name="urlset[<?php echo $id ."_" . $entryId; ?>][visible]"" value="1" <?php if(!isset($entryConfig["visible"]) || $entryConfig["visible"] == 1){ ?>checked<?php } ?> />
						</td>
					</tr>
						
						
						<?php
					}	
				}
			?>
			<?php } ?>
			</tbody>
		</table>
		
		<div class="form-btn ce">
			<input type="submit" name="generate" class="l-btn" value="作成" />
		</div>
		
		</form>	
	</div>
</div>