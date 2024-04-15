<?php
$templates = [
    'Content With Multiple Images and Videos' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
        'sections_pageContent_0' => [
            'selector' => ' div[@id="sections_pageContent_0"]',
            'type' => 'html'
        ],
    'sections_imageLink_0' => [
            'selector' => ' a[@id="sections_imageLink_0"]',
            'type' => 'link'
        ],
        'sections_image_0' => [
            'selector' => ' img[@id="sections_image_0"]',
            'type' => 'image'
        ],
        'sections_pageContent_1' => [
            'selector' => ' div[@id="sections_pageContent_1"]',
            'type' => 'html'
        ],
        'sections_image_1' => [
            'selector' => ' img[@id="sections_image_1"]',
            'type' => 'image'
        ],
        'sections_imageLink_1' => [
            'selector' => ' a[@id="sections_imageLink_1"]',
            'type' => 'link'
        ],
        'sections_pageContent_2' => [
            'selector' => ' div[@id="sections_pageContent_2"]',
            'type' => 'html'
        ],
        'sections_image_2' => [
            'selector' => ' img[@id="sections_image_2"]',
            'type' => 'image'
        ],
        'sections_imageLink_2' => [
            'selector' => ' a[@id="sections_imageLink_2"]',
            'type' => 'link'
        ],
        'sections_pageContent_3' => [
            'selector' => ' div[@id="sections_pageContent_3"]',
            'type' => 'html'
        ],
        'sections_image_3' => [
            'selector' => ' img[@id="sections_image_3"]',
            'type' => 'image'
        ],
        'sections_imageLink_3' => [
            'selector' => ' a[@id="sections_imageLink_3"]',
            'type' => 'link'
        ],
        'sections_pageContent_4' => [
            'selector' => ' div[@id="sections_pageContent_4"]',
            'type' => 'html'
        ],
        'sections_image_4' => [
            'selector' => ' img[@id="sections_image_4"]',
            'type' => 'image'
        ],
        'sections_imageLink_4' => [
            'selector' => ' a[@id="sections_imageLink_4"]',
            'type' => 'link'
        ],
        'sections_pageContent_5' => [
            'selector' => ' div[@id="sections_pageContent_5"]',
            'type' => 'html'
        ],
        'sections_image_5' => [
            'selector' => ' img[@id="sections_image_5"]',
            'type' => 'image'
        ],
        'sections_imageLink_5' => [
            'selector' => ' a[@id="sections_imageLink_5"]',
            'type' => 'link'
        ],
        
    ]
];


$pages = [
    [
        'wp_pageid' => 43, 
        'scrap_from' => 'https://www.illinoistreasurer.gov/Office_of_the_Treasurer/19th_Amendment_Commemorative_Coin',
        'content_structure' => $templates['Content With Multiple Images and Videos']
    ]
];