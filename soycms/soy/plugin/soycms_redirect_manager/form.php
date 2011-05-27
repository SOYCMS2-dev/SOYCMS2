<div class="section">
	<div class="title">
		<h3>リダイレクトの条件と遷移先を指定してください。</h3>
	</div>
	<div class="content">
		<form method="post">
		
			<p>上から優先的に処理されます。</p>
			
			<div class="form-btn ce save_order_btn break" style="display:none;">
				<input type="submit" name="save_order" class="l-btn" value="表示順の保存" />
			</div>
		
			<table class="list-table break">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th>名前</th>
						<th>遷移先</th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($config_array as $key => $config){ 
						$formName = "Rule[$key]";
					?>
					<tr class="rule_<?php echo $key; ?> rule-list">
						<td class="ce"><strong><?php echo ($key+1); ?></strong></td>
						<td>
							<?php echo $config["name"]; ?>
						</td>
						<td>
							<?php echo $config["url"]; ?> to <?php echo $config["to"]; ?>
						</td>
						<td>
							<input class="s-btn" type="button" value="設定" onclick="$('#config_row_<?php echo $key; ?>').toggle();" />
							<a href="javascript:void(0);" onclick="$('.remove_<?php echo $key; ?>').toggle();">削除</a>
							<span style="float:right;">
								<input class="s-btn" type="button" value="↑" onclick="rule_move(1,'.rule_<?php echo $key; ?>');" />
								<input class="s-btn" type="button" value="↓" onclick="rule_move(0,'.rule_<?php echo $key; ?>');" />
							</span>
							
							<p class="remove_<?php echo $key; ?>" style="display:none;" >
								<input class="s-btn" type="submit" name="remove[<?php echo $key; ?>]" value="このルールを削除" />
							</p>
						</td>
					</tr>
					<tr class="rule_<?php echo $key; ?>" id="config_row_<?php echo $key; ?>" style="display:none;">
						<td colspan="4">
						<!-- 設定フォーム -->
						<table class="form-table break" style="border:solid 1px #999;border-collapse:separate;"> 
							<tr>
								<th>説明</th>
								<td style="border-right:none;">
									<p>このルールの簡単な説明を入力してください</p>
									<input type="text" class="s-area liq-area" name="<?php echo $formName; ?>[name]" value="<?php echo $config["name"]; ?>" />
								</td>
								<td colspan="2" style="border-right:none;"></td>
							</tr>
							<tr>
								<th>URL</th>
								<td style="border-right:none;"> 
									<input type="text" class="s-area liq-area" name="<?php echo $formName; ?>[url]" value="<?php echo $config["url"]; ?>" />
								</td>
								<th>URLオプション</th>
								<td style="border-right:none;">
									<input type="checkbox" id="new_rule_regex" name="<?php echo $formName; ?>[regex]" value="1" <?php if(@$config["regex"]){echo "checked";} ?>/>
									<label for="new_rule_regex">正規表現</label>
								</td>
							</tr>
							<tr>
								<th>閲覧環境</th>
								<td style="border-right:none;"> 
									<p>閲覧環境で制限をかける場合はチェックを入れてください。</p>
									<?php $this->printUserAgentCheck($formName . "[agent]",@$config["agent"]); ?>
								</td>
								<td colspan="2" style="border-right:none;"></td>
							</tr>
							<tr>
								<th>遷移先</th>
								<td style="border-right:none;"> 
									<input type="text" class="s-area liq-area" name="<?php echo $formName; ?>[to]" value="<?php echo $config["to"]; ?>"/>
								</td>
								<th>オプション</th>
								<td style="border-right:none;">
									<input type="text" class="s-area" name="<?php echo $formName; ?>[code]" value="<?php echo $config["code"]; ?>" />
									<br />
									<select name="<?php echo $formName; ?>[count]" style="width:100px;">
										<option value="">毎回</option>
										<option value="once" <?php if($config["count"] == "once"){echo "selected"; } ?>>1回(Cookieが使用可能な場合のみ)</option>
									</select>
								</td>
							</tr>
						</table>
						
						<div class="form-btn ce">
							<input type="submit" name="save[<?php echo $key; ?>]" class="l-btn" value="保存" />
						</div>
						<!-- /設定フォーム -->
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
			
			<div class="form-btn ce save_order_btn" style="display:none;">
				<input type="submit" name="save_order" class="l-btn" value="表示順の保存" />
			</div>
			
			<h3>ルールの追加</h3>
			
			<fieldset style="border:solid 1px gray;padding:8px;" class="break">
				<legend><h4>ルールの追加</h4></legend>
			
				<table class="form-table"> 
					<tr>
						<th>説明</th>
						<td>
							<p>このルールの簡単な説明を入力してください</p>
							<input type="text" class="s-area liq-area" name="NewRule[name]" value="" />
						</td>
						<td colspan="2"></td>
					</tr>
					<tr>
						<th>URL</th>
						<td> 
							<input type="text" class="s-area liq-area" name="NewRule[url]" value="<?php echo soycms_get_site_url(""); ?>" />
						</td>
						<th>URLオプション</th>
						<td>
							<input type="checkbox" id="new_rule_regex" name="NewRule[regex]" value="1" />
							<label for="new_rule_regex">正規表現</label>
						</td>
					</tr>
					<tr>
						<th>閲覧環境</th>
						<td> 
							<p>閲覧環境で制限をかける場合はチェックを入れてください。</p>
							<?php $this->printUserAgentCheck("NewRule[agent]"); ?>
						</td>
						<td colspan="2"></td>
					</tr>
					<tr>
						<th>遷移先</th>
						<td> 
							<input type="text" class="s-area liq-area" name="NewRule[to]" value="http://" />
						</td>
						<th>オプション</th>
						<td>
							<input type="text" class="s-area" name="NewRule[code]" value="301" />
							<br />
							<select name="NewRule[count]" style="width:100px;">
								<option value="">毎回</option>
								<option value="once">1回(Cookieが使用可能な場合のみ)</option>
							</select>
						</td>
					</tr>
				</table>
				
				<div class="form-btn ce">
					<input type="submit" name="new" class="l-btn" value="追加" />
				</div>
			</fieldset>
			
			<fieldset style="border:solid 1px gray;padding:8px;">
				<legend><h4>一括設定</h4></legend>
			
				<div class="item zbreak">
					<p class="intro">
						「説明,遷移元,遷移先」の順に記述してください。
					</p>
					<textarea class="resizable m-area liq-area" name="NewRules" rows="5"></textarea>
				</div>
				
				<div class="form-btn ce">
					<input type="submit" name="new_list" class="l-btn" value="追加" />
					
					<p>
					<input type="checkbox" id="clear_config" name="clear_config" value="1" />
					<label for="clear_config">既存の項目を上書きする</label>
					</p>
				</div>
			</fieldset>
			
		
			
		
		</form>
	</div>
</div>

<script type="text/javascript">
function rule_move(flag, ele){
	
	//move up
	if(flag){
		target = $($(ele)[0]).prev().prev();
		target.before($(ele));
	}else{
		target = $($(ele)[1]).next().next();
		target.after($(ele));
	}
	
	$(".save_order_btn").show();
}
</script>