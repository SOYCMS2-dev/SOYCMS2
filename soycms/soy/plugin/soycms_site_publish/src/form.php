<form method="post">
<div class="section">
	<div class="title">
		<h3>書き出し先の設定</h3>
	</div>
	<div class="content">
		<table class="form-table">
			<tr>
				<th>書き出し先のディレクトリ</th>
				<td>
					<input type="text" class="m-area liq-area" name="output_dir" value="<?php echo $output_dir; ?>" />
				</td>
			</tr>
			<tr>
				<th>書き出し先のサイトのURL</th>
				<td>
					<input type="text" class="m-area liq-area" name="output_url" value="<?php echo $output_url; ?>" />
				</td>
			</tr>
			<?php if(file_exists($output_dir)){ ?>
			<tr>
				<th>書き出し先のディレクトリ</th>
				<td>
					<?php echo implode(",",soy2_scandir($output_dir)); ?>
				</td>
			</tr>
			<tr>
				<th>書き出しのログ</th>
				<td>
					<pre><?php echo @file_get_contents($output_dir . "/.export.log"); ?></pre>
				</td>
			</tr>
			<tr>
				<th>アップロードのログ</th>
				<td>
					<pre><?php echo @file_get_contents($output_dir . "/.upload.log"); ?></pre>
				</td>
			</tr>
			<?php } ?>
				
			
		</table>
	</div>
</div>

<div class="section">
	<div class="title">
		<h3>FTPの設定</h3>
	</div>
	<div class="content">
		<table class="form-table">
			<tr>
				<th>ホスト／ポート</th>
				<td>
					<input type="text" class="s-area" name="ftp[host]" value="<?php echo @$ftp["host"]; ?>" />：
					<input type="text" class="s-area" name="ftp[port]" value="<?php echo @$ftp["port"]; ?>" size="4" />
					
					<input type="hidden" name="ftp[secure]" value="0" />
					<input id="is_secure" type="checkbox" name="ftp[secure]" value="1" <?php if(@$ftp["secure"]){echo "checked";} ?>/>
					<label for="is_secure">FTP-SSL</label>
				</td>
			</tr>
			<tr>
				<th>ID</th>
				<td>
					<input type="text" class="s-area" name="ftp[id]" value="<?php echo @$ftp["id"]; ?>" />
				</td>
			</tr>
			<tr>
				<th>パスワード</th>
				<td>
					<input type="text" class="s-area" name="ftp[password]" value="<?php echo @$ftp["password"]; ?>" />
				</td>
			</tr>
			<tr>
				<th>ディレクトリ</th>
				<td>
					<input type="text" class="s-area" name="ftp[directory]" value="<?php echo @$ftp["directory"]; ?>" />
				</td>
			</tr>
			<tr>
				<th>オプション</th>
				<td>
					<p>
						<input type="checkbox" id="option_overwite" name="ftp[option][overwite]" value="1" <?php if(@$ftp["option"]["overwite"] == 1){ ?>checked<?php } ?> />
						<label for="option_overwite">ファイルが存在した場合上書きを行う</label>
					</p>
					<p>
						<input type="checkbox" id="option_clear" name="ftp[option][clear]" value="1" <?php if(@$ftp["option"]["clear"] == 1){ ?>checked<?php } ?> />
						<label for="option_clear">サーバ側のファイルを削除する</label>
					</p>
				</td>
			</tr>
			<?php if($connect != -1){ ?>
			<tr>
				<th>接続状況</th>
				<td>
				<?php if($connect){ ?>
					<p class="xxl">接続に成功しました@<?php echo $connect; ?></p>
				<?php }else{ ?>
					<p class="xxl" style="color:red;">サーバに接続出来ませんでした。</p>
				<?php } ?>
				</td>
			</tr>
			<?php } ?>
		</table>
	</div>
</div>

<div class="section">
	<div class="content">
		<div class="ce">
			<input type="submit" class="l-btn" name="test" value="接続テスト" onclick="return confirm('設定を保存して接続テストを行います。');" />
			<input type="submit" class="l-btn" name="submit" value="書き出し" />
			<?php if(file_exists($output_dir . ".export.log")){ ?>
			<p>
				<input type="submit" class="l-btn" name="upload" value="アップロード実行" />
			</p>
			<?php } ?>
		</div>
	</div>
</div>

</form>