<?php



function pre($html){
    echo '<pre>';
    if(gettype($html) === 'string')
    echo $html;
else
    print_r($html);
    echo '</pre>';
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
                if(strpos($url, 'mailto:') === false && strpos($url, 'tel:') === false && strpos($url, 'javascript') === false)
                   $url = str_replace(["'", "(", ")"], "",  $domain_name . $url) ;
			}

            if(strpos($url, 'https://') === false && strpos($url, 'http://') === false){
                if(strpos($url, 'mailto:') === false && strpos($url, 'tel:') === false && strpos($url, 'javascript') === false)
                    $url = 'https://' . $url;
            }
        }
        else{
            // $attachment_id = $downloadImage($url);
            // $attachment_url = wp_get_attachment_url($attachment_id);
            // if ($attachment_url) 
            //     $url = $attachment_url;            
        }
        return 'href="' . $url . '"';
    };
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

function downloadImage($imageUrl, $savePath){
    try {
        $imageData = file_get_contents($imageUrl);
        if($imageData === false){
            throw new Exception('Failed to download image from URL: ' . $imageUrl);
        }
        file_put_contents($savePath, $imageData);
    } catch (Exception $e) {
        // Handle the exception, you can log it or display an error message
        echo 'Error downloading image';
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




require_once (__DIR__ . '/templates.php');


$pagesContent = [];
$UPLOAD_TO = __DIR__ . '/scrapped-assets/';
$FILE_URL_PREFIX = 'https://www.illinoistreasurer.gov';

foreach($pages as $index => $page){
    $htmlString = fetchPage($page['scrap_from']);
    if(!$htmlString) return;
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($htmlString);
    $xpath = new DOMXPath($doc);

    $pagesContent[$index] = $page;
    foreach($page['content_structure'] as $key => $contentStructure){
        $selector =  str_replace(' ', '//', $contentStructure['selector']);
        $contentElements = $xpath->evaluate($selector) ;
        $contentValue ='';
        if($contentStructure['type'] === 'text')
            $contentValue = isset($contentElements[0]) ? $contentElements->item(0)->textContent.PHP_EOL  :   "No {$key} found";
        else if($contentStructure['type'] === 'image'){
            $imageUrl = isset($contentElements[0]) ? $contentElements->item(0)->getAttribute('src') : '';
            if($imageUrl !== '' && $imageUrl !== '../#'){
                $filename = basename($imageUrl);
                $savePath = $UPLOAD_TO . $filename; 
                downloadImage( $FILE_URL_PREFIX .  $imageUrl, $savePath);
                $contentValue = $savePath;
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
        elseif($contentStructure['type'] === 'link'){
             $contentValue = isset($contentElements[0]) ? $contentElements->item(0)->getAttribute('href') : '';
        }
        elseif($contentStructure['type'] === 'repeater'){                
                $repeaterArray = [];
                
                if(count($contentStructure['fields']) > 0 && count($contentElements) > 0){
                    foreach($contentStructure['fields'] as $keyname => $field){
                        $repeaterArray[$keyname] = [];
                        $repeaterSelector =  str_replace(' ', '//', $field['selector']);
                        $repeaterContentElements = $xpath->evaluate($repeaterSelector) ;
                        if($field['type'] === 'html') {
                            if(count($repeaterContentElements) > 0)
                                foreach ($repeaterContentElements as $element) {
                                    // $repeaterArray[$keyname][] = $doc->saveHTML($element);
                                    $repeaterArray[$keyname][] = 'htm';
                                }
                        } 
                        else if($field['type'] === 'image'){                            
                            if(count($repeaterContentElements) > 0)
                                foreach ($repeaterContentElements as $element) {
                                    $imageUrl = $element->getAttribute('src') ;
                                    if($imageUrl !== '' && $imageUrl !== '../#' && $imageUrl !== "../../#"){                   
                                        // $repeaterArray[$keyname][]= downloadImage( $FILE_URL_PREFIX .  $imageUrl);
                                        $repeaterArray[$keyname][]= $FILE_URL_PREFIX .  $imageUrl;
                                        // if(!$contentValue){				   
                                        //     $repeaterArray[$keyname][]= downloadImage(str_replace(' ', '%20', strtolower('https://illinoistreasurergovprod.blob.core.usgovcloudapi.net' .  $imageUrl)));
                                        // }
                                    }
                                    else{
                                        $repeaterArray[$keyname][] = null;
                                    }
                                }                           
                        }
                        else if($field['type'] === 'alt'){
                            $imageAlt = isset($repeaterContentElements[0]) ? $repeaterContentElements->item(0)->getAttribute('alt') : '';                          
                            $repeaterArray[$keyname][]= $imageAlt;
                        }
                        elseif($field['type'] === 'link'){
                            $repeaterArray[$keyname][] = isset($repeaterContentElements[0]) ? $repeaterContentElements->item(0)->getAttribute('href') : '';
                        } 
                        elseif($field['type'] === 'text'){
                            if(count($repeaterContentElements) > 0)
                                foreach ($repeaterContentElements as $element) {
                                    // $repeaterArray[$keyname][] = $doc->saveHTML($element);
                                    $repeaterArray[$keyname][] = $element->textContent.PHP_EOL ;
                                }
                        }
                        $contentValue = transformArray($repeaterArray);
                        $index = $index + 1;
                    }
                }
            }
            elseif($contentStructure['type'] === 'function'){
                $contentValue = $contentStructure['function']($xpath, function($path){downloadImage($path);}, $FILE_URL_PREFIX, $doc); 
            }
        $pagesContent[$index]['content_structure'][$key] = $contentValue;
    }
}


pre($pagesContent, true);