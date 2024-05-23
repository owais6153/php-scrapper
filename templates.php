<?php

// College Savings
// content with multiple images ePAY
// The Illinois Funds
// historical daily rates
// archived documents
// content with images documents
// content-with-banner-image-templat
// daily rates
// content with table RIP
// media gallery
// directories




// Done====
// content-without-image-template
// content-with-multiple-image-template.php
// categorized_documents
// Contact Us
// content-with-banner-image
// content-with-image-template
// styled_table
// The Illinois Funds
// content_accordion
// daily_rates
// content_with_multiple_images_invest

// Remaing====
$ignore = [
    'content_with_table_RIP',
'content_with_multiple_images_ePAY',
'content-with-static-image-template',
'heritage-month-template',

// Problems
'archived_documents',
'content_with_images_documents',
'College_Savings',
'historical_daily_rates',
'media_gallery',
'heritage-month-template',

];
function  home_url () {
    return 'https://example.com';
}



function  changeDomain ($downloadImage) {
   return function ($matches) use($downloadImage){ 
    $home_url = home_url();
    $home_parsed_url = parse_url($home_url);
    $domain_name = $home_parsed_url['host'];

    $url = $matches[1];
        $parsedUrl = parse_url($url);
        if (pathinfo($parsedUrl['path'], PATHINFO_EXTENSION) !== 'pdf') {
            if(isset($parsedUrl['host']) && ($parsedUrl['host'] == 'www.illinoistreasurer.gov' || $parsedUrl['host'] == 'illinoistreasurer.gov')){
                $url = str_replace($parsedUrl['host'], $domain_name, $url);
            }
        }
        else{
            // $attachment_id = $downloadImage($url);
            // $attachment_url = wp_get_attachment_url($attachment_id);
            // if ($attachment_url) 
            //    $url = $attachment_url;            
        }
        return 'href="' . $url . '"';
    };
}

$templates = [
 'categorized_documents-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
       'content_with_multiple_images_invest' => [
            'selector' => ' div[@class="content_left"]',
            'type' => 'function',
            'function' => function ($xpath, $downloadImage, $FILE_URL_PREFIX, $doc ) {
                $content = [];
                $lastIndex = 0;
                $contentHTML = $xpath->query('//div[@class="content_left"]//div[@class="col-md-12"]')->item(0);
                $childNodes = $contentHTML->childNodes;
                foreach ($childNodes as $childNode) {                  
                    if ($childNode->nodeType === XML_ELEMENT_NODE) {
                        $idAttr = $childNode->getAttribute('id');
                        if(strpos($idAttr, 'sectionContent') !== false){
                            if(isset($content[$lastIndex]['main_content']))
                                $lastIndex = $lastIndex + 1;
                            $content[$lastIndex]['main_content'] = $doc->saveHTML($childNode);
                            $content[$lastIndex]['main_content'] = preg_replace_callback(
                                '/href="([^"]+)"/',
                                changeDomain($downloadImage),
                                $content[$lastIndex]['main_content']
                            );
                        }
                        if(strpos($idAttr, 'inlineImage') !== false){
                            $content[$lastIndex]['image_with_content'] = $childNode->getAttribute('src'); 
                            $image = $downloadImage( $content[$lastIndex]['image_with_content'] );
								// $image = ( $content[$lastIndex]['image_with_content'] );
                                $content[$lastIndex]['image_with_content'] = $image;
                        }
                        if(strpos($idAttr, 'imageLink') !== false){
                            $content[$lastIndex]['content_with_multiple_images_invest_image_link'] = $childNode->getAttribute('href');                             

                            $home_url = home_url();
                            $home_parsed_url = parse_url($home_url);
                            $domain_name = $home_parsed_url['host'];
                            if(isset($home_parsed_url['path']))
                                $domain_name .= $home_parsed_url['path'];
                            
                            $parsedUrl = parse_url($content[$lastIndex]['content_with_multiple_images_invest_image_link']);
                            if (pathinfo($parsedUrl['path'], PATHINFO_EXTENSION) !== 'pdf') {
                                if(isset($parsedUrl['host']) && ($parsedUrl['host'] == 'www.illinoistreasurer.gov' || $parsedUrl['host'] == 'illinoistreasurer.gov')){
                                    $content[$lastIndex]['content_with_multiple_images_invest_image_link'] = str_replace($parsedUrl['host'], $domain_name, $content[$lastIndex]['content_with_multiple_images_invest_image_link']);
                                }
                                else if(!isset($parsedUrl['host'])){
                                    $content[$lastIndex]['content_with_multiple_images_invest_image_link'] = $domain_name . $content[$lastIndex]['content_with_multiple_images_invest_image_link'];
                                }
                            }

                            
                            $image = $childNode->getElementsByTagName('img');
                            if(isset($image[0]) ){
                                $content[$lastIndex]['content_with_multiple_images_invest_image_caption'] = $image->item(0)->getAttribute('alt');
                                $content[$lastIndex]['content_with_multiple_images_invest_image'] = $image->item(0)->getAttribute('src');
								$image = '';
								
								if($content[$lastIndex]['content_with_multiple_images_invest_image'] !== '' && $content[$lastIndex]['content_with_multiple_images_invest_image'] !== '../#' && $content[$lastIndex]['content_with_multiple_images_invest_image'] !== '../../#' ){
									// $image = $downloadImage( $content[$lastIndex]['content_with_multiple_images_invest_image'] );
									$image = ( $content[$lastIndex]['content_with_multiple_images_invest_image'] );
						
									
									$content[$lastIndex]['content_with_multiple_images_invest_image'] = $image;
									
								}							
								else{
									$content[$lastIndex]['content_with_multiple_images_invest_image'] = '';
									$content[$lastIndex]['content_with_multiple_images_invest_image_caption'] = '';
								}	
                            }
							else{
								$content[$lastIndex]['content_with_multiple_images_invest_image'] = '';
								$content[$lastIndex]['content_with_multiple_images_invest_image_caption'] = '';
							}
                        }

                    }
                }

                return $content;
            }
       ]
    ],       
];

$pages = [
    [
        'wp_pageid' => 43, 
        'scrap_from' => 'https://www.illinoistreasurer.gov/Individuals/College_Savings',
        'content_structure' => $templates['categorized_documents-template.php']
    ]
];
