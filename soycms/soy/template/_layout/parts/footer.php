<?php
$footer_default = <<<HTML
<div class="navi">
	<ul>
		<li><a href="http://www.soycms2.net/document/" target="_blank">ドキュメント(マニュアル)</a></li> 
		<li><a href="http://twitter.com/SOYCMS2_dev" target="_blank">SOY CMS2 Developer Team Twitter</a></li>
		<li><a href="http://www.facebook.com/SOYCMS" target="_blank">SOY CMS2 Facebook</a></li>
		<li class="last"><a href="http://www.soycms2.net/feedback/" target="_blank">フィードバックする</a></li>
	</ul>
</div>
HTML;
echo SOYCMS_DataSets::get("parts.footer",$footer_default);
?>
<div class="copyright">
	<address>Powered by <a href="http://www.soycms2.net/">SOY CMS2</a></address>
</div>