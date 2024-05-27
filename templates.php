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




$templates = [
'College_Savings-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
        'content_without_image' => [
            'selector' => ' div[@id="pageContent"]',
            'type' => 'html'
        ],
    ],     
];

$pages = [
    [
        'wp_pageid' => 43, 
        'scrap_from' => "https://illinoistreasurer.gov/Office_of_the_Treasurer/Contact_the_Treasurer's_Office",
        'content_structure' => $templates['College_Savings-template.php']
    ]
];
