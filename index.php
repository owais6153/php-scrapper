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
    $pageCurl = curl_init($link);
    curl_setopt($pageCurl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($pageCurl, CURLOPT_BINARYTRANSFER, true);
    $pageContent = curl_exec($pageCurl);
    curl_close($pageCurl);
    return $pageContent;
}




require_once (__DIR__ . '/templates.php');
$pages = [
    [
        'wp_pageid' => 43, 
        'scrap_from' => 'https://www.illinoistreasurer.gov/Office_of_the_Treasurer/19th_Amendment_Commemorative_Coin',
        'content_structure' => $templates['Content With Multiple Images and Videos']
    ]
];

$pagesContent = [];
$UPLOAD_TO = __DIR__ . '/scrapped-assets/';
$FILE_URL_PREFIX = 'https://www.illinoistreasurer.gov';

foreach($pages as $index => $page){
    $htmlString = fetchPage($page['scrap_from']);
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
                $imageData = file_get_contents($FILE_URL_PREFIX . $imageUrl);
                $filename = basename($imageUrl);
                $savePath = $UPLOAD_TO . $filename; 
                file_put_contents($savePath, $imageData);
                $contentValue = $savePath;
            }            
        }
        elseif($contentStructure['type'] === 'html') {
            if(count($contentElements) > 0)
                foreach ($contentElements as $element) {
                    $contentValue .= $doc->saveHTML($element);
                }
        } 
        elseif($contentStructure['type'] === 'link'){
             $contentValue = isset($contentElements[0]) ? $contentElements->item(0)->getAttribute('href') : '';
        }
        $pagesContent[$index]['content_structure'][$key] = $contentValue;
    }
}


pre($pagesContent, true);