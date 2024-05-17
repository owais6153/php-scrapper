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
$templates = [
 'categorized_documents-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
       'image_with_content' => [
            'selector' => ' img[@id="sections_inlineImage_0"]',
            'type' => 'image',
       ],
       'main_content' => [
            'selector' => ' div[@id="sections_sectionContent_0"]',
            'type' => 'html',
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
                        if(strpos($idAttr, 'sectionContent') !== false && $idAttr !== 'sections_sectionContent_0'){
                            if(isset($content[$lastIndex]['content_with_multiple_images_invest_content']))
                                $lastIndex = $lastIndex + 1;
                            $content[$lastIndex]['content_with_multiple_images_invest_content'] = $doc->saveHTML($childNode);
                        }
                        if(strpos($idAttr, 'imageLink') !== false){
                            $content[$lastIndex]['content_with_multiple_images_invest_image_link'] = $childNode->getAttribute('href');                             
                            $image = $childNode->getElementsByTagName('img');
                            if(isset($image[0]) ){
                                $content[$lastIndex]['content_with_multiple_images_invest_image_caption'] = $image->item(0)->getAttribute('alt');
                                $content[$lastIndex]['content_with_multiple_images_invest_image'] = $image->item(0)->getAttribute('src');
								$image = '';
								
								if($content[$lastIndex]['content_with_multiple_images_invest_image'] !== '' && $content[$lastIndex]['content_with_multiple_images_invest_image'] !== '../#' && $content[$lastIndex]['content_with_multiple_images_invest_image'] !== '../../#' ){
									$image = ( $FILE_URL_PREFIX . $content[$lastIndex]['content_with_multiple_images_invest_image'] );
									if(!$image || $image == null || $image == ''){				   
										$image = (str_replace(' ', '%20', strtolower('https://illinoistreasurergovprod.blob.core.usgovcloudapi.net' . $content[$lastIndex]['content_with_multiple_images_invest_image'] )));
									}
									
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
        'scrap_from' => 'https://illinoistreasurer.gov/Invest_in_Illinois/Ag_Invest#',
        'content_structure' => $templates['categorized_documents-template.php']
    ]
];
