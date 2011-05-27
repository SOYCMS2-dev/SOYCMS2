<?php
$pages = SOY2DAO::find("SOYCMS_Page");
$entryDAO = SOY2DAOFactory::create("SOYCMS_EntryDAO");
?>
<div class="section">
	<div class="title">
		<h3>コンテンツキャッシュの設定</h3>
	</div>
	<div class="content">
	
		<form method="post">
		
			<div class="form-section">
				<div class="label">
					<h4>更新時の動作</h4>
				</div>
				<div class="item">
					<ul>
						<li>
							<input type="radio" name="config[cache_type]" id="cache_type_none" value="none" <?php if(@$config["cache_type"] == "none" || !@$config["cache_type"]){ ?>checked<?php } ?>>
							<label for="cache_type_none">何もしない</label>
						</li>
						<li>
							<input type="radio" name="config[cache_type]" id="cache_type_clear" value="clear" <?php if(@$config["cache_type"] == "clear"){ ?>checked<?php } ?>>
							<label for="cache_type_clear">記事更新時に全てのキャッシュを削除する</label>
						</li>
						<li>
							<input type="radio" name="config[cache_type]" id="cache_type_dir" value="directory" <?php if(@$config["cache_type"] == "directory"){ ?>checked<?php } ?>>
							<label for="cache_type_dir">記事更新時に該当ディレクトリ以下のキャッシュを削除する</label>
						</li>
						<li>
							<input type="radio" name="config[cache_type]" id="cache_type_entry" value="entry" <?php if(@$config["cache_type"] == "entry"){ ?>checked<?php } ?>>
							<label for="cache_type_entry">記事更新時に該当記事のキャッシュを削除する</label>
						</li>
					</ul>
				</div>
				<div class="label">
					<h4>フットプリント</h4>
				</div>
				<div class="item">
					<input type="hidden" name="config[footprint]" value="0" />
					<input id="config_footprint" type="checkbox" name="config[footprint]" value="1" <?php if(@$config["footprint"] == 1 || !@$config["footprint"]){ ?>checked<?php } ?>>
					<label for="config_footprint">キャッシュ利用時にフットプリント(&lt;!-- cache:キャッシュ読み込み時間 sec. ---&gt;)を表示する</label>
				</div>
			</div>
			
			<div class="form-section">
				<div class="label">
					<h3>各ディレクトリの設定</h3>
				</div>
				<div class="item">
					<table class="list-table">
						<thead>
							<tr>
								<th width="40">有効</th>
								<th>ディレクトリ名</th>
								<th width="80">有効期間</th>
							</tr>
						</thead>
						<?php foreach($pages as $page){ 
							if(!$page->isDirectory())continue;
							$url = $page->getUri();
							$conf = (isset($config["pages"]) && isset($config["pages"][$url])) ? $config["pages"][$url] : array(
								"acive" => 0,
								"limit" => 180,
							);
						?>
							<tr>
								<td class="ce">
									<input type="hidden" name="config[pages][<?php echo $url; ?>][active]" value="0" />
									<input type="checkbox" name="config[pages][<?php echo $url; ?>][active]" value="1" <?php if(@$conf["active"] == 1){ ?>checked<?php } ?>>
								</td>
								<td>
									<strong><?php echo $page->getName(); ?></strong>
									
									(<?php echo soycms_get_page_url($page->getUri()) ?>)
								</td>
								<td>
									<p class="m">
									<input type="text" class="s-area" name="config[pages][<?php echo $url; ?>][limit]" value="<?php echo @$conf["limit"]; ?>" size="4" />
									 分
									</p>
								</td>
							</tr>
						<?php } ?>
					</table>
				</div>
			</div>
		
		
			<div class="form-btn ce">
				<input type="submit" name="generate" class="l-btn" value="設定" />
			</div>
		
		</form>	
	</div>
</div>