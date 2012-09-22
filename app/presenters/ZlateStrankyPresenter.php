<?php

set_time_limit(600);

class ZlatestrankyPresenter extends BasePresenter
{
	
	public function renderCheck(){

		$list = dibi::fetchAll("SELECT * FROM [zlatestranky_email] WHERE checked = 0 LIMIT 5000");
		foreach($list as $l){
			if( !ZlatestrankyModel::validEmail($l['email'])){
				echo $l['email'].'<br />
					';
				dibi::query("DELETE FROM [zlatestranky_email] WHERE email = %s",$l['email']);
			}else{
				dibi::query("UPDATE [zlatestranky_email] SET checked=1 WHERE email = %s",$l['email']);
			}
		}
		$this->terminate();
	}
	
	public function renderTree(){
		$this->template->tree = dibi::query("SELECT * FROM [link_zlatestranky]")->fetchAssoc('parent_id,id');
	}

	
	
	function handleParseLink(){
		
		
		//vytiahne z databazy neskontrolovane stranky
		$list = ZlatestrankyModel::getNoCheckPages(50);
//		print_r($list);exit;
		foreach($list as $l){
//			$l['link'] = 'www.zlatestranky.sk/firmy/Hri%C5%88ov%C3%A1/H600241/RAN%C4%8C+HAFERN%C3%8DK/;jsessionid=71523D3BC8D34367865D761748D33A96';
			//zisti obsah
			//			[0]=>'text',
//			 [1] => Array
//			(
//				[url] => http://www.zlatestranky.sk/
//				[content_type] => text/html;charset=UTF-8
//				[http_code] => 200
//				[header_size] => 687
//				[request_size] => 184
//				[filetime] => -1
//				[ssl_verify_result] => 0
//				[redirect_count] => 0
//				[total_time] => 0.277238
//				[namelookup_time] => 0.04397
//				[connect_time] => 0.087127
//				[pretransfer_time] => 0.087132
//				[size_upload] => 0
//				[size_download] => 9155
//				[speed_download] => 33022
//				[speed_upload] => 0
//				[download_content_length] => 9155
//				[upload_content_length] => 0
//				[starttransfer_time] => 0.234048
//				[redirect_time] => 0
//			)
			$content = ZlatestrankyModel::get_url_content($l['link']);

			
			
			$text = $content[0];
			//zisti linky

			$links = ZlatestrankyModel::getLinks($text);
//			print_r($links);exit;
//			$info = ZlatestrankyModel::getCompanyInfo($l['link']);
			
			ZlatestrankyModel::addEmailForText($l['id'], $text);
			
			//zapis linky
			foreach($links as $link){
				ZlatestrankyModel::addLinkWithMeta($link, $l['id'], $text);				
			}
			
			//zapis ze je linka skontrolovana
			ZlatestrankyModel::update( array('check_date'=>dibi::datetime()), $l['id']);
		}
	
	}
	
	
	function handleParseEmail(){
		
		$links = ZlatestrankyModel::getLinksForCheck();
		foreach($links as $link){
			
		}
	}
}