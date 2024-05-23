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
    'College_Savings-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
        'college_saving_banner_image' => [
            'selector' => ' img[@class="csBannerImg"]',
            'type' => 'image'
        ],
        'college_saving_banner_image_link' => [
            'selector' => ' a[@class="csBannerImg"]',
            'type' => 'link'
        ],
        'college_saving_banner_image_caption' => [
            'selector' => ' div[@class="content_left"]',
            'type' => 'function',
            'function' => function ($xpath, $downloadImage, $FILE_URL_PREFIX, $doc ) {
                $content = '';
                $contentHTML = $xpath->query('//div[@class="content_left"]')->item(0);

                $childNodes = $contentHTML->childNodes;
                foreach ($childNodes as $childNode) {                  
                    if ($childNode->nodeType === XML_ELEMENT_NODE) {
                      $className = $childNode->getAttribute('class');
                      if(strpos($className, 'quoteContainer') !== false)
                          {
                             $content = $doc->saveHTML($childNode);
                            $content= preg_replace_callback(
                                '/href="([^"]+)"/',
                                changeDomain($downloadImage),
                                $content
                            );
                          }
                    }
                }

                return $content;
            }
        ],
        'college_saving_banner_image_content_1' => [
            'selector' => ' div[@class="quoteContainer"]',
            'type' => 'html'
        ],
        'illinois_529_programs_image_1' => [
            'selector' => ' img[@class="csProjectionGraph"]',
            'type' => 'image'
        ],
        'illinois_529_programs_heading_1' => [
            'selector' => ' div[@class="textNextToLogo"]',
            'type' => 'html'
        ],
        'illinois_529_programs_paragraph_1' => [
            'selector' => ' div[@class="textNextToLogo"]',
            'type' => 'html'
        ],
        'illinois_529_programs_image_2' => [
            'selector' => ' img[@class="csProjectionGraph"]',
            'type' => 'image'
        ],
        'illinois_529_programs_heading_2' => [
            'selector' => ' div[@class="textNextToLogo"]',
            'type' => 'html'
        ],
        'illinois_529_programs_paragraph_2' => [
            'selector' => ' div[@class="textNextToLogo"]',
            'type' => 'html'
        ],
        'illinois_529_programs_image_3' => [
            'selector' => ' img[@class="csProjectionGraph"]',
            'type' => 'image'
        ],
        'illinois_529_programs_heading_3' => [
            'selector' => ' div[@class="textNextToLogo"]',
            'type' => 'html'
        ],
        'illinois_529_programs_paragraph_3' => [
            'selector' => ' div[@class="textNextToLogo"]',
            'type' => 'html'
        ],
        'illinois_549_programs_content_2' => [
            'selector' => ' div[@class="textNextToLogo"]',
            'type' => 'html'
        ],
        'illinois_549_programs_content_2' => [
            'selector' => ' div[@class="content_left"]',
            'type' => 'child',
            'number' => 4,
            'childtype' => 'html'
        ],
        'college_savings_image_2' => [
            'selector' => ' img[@class="textAndImage"] img',
            'type' => 'image'
        ],
        'college_savings_image_2_caption' => [
            'selector' => ' div[@class="twoImageTextHeader"]',
            'type' => 'html'
        ],
        'college_savings_image_2_caption' => [
            'selector' => ' div[@class="twoImageTextBody"]',
            'type' => 'html'
        ],
        'college_savings_image_3' => [
            'selector' => ' img[@class="smallImage"]',
            'type' => 'image'
        ],
        'college_savings_image_3_caption' => [
            'selector' => ' div[@class="twoImageTextHeader"]',
            'type' => 'html'
        ],
        'college_savings_image_3_caption' => [
            'selector' => ' div[@class="twoImageTextBody"]',
            'type' => 'html'
        ],
        
    ],      
];

$pages = [
    [
        'wp_pageid' => 43, 
        'scrap_from' => 'https://www.illinoistreasurer.gov/Individuals/College_Savings',
        'content_structure' => $templates['College_Savings-template.php']
    ]
];
