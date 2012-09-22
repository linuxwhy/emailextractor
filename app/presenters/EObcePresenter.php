<?php

set_time_limit(600);

class EobcePresenter extends BasePresenter
{
	
	public function renderCheck(){

		$list = dibi::fetchAll("SELECT * FROM [e_obce_email] WHERE checked = 0 LIMIT 5000");
		foreach($list as $l){
			if( !EObceModel::validEmail($l['email'])){
				echo $l['email'].'<br />
					';
				dibi::query("DELETE FROM [e_obce_email] WHERE email = %s",$l['email']);
			}else{
				dibi::query("UPDATE [e_obce_email] SET checked=1 WHERE email = %s",$l['email']);
			}
		}
		$this->terminate();
	}
	
	public function renderTree(){
		$this->template->tree = dibi::query("SELECT * FROM [link_e_obce]")->fetchAssoc('parent_id,id');
	}

	public function renderSimilarEmail(){
		$list = dibi::fetchAll("SELECT * FROM [e_obce_email]");
		
		$this->template->emails_tree = array();
		
		foreach($list as $k=>$l){
			list($prefix, $domain) = explode("@", $l['email']);
			
			$this->template->emails_tree[$domain][] = $l['email'];
		}
		
		
	}
	
	
	function handleParseLink(){
		
		
		//vytiahne z databazy neskontrolovane stranky
		$list = EObceModel::getNoCheckPages(100);
//		print_r($list);exit;
		foreach($list as $l){
//			$l['link'] = 'www.e_obce.sk/firmy/Hri%C5%88ov%C3%A1/H600241/RAN%C4%8C+HAFERN%C3%8DK/;jsessionid=71523D3BC8D34367865D761748D33A96';
			//zisti obsah
			//			[0]=>'text',
//			 [1] => Array
//			(
//				[url] => http://www.e_obce.sk/
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
			$content = EObceModel::get_url_content($l['link']);

			
			
			$text = $content[0];
			//zisti linky

			$links = EObceModel::getLinks($text);
//			print_r($links);exit;
//			$info = EObceModel::getCompanyInfo($l['link']);
			
			EObceModel::addEmailForText($l['id'], $text);
			
			//zapis linky
			foreach($links as $link){
				EObceModel::addLinkWithMeta($link, $l['id'], $text);				
			}
			
			//zapis ze je linka skontrolovana
			EObceModel::update( array('check_date'=>dibi::datetime()), $l['id']);
		}
	
	}
	
	
	function handleParseEmail(){
		
		$links = EObceModel::getLinksForCheck();
		foreach($links as $link){
			
		}
	}
}