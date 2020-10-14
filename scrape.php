<?php

// require the file 
require_once('PDO.DB.class.php');
require_once('simple_html_dom.php');

//create db object
$db = new DB();
//target url
$link = "https://considertheconsumer.com/";

//set up curl 
//Initiate curl
$ch = curl_init();
// Set the url
curl_setopt($ch,CURLOPT_URL,$link);
// Will return the response, if false it print the response (we want to capture it in a variable $result)
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
//set user agent
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
// Execute
$result = curl_exec($ch);
// Closing
curl_close($ch);

//collect all URLs into array
$urls = array();

// Create a DOM object from result
$html = str_get_html($result);

if(!empty($html)) {
	//get all urls and add into array
	foreach($html->find('.postTitle a') as $postTitle) {
		array_push($urls,$postTitle->href);
	}
}


//collect post fields
$curl_arr = array();
//set up multi curl 
$master = curl_multi_init();
//count urls
$count_url = count($urls);

//set up multi curl
for($i = 0; $i < $count_url; $i++){
    $url =$urls[$i];
    $curl_arr[$i] = curl_init($url);
    curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_arr[$i], CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
    curl_multi_add_handle($master, $curl_arr[$i]);
}

//Execute multi curl 
do {
    curl_multi_exec($master,$running);
} while($running > 0);


for($i = 0; $i < $count_url; $i++){
	
    // Create a DOM object from result
    $result = str_get_html(curl_multi_getcontent( $curl_arr[$i] ));
	
	if(!empty($result)) {
		
		//Initialize variables
		$category = $title = $author = $publish_date = $content = "";
		
		$postInfo = $result->find('.postInfo')[0]->plaintext;
		
		$match = array();
		//Use regular expression to extract the author
		if (preg_match('/By (.*?) on/', $postInfo, $match) == 1) {
			
			$author = $match[1];
		}
		//Use regular expression to extract the publish date
		if (preg_match('/on (.*)/', $postInfo, $match) == 1) {
			
			$publish_date = $match[1];
		}
		
		//Get category
		$category = $result->find('.contentWrapper .postCat a')[0]->plaintext;
		//Get title
		$title = $result->find('.contentWrapper .postTitle')[0]->plaintext;
		//Get link
		$link = $result->find('.contentWrapper a')[0]->href;
		
		//collect all contents
		foreach($result->find('.contentWrapper p span') as $postContent) {
			
			$content .= $postContent->plaintext;
			$content .="\r\n";
		}
		
		//Add all fields to database
		$exec = $db->addPost($category, $title, $link, $author, $publish_date, $content);
		if($exec){
			echo "<br>\"".$title."\" inserted...";
		}else{
			echo "<br>Failed to insert post \"".$title."\"...";
		}
		
	}
}
curl_multi_close($master);
echo "<br>Scraping data completed!";
?>












