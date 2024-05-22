<?php

function pre($html){
    echo '<pre>';
    if(gettype($html) === 'string')
    echo $html;
    else
    print_r($html);
    echo '</pre>';
}

function fetchPage($link){
    $pageContent =false;
    try {
        $pageCurl = curl_init($link);
        curl_setopt($pageCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($pageCurl, CURLOPT_BINARYTRANSFER, true);
        $pageContent = curl_exec($pageCurl);
        if($pageContent === false){
            throw new Exception('Failed to fetch page: ' . curl_error($pageCurl));
        }
        curl_close($pageCurl);
    } catch (Exception $e) {
        echo 'Error fetching page';
    }
    return $pageContent;
}

function  changeDomain ($downloadImage) {
   return function ($matches) use($downloadImage){ 
    $home_url = home_url();
    $home_parsed_url = parse_url($home_url);
    $domain_name = $home_parsed_url['host'];
	   if(isset($home_parsed_url['path']))
		   $domain_name .= $home_parsed_url['path'];
	
  		$url = $matches[1];
        $parsedUrl = parse_url($url);
        if (pathinfo($parsedUrl['path'], PATHINFO_EXTENSION) !== 'pdf') {
            if(isset($parsedUrl['host']) && ($parsedUrl['host'] == 'www.illinoistreasurer.gov' || $parsedUrl['host'] == 'illinoistreasurer.gov')){
                $url = str_replace($parsedUrl['host'], $domain_name, $url);
				$url = str_replace(["'", "(", ")"], "", $url);
            }
			else if(!isset($parsedUrl['host'])){
                $url = str_replace(["'", "(", ")"], "", 'https://' . $domain_name . $url) ;
			}
        }
        else{
            $attachment_id = $downloadImage($url);
            $attachment_url = wp_get_attachment_url($attachment_id);
            if ($attachment_url) 
                $url = $attachment_url;            
        }
        return 'href="' . $url . '"';
    };
}

function downloadImage($imageUrl){
    try {
        
        if($imageUrl !== '' && $imageUrl !== '../#' && $imageUrl !== "../../#"){          
            $imageData = file_get_contents($imageUrl);
            if($imageData === false){
                $imageData = file_get_contents("https://www.illinoistreasurer.gov" . $imageUrl);
                if($imageData === false){
                    $imageData = file_get_contents(str_replace(' ', '%20', strtolower("https://illinoistreasurergovprod.blob.core.usgovcloudapi.net" .  $imageUrl)));
                    if($imageData === false){
                        throw new Exception('Failed to download image from URL: ' . $imageUrl);
                    }
                }
            }        
            $upload = wp_upload_bits(basename($imageUrl), null, $imageData);        
            if($upload['error']) {
                throw new Exception('Failed to upload image: ' . $upload['error']);
            }

            // Insert attachment into the media library
            $attachment = array(
                'post_mime_type' => $upload['type'],
                'post_title' => basename($imageUrl),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attachment_id = wp_insert_attachment($attachment, $upload['file']);
            if (is_wp_error($attachment_id)) {
                throw new Exception('Failed to insert attachment: ' . $attachment_id->get_error_message());
            }
            
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            // Generate attachment metadata
            $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
            wp_update_attachment_metadata($attachment_id, $attach_data);
            return $attachment_id;
        }
        else{
            throw new Exception('Not Valid URL' );
        }
    } catch (Exception $e) {
        // Handle the exception, you can log it or display an error message
        echo '<br/>Error downloading image: ' . $e->getMessage() . '<br/><b>' . $imageUrl . '</b><br/><br/>';
    }
}

function transformArray($inputArray) {
    $transformedArray = [];

        $longestLength = 0;
        foreach ($inputArray as $values) {
            $length = count($values);
            if ($length > $longestLength) {
                $longestLength = $length;
            }
        }

        for ($i = 0; $i < $longestLength; $i++) {
            $newContent = [];
            foreach ($inputArray as $key => $values) {
                if (isset($values[$i])) {
                    $newContent[$key] = $values[$i];
                } else {
                    $newContent[$key] = '';
                }
            }
            $transformedArray[] = $newContent;
        }
    return $transformedArray;
}

function scrapePages($index = 0){
        require_once (__DIR__ . '/templates.php');
        $pagesContent = [];

        $pages = getAllPages();
        $page = $pages['ready_for_scrape'][$index];
		$page['content_structure'] = $templates[$page['template']];
        $htmlString = fetchPage($page['scrap_from']);
        if(!$htmlString) return;
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML($htmlString);
        $xpath = new DOMXPath($doc);

        $pagesContent[$index] = $page;
		$pagesContent[$index]['content_structure'] = [];
        foreach($page['content_structure'] as $key => $contentStructure){
            $selector =  str_replace(' ', '//', $contentStructure['selector']);
            $contentElements = $xpath->evaluate($selector) ;
            $contentValue ='';
            if($contentStructure['type'] === 'text')
                $contentValue = isset($contentElements[0]) ? $contentElements->item(0)->textContent.PHP_EOL  :   "";
            else if($contentStructure['type'] === 'image'){
                $imageUrl = isset($contentElements[0]) ? $contentElements->item(0)->getAttribute('src') : '';
                if($imageUrl !== '' && $imageUrl !== '../#'){                   
                   $contentValue = downloadImage(   $imageUrl);
				   
                }            
            }
            elseif($contentStructure['type'] === 'html') {
                if(count($contentElements) > 0){
                    foreach ($contentElements as $element) {
                        $contentValue .= $doc->saveHTML($element);
                    }

                    $contentValue =  preg_replace_callback(
                        '/href="([^"]+)"/',
                        changeDomain(function($path){return downloadImage($path);}),
                        $contentValue
                    );

                }
            } 
            elseif($contentStructure['type'] === 'child') {
                if($contentStructure['childtype'] === 'html')
                    if(count($contentElements) > 0){
                        $child = $contentElements->item($contentStructure['number']);
                        if(count($contentElements) > 0){
                            foreach ($child as $element) {
                                $contentValue .= $doc->saveHTML($element);
                            }
                            $contentValue = preg_replace_callback(
                                                '/href="([^"]+)"/',
                                                changeDomain(function($path){return downloadImage($path);}),
                                                $contentValue
                                            );
                        }

                    }
            } 
            elseif($contentStructure['type'] === 'link'){
                $contentValue = isset($contentElements[0]) ? $contentElements->item(0)->getAttribute('href') : '';

                $home_url = home_url();
                $home_parsed_url = parse_url($home_url);
                $domain_name = $home_parsed_url['host'];
                if(isset($home_parsed_url['path']))
                    $domain_name .= $home_parsed_url['path'];
                
                $parsedUrl = parse_url($contentValue);
                if (pathinfo($parsedUrl['path'], PATHINFO_EXTENSION) !== 'pdf') {
                    if(isset($parsedUrl['host']) && ($parsedUrl['host'] == 'www.illinoistreasurer.gov' || $parsedUrl['host'] == 'illinoistreasurer.gov')){
                        $contentValue = str_replace($parsedUrl['host'], $domain_name, $contentValue);
                    }
                    else if(!isset($parsedUrl['host'])){
                        $contentValue = $domain_name . $contentValue;
                    }
                }
            }
			elseif($contentStructure['type'] === 'iframe'){
                $contentValue = isset($contentElements[0]) ? $contentElements->item(0)->getAttribute('src') : '';
            }
			elseif($contentStructure['type'] === 'function'){
                $contentValue = $contentStructure['function']($xpath, function($path){return downloadImage($path);}, $FILE_URL_PREFIX, $doc ); 
            }
            elseif($contentStructure['type'] === 'repeater'){                
                $repeaterArray = [];
                
                if(count($contentStructure['fields']) > 0 ){
                    foreach($contentStructure['fields'] as $keyname => $field){
                        $repeaterArray[$keyname] = [];
                        $repeaterSelector =  str_replace(' ', '//', $field['selector']);
                        $repeaterContentElements = $xpath->evaluate($repeaterSelector) ;
                        if($field['type'] === 'html') {
                            if(count($repeaterContentElements) > 0)
                                foreach ($repeaterContentElements as $element) {
                                    $repeaterArray[$keyname][] = $doc->saveHTML($element);
                                    // $repeaterArray[$keyname][] = 'htm';
                                }
                        } 
                        else if($field['type'] === 'image'){                            
                            if(count($repeaterContentElements) > 0)
                                foreach ($repeaterContentElements as $element) {
                                    $imageUrl = $element->getAttribute('src') ;
                                    if($imageUrl !== '' && $imageUrl !== '../#' && $imageUrl !== "../../#"){                   
                                        $repeaterArray[$keyname][]= downloadImage($imageUrl);
                                    }
                                    else{
                                        $repeaterArray[$keyname][] = null;
                                    }
                                }                           
                        }
                        else if($field['type'] === 'alt'){
                            if(count($repeaterContentElements) > 0)
                                foreach ($repeaterContentElements as $element) {
                                     $repeaterArray[$keyname][] = $element->getAttribute('alt');
                                }
                        }
                        elseif($field['type'] === 'link'){
                             if(count($repeaterContentElements) > 0)
                                foreach ($repeaterContentElements as $element) {
                                     $repeaterArray[$keyname][] = $element->getAttribute('href');
                                }
                        }
                    }
                $contentValue = transformArray($repeaterArray);
                }
            }

            $pagesContent[$index]['content_structure'][$key] = $contentValue;
        }
    return [$pagesContent, $pages['ready_for_scrape']];
}

add_action('init', function(){
	if(isset($_REQUEST['SP'])){
		    $newv = scrapePages($_REQUEST['SP']);
    pre($newv);
    echo 'Exit';
            exit();
	}
});


add_action('init', function (){
	if(isset($_GET['scrapperHistory'])){
		

		
    $args = [
        'post_type'      => 'scrapper-history',   
        'posts_per_page' => -1,
		'orderBy' => 'id',
		'order' => 'DESC',
// 		'post__in' => [4659]
		
    ];
    $query = new WP_Query($args);



    if ($query->have_posts()) {
        while ($query->have_posts()) {
		
            $query->the_post();
			
			$url = get_field('page_scrape_from', get_the_ID());
			$pageName = basename(parse_url($url, PHP_URL_PATH));
			
			
			
         	echo '<div>
				Title: '.get_field('page_title', get_the_ID()).'<br/>
				Slug: '.$pageName.'<br/>			
				Page ID: '.get_field('page_id', get_the_ID()).'<br/>
				Page Link: <a href="'.get_permalink(get_field('page_id', get_the_ID())).'">'.get_permalink(get_the_ID()).'</a><br/>
				Refernce: <a href="'.get_field('page_scrape_from', get_the_ID()).'">'.get_field('page_scrape_from', get_the_ID()).'</a><br/>
				
			</div><hr/>';
            wp_reset_postdata();
        }
		
		exit();
    }
	}
});