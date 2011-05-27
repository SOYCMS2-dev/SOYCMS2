<?php

class TimeZoneSelectComponent extends HTMLSelect{

	function init(){
		$options = array(
			"Pacific/Tongatapu" => "UTC+13(トンガ)",
			"Pacific/Fiji" => "UTC+12(ニュージーランド標準時)",
			"Asia/Magadan" => "UTC+11(ニューカレドニア",
			"Australia/Canberra" => "UTC+10(オーストラリア東部標準時)",
			"Australia/Darwin" => "UTC+9.5(中央オーストラリア標準時)",
			"Asia/Tokyo" => "UTC+9(日本標準時)",
			"Asia/Brunei" => "UTC+8(中国標準時)",
			"Asia/Krasnoyarsk" => "UTC+7 (タイ標準時)",
			"Asia/Rangoon" => "UTC+6.5 (ミャンマー標準時)",
			"Asia/Dhaka" => "UTC+6 (ロシア第5標準時)",
			"Asia/Kolkata" => "UTC+5.5 (インド標準時)",
			"Asia/Yekaterinburg" => "UTC+5 (ロシア第4標準時)",
			"Asia/Muscat" => "UTC+4 (ロシア第3標準時)",
			"Asia/Tehran" => "UTC+3.5 (イラン標準時)",
			"Asia/Kuwait" => "UTC+3 (モスクワ標準時)",
			"Europe/Minsk" => "UTC+2 (東ヨーロッパ標準時)",
			"Europe/Belgrade" => "UTC+1 (中央ヨーロッパ標準時)",
			"Europe/Dublin" => "UTC+0 (協定世界時)",
			"Atlantic/Azores" => "UTC-1 (ポルトガル標準時)",
			"Atlantic/South_Georgia" => "UTC-2 (南ジョージア島標準時)",
			"America/Argentina/Buenos_Aires" => "UTC-3 (ブラジル標準時)",
			"America/St_Johns" => "UTC-3.5 (ニューファンドランド標準時)",
			"America/Halifax" => "UTC-4 (アメリカ大西洋標準時)",
			"America/New_York" => "UTC-5 (アメリカ東部標準時)",
			"America/Tegucigalpa" => "UTC-6 (アメリカ中部標準時)",
			"America/Denver" => "UTC-7 (アメリカ山岳部標準時)",
			"America/Los_Angeles" => "UTC-8 (アメリカ太平洋標準時)",
			"America/Anchorage" => "UTC-9 (アラスカ標準時)",
			"Pacific/Honolulu" => "UTC-10 (ハワイ標準時)",
			"Pacific/Midway" => "UTC-11 (サモア標準時)",
			"Kwajalein" => "UTC-12 (マーシャル諸島標準時)",
			
		);
		$this->setOptions($options);
	}
}
?>