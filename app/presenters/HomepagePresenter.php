<?php

/**
 * My NApplication
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */



/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
define('HOW_MANY_DOMAINS_CHECK', 25);
define('HOW_MANY_PAGES_CHECK', 300);
set_time_limit(180);


class HomepagePresenter extends BasePresenter
{
	protected $debug = true;

	

		
	//http://emailextractor.tvorbaeshop.sk/www/homepage/check
	public function renderCheck(){
//		
//		for($i=5000; $i<15800; $i++){
//			echo $i.'=>'.$this->toBase($i)."<br />";
//		}
		
	
		
		$list = dibi::fetchAll("SELECT * FROM [email] WHERE checked = 0 LIMIT 5000");
		foreach($list as $l){
			if( !$this->validEmail($l['email'])){
				echo $l['email'].'<br />
					';
				dibi::query("DELETE FROM [email] WHERE email = %s",$l['email']);
			}else{
				dibi::query("UPDATE [email] SET checked=1 WHERE email = %s",$l['email']);
			}
		}
		$this->terminate();
	}

	


//	http://emailextractor.tvorbaeshop.sk/www/homepage/domain?domain=eurowagon.sk
	// checkne danu domenu a vypise emaili
	public function renderDomain($domain){
//		$this->debug = true;
		$domains = dibi::fetchAll("SELECT * FROM [domain] WHERE domain = %s",$domain);
		$this->renderDefault($domains);
	}

	public function pr($var){
		if( $this->debug )
				print_r($var);
	}

	public function renderDefault($domains = false)
	{

		
		$this->template->anyVariable = 'any value';

		if(!$domains)
			$domains = dibi::fetchAll("SELECT * FROM [domain] WHERE [check] = 0 LIMIT ".HOW_MANY_DOMAINS_CHECK);
		
//		print_r($domains);exit;
		$allowed_ext = array('','html', 'php', 'htm', 'asp');
		foreach($domains as $d){

			$this->pr('Domena: '.$d['domain'].' ');

			$check_all = true;

			$links_on_page = $this->getPage($d['domain']);
			
			$count_all_links_on_page = $count_link_on_first_page = @count($links_on_page['links']);

			$checked_links = array();
			
			$this->pr('Pocet liniek: '.$count_all_links_on_page);

			//ak je pocet domen vacsi ako 100, zober iba tie co obsahuju slovo kontakt alebo contact
			if( $count_all_links_on_page > HOW_MANY_PAGES_CHECK ) $check_all = false;


			//zisti dalsie podstranky
			if(!empty($links_on_page['links']))

				foreach($links_on_page['links'] as $l){
					
					
						if(!$check_all ){
							if( strstr($l, 'kontakt') OR strstr($l, 'contact') )
								$sublinks = $this->getPage($d['domain'], $l);
						}else{
							$sublinks = $this->getPage($d['domain'], $l);
						}

						$count_all_links_on_page+=@count($sublinks['links']);
					

				}

			
			//zapise ze sa stranka skontrolovala
			$arr = array(
				'check'=>1,
				'check_date'=>date('Y-m-d H:i:s'),
				'count_link'=>$count_link_on_first_page,
			);
			
			dibi::query("UPDATE [domain] SET",$arr,"WHERE domain = %s",$d['domain']);
		}

//		exit;
	}
//http://simplehtmldom.sourceforge.net/
//	// Create DOM from URL or file
//$html = file_get_html('http://www.google.com/');
//
//// Find all images
//foreach($html->find('img') as $element)
//       echo $element->src . '<br>';
//
//// Find all links
//foreach($html->find('a') as $element)
//       echo $element->href . '<br>';

