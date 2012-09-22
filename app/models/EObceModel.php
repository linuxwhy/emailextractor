<?php

class EObceModel extends NObject{
	
	
	
	static function getFluent(){
		return dibi::select("*")
				->from("link_e_obce")
				->join("e_obce_email")->on("(link_id = link_e_obce.id)")
				->where('e_obce_email.checked = 1');
	}
	
	static function getNoCheckPages($limit = 10){
		return dibi::fetchAll("SELECT link,id,parent_id FROM [link_e_obce] WHERE check_date IS NULL OR check_date = '0000-00-00 00:00:00' LIMIT ".$limit);
	}
	
	
	static function getLinks($text){
		
		//zisti linky
		$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>"; 
		
		$return = array();
		
		if(preg_match_all("/$regexp/siU", $text, $matches, PREG_SET_ORDER)) { 
			foreach($matches as $match) {
				$link = $match[2];
//				
				if(isset($link[0]) AND $link[0] == "'"){
					$link = str_replace("'", "", $link);
				}
//				
//				if(isset($link[0]) AND $link[0] == '/'){
//					$link = 'www.e_obce.sk'.$link;
//				}

				$arr_link = parse_url($link);
				
				if(!isset($arr_link['host'])){
					$arr_link['host'] = 'www.e-obce.sk';
				}
				
//				print_r($arr_link);
				
				if(!isset($arr_link['path'])){
					$arr_link['path'] = '';
				}
//				echo $arr_link['host'].$arr_link['path'].' | <br />
//					';
				//ak adresa obsahuje www.e_obce.sk
				$link_to_return = $arr_link['host'].$arr_link['path'];
				if( strstr($link_to_return,'www.e-obce.sk/obec/')){
					
					$pom = explode(";jsessionid=", $arr_link['path']);
					
					if(isset($pom[0]) AND $pom[0]!=''){
						$arr_link['path'] = $pom[0];
					}
					
					$return[] = $arr_link['host'].$arr_link['path'];//// $match[2] = link address // $match[3] = link text } }
				}
			}
		}
		
		
		
		return $return;
		
	}


	static function addLinkWithMeta($link, $parent_id = NULL, $text){
//		$meta = @get_meta_tags($link);
//		print_r($meta);
		
		require_once LIBS_DIR.'/simplehtmldom/simple_html_dom.php';
		$html = str_get_html(  $text );			
		$text = NULL;	
		$title = NULL;
		$keywords = NULL;
		$description = NULL;
		
			
		foreach($html->find('title') as $element)
			  $title = $element->innertext; 
		
		foreach($html->find('meta[name=description]') as $element)
			  $description = $element->content;
		
		foreach($html->find('div[class=resultMainInfo]') as $element)
			   $text = $element->innertext; 
			
		if( !dibi::fetchSingle("SELECT 1 FROM [link_e_obce] WHERE link = %s",$link) ){
//			$title = @mb_convert_encoding( @$meta['title'] ,"utf-8", "auto");
//			$keywords = @mb_convert_encoding( @$meta['keywords'] ,"utf-8",  "auto");
//			$description = @mb_convert_encoding(@$meta['description'] , "utf-8",  "auto");
//			$title = @$meta['title'];
//			$keywords = @$meta['keywords'];
//			$description = @$meta['description'];
//
//
//			require_once LIBS_DIR.'/simplehtmldom/simple_html_dom.php';
//			$html = str_get_html(  $text );			
//			$text = NULL;	
//
//			foreach($html->find('div[class=resultMainInfo]') as $element)
//				   $text = $element->innertext; 


			$arr = array(	
				'parent_id'=>$parent_id,
				'link'=>$link,
				'title'=>$title,
				'keywords'=>$keywords,
				'description'=>$description,
				'add_date'=>dibi::datetime(),
				'info'=>$text
			);

			try{
				self::add($arr);
			}catch( Exception $e){
				
			};
			return true;
		}else{
			return false;
		}
	}
	
	static function add($values){
		dibi::query("INSERT INTO [link_e_obce] ", $values);
	}
	
	
	/*==================================
	Get url content and response headers (given a url, follows all redirections on it and returned content and response headers of final url)

	@return    array[0]    content
			array[1]    array of response headers
	==================================*/
	static function get_url_content( $url,  $javascript_loop = 0, $timeout = 5 )
	{
		$url = str_replace( "&amp;", "&", $url );
//echo $url;
		try{
			$cookie = tempnam (TEMP_DIR, "CURLCOOKIE");
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_HTTPHEADER,array (
				"Content-Type: text/xml; charset=utf-8",
				"Expect: 100-continue"
			));
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

//		print_r($content);
		

		
		return array( $content, $response );
		
	}
	
	
	static function update($values, $id){
		dibi::query("UPDATE [link_e_obce] SET",$values, "WHERE id = %i",$id);
	}
	
	static function getLinksForCheck($limit = 10){
		return dibi::fetchAll("SELECT * FROM [link_e_obce] WHERE checked_for_email = 0 LIMIT ".$limit);
	}
	
	static function addEmailForText($id_link, $text){
		//zisti emaili
		preg_match_all('/([\w\d\.\-\_]+)@([\w\d\.\_\-]+)/mi', $text, $matches);

		foreach($matches[0] as $m){
			try{
				self::addEmail($id_link, $m);
			}catch(DibiDriverException $e){}
		}
	}
	
	static function getCompanyInfo( $link ){
		
		require_once LIBS_DIR.'/simplehtmldom/simple_html_dom.php';
		$html = file_get_html(  $link );
		$links = array();
		$info = array();
		
		// Find all links
		foreach($html->find('a') as $element)
			   $links[] = $element->href; 
		
		foreach($links as $k=>$l){
			if( !strstr($l,'www.e_obce.sk') )
				unset($links[$k]);
		}
		
		foreach($html->find('div[class=resultMainInfo]') as $element)
			   $info[] = $element->innertext; 
		
//		print_r($info);
		return array('info'=>$info, 'links'=>$links);
	}
	
//	static function get_all_info($url) {
// 
//		// Create a new DOM Document to hold our webpage structure
//		$xml = new DOMDocument();
//		
////		$xml->loadHTML($text);
//
////		// Load the url's contents into the DOM
//		@$xml->loadHTMLFile($url);
//
//		// Empty array to hold all links to return
//		$links = array();
//
//		//Loop through each <a> tag in the dom and add it to the link array
//		foreach($xml->getElementsByTagName('a') as $link) {
//			$links[] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
//		}
//		
//		//Loop through each <a> tag in the dom and add it to the link array
//		foreach($xml->getElementsByTagName('a') as $link) {
//			$links[] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
//		}
//
//		//Return the links
//		return $links;
//	} 
//	
	
	
	static function addEmail($id_link, $email){
		
		dibi::query("INSERT INTO [e_obce_email]", array('email'=>$email,'link_id'=>$id_link));
	}
	
	
		/**
	Validate an email address.
	Provide email address (raw input)
	Returns true if the email address has the email
	address format and the domain exists.
	*/
	static function validEmail($email)
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