	function getPage( $domain, $link = ''){

		
		ini_set('user_agent', 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12');

		if($link!=''){
			
			if($link[0] != '/'){
				
				$pom1 = "http://www.".$domain;
				$pom2 = "http://".$domain;
				if(strstr($link, $pom1) OR strstr($link, $pom2)){					
					$p = explode($domain,$link);
					$link = $p[1];
				}else{
					
					return false;
				}

			}
		}

		
		
		$url = "http://www.".$domain.$link;

		//ak uz je pridana tak nekontrolovat
		if( dibi::fetchSingle("SELECT 1 FROM [link] WHERE link = %s",$link) ){
			return false;
		}

		$res = $this->get_url($url);
		
		$this->pr($url);
		
		if( !strstr($res[1]['content_type'], 'text/html'))
			return false;

		$text = $res[0];

		//zisti linky
		$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>"; 
		
		$return = array();
		
		if(preg_match_all("/$regexp/siU", $text, $matches, PREG_SET_ORDER)) { 
			foreach($matches as $match) {
				$return['links'][] = $match[2];//// $match[2] = link address // $match[3] = link text } }
			}
		}

		//zisti emaili
		preg_match_all('/([\w\d\.\-\_]+)@([\w\d\.\_\-]+)/mi', $text, $matches);


		$this->pr($matches[0]);
		
		foreach($matches[0] as $m){
			$this->addEmail($domain, $m);
		}


		//zisti info o stranke
		
		$meta = @get_meta_tags($url);

		$this->addMeta($domain, $url, $meta);

		return $return;
		
	}
	
	
	function addEmail($domain, $email){
		$p = dibi::fetchSingle("SELECT 1 FROM [email] WHERE email = %s",$email);
		echo $email.'<br />
			';
		if(!$p){
			try{
				dibi::query("INSERT INTO [email] ", array('domain'=>$domain,'email'=>$email, 'active'=>1));
			}catch(Exception $e){
				
			}
		}
	}


	function addMeta($domain, $link, $meta){
		$this->pr('Pridanie meta: '.$link.' - '.$meta.'<br />
			' );
		if( !dibi::fetchSingle("SELECT 1 FROM [link] WHERE link = %s",$link) ){
			$title = @mb_convert_encoding( @$meta['title'] ,"utf-8", "auto");
			$keywords = @mb_convert_encoding( @$meta['keywords'] ,"utf-8",  "auto");
			$description = @mb_convert_encoding(@$meta['description'] , "utf-8",  "auto");


			$arr = array(
				'domain'=>$domain,
				'link'=>$link,
				'title'=>$title,
				'keywords'=>$keywords,
				'description'=>$description
			);

			dibi::query("INSERT INTO [link] ", $arr);
			return true;
		}else{
			return false;
		}
	}


	function handleGetDomain(){
		$domain = WWW_DIR.'/domeny_1.txt';

		$row = 1;
		if (($handle = fopen($domain, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
				if($data[0]){
					if(dibi::fetchSingle("SELECT 1 FROM [domain] WHERE domain = %s",$data[0])!=1){
						dibi::query("INSERT INTO [domain]", array('domain'=>$data[0]));
					}
				}
			}
			fclose($handle);
		}
	}


	/*==================================
	Get url content and response headers (given a url, follows all redirections on it and returned content and response headers of final url)

	@return    array[0]    content
			array[1]    array of response headers
	==================================*/
	function get_url( $url,  $javascript_loop = 0, $timeout = 5 )
	{
		$url = str_replace( "&amp;", "&", urldecode(trim($url)) );

		try{
			$cookie = tempnam (TEMP_DIR, "CURLCOOKIE");
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
			@curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
			@curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $ch, CURLOPT_ENCODING, "" );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
			curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
			curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
			$content = curl_exec( $ch );
			$response = curl_getinfo( $ch );
			curl_close ( $ch );
		}catch(Exception $e){
			
		}

//		$this->pr($content);
		

		
		return array( $content, $response );
		
	}


	/**
	Validate an email address.
	Provide email address (raw input)
	Returns true if the email address has the email
	address format and the domain exists.
	*/
	function validEmail($email)
	{
	   $isValid = true;
	   $atIndex = strrpos($email, "@");
	   if (is_bool($atIndex) && !$atIndex)
	   {
		  $isValid = false;
	   }
	   else
	   {
		  $domain = substr($email, $atIndex+1);
		  $local = substr($email, 0, $atIndex);
		  $localLen = strlen($local);
		  $domainLen = strlen($domain);
		  if ($localLen < 1 || $localLen > 64)
		  {
			 // local part length exceeded
			 $isValid = false;
		  }
		  else if ($domainLen < 1 || $domainLen > 255)
		  {
			 // domain part length exceeded
			 $isValid = false;
		  }
		  else if ($local[0] == '.' || $local[$localLen-1] == '.')
		  {
			 // local part starts or ends with '.'
			 $isValid = false;
		  }
		  else if (preg_match('/\\.\\./', $local))
		  {
			 // local part has two consecutive dots
			 $isValid = false;
		  }
		  else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
		  {
			 // character not valid in domain part
			 $isValid = false;
		  }
		  else if (preg_match('/\\.\\./', $domain))
		  {
			 // domain part has two consecutive dots
			 $isValid = false;
		  }
		  else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
					 str_replace("\\\\","",$local)))
		  {
			 // character not valid in local part unless
			 // local part is quoted
			 if (!preg_match('/^"(\\\\"|[^"])+"$/',
				 str_replace("\\\\","",$local)))
			 {
				$isValid = false;
			 }
		  }
		  if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
		  {
			 // domain not found in DNS
			 $isValid = false;
		  }
	   }
	   return $isValid;
	}


}
