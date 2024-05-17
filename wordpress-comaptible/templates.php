<?php
$UPLOAD_TO = __DIR__ . '/scrapped-assets/';
$FILE_URL_PREFIX = 'https://www.illinoistreasurer.gov';
$FILE_URL_ALTERNATE_PREFIX = 'https://illinoistreasurergovprod.blob.core.usgovcloudapi.net';
$templates = [
    'Content with_multiple_images_and_videos-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
        'content_with_banner_image' => [
            'selector' => ' img[@id="sections_image_0"]',
            'type' => 'image'
        ],
        'content_with_banner_image_content_1' => [
            'selector' => ' div[@id="sections_pageContent_1"]',
            'type' => 'html'
        ],
        'content_with_banner_image_2' => [
            'selector' => ' img[@id="sections_image_2"]',
            'type' => 'image'
        ],
        'content_with_banner_image_content_2' => [
            'selector' => ' div[@id="sections_pageContent_3"]',
            'type' => 'html'
        ],
        'content_with_banner_image_3' => [
            'selector' => ' img[@id="sections_image_4"]',
            'type' => 'image'
        ],
        'content_with_banner_image_3_link' => [
            'selector' => ' a[@id="sections_imageLink_4"]',
            'type' => 'link'
        ],
        'content_with_banner_image_content_3' => [
            'selector' => ' div[@id="sections_pageContent_5"]',
            'type' => 'html'
        ],
        
    ],
    'content_accordion-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
        'content_accordion' => [
            'selector' => ' div[@class="content_left"]',
            'type' => 'html'
        ],
		       'content_accordions' => [
            'selector' => 'div[contains(@class, "accrodation")',
            'type' => 'function',
            'function' => function ($xpath, $downloadImage, $FILE_URL_PREFIX, $doc ) {
                $accordions = [];
                $lastAccordianIndex = 0;
                $accordion = $xpath->query('//div[contains(@class, "accrodation")]')->item(0);
                $childNodes = $accordion->childNodes;
                foreach ($childNodes as $childNode) {                  
                    if ($childNode->nodeType === XML_ELEMENT_NODE) {                        
                        if($childNode->nodeName === 'span'){
                            if(isset($accordions[$lastAccordianIndex]['accordion_heading']))
                                $lastAccordianIndex = $lastAccordianIndex +1;
    
                            $accordions[$lastAccordianIndex]['accordion_heading'] = $childNode->nodeValue;
                        }
                        else if($childNode->nodeName === 'div'){
                            if(isset($accordions[$lastAccordianIndex]['accordion_content']))
                                $lastAccordianIndex = $lastAccordianIndex +1;
                                                        
                                $accordions[$lastAccordianIndex]['accordion_content'] = $doc->saveHTML($childNode);
                        }
                    }
                }
                return $accordions;
            },       
        ]  
        
    ],
    'contact_form-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
        'contact_form_content' => [
            'selector' => ' div[@id="pageContent"]',
            'type' => 'html'
        ],
    ],
    'styled_table-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
        'styled_table' => [
            'selector' => ' div[@class="content_left"]',
            'type' => 'html'
        ],
        
    ],
    'content-with-multiple-image-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
        'content_with_multiple_images_repeater' => [
            'selector' => ' div[@class="content_left"]',
            'type' => 'function',
            'function' => function ($xpath, $downloadImage, $FILE_URL_PREFIX, $doc ) {
                $content = [];
                $lastIndex = 0;
                $contentHTML = $xpath->query('//div[@class="content_left"]')->item(0);
                $childNodes = $contentHTML->childNodes;
                foreach ($childNodes as $childNode) {                  
                    if ($childNode->nodeType === XML_ELEMENT_NODE) {
                        $idAttr = $childNode->getAttribute('id');
                        if(strpos($idAttr, 'pageContent') !== false){
                            if(isset($content[$lastIndex]['content_with_multiple_images_content']))
                                $lastIndex = $lastIndex + 1;
                            $content[$lastIndex]['content_with_multiple_images_content'] = $doc->saveHTML($childNode);
                        }
                        if(strpos($idAttr, 'imageLink') !== false){
                            $content[$lastIndex]['content_with_multiple_images_image_link'] = $childNode->getAttribute('href');                             
                            $image = $childNode->getElementsByTagName('img');
                            if(isset($image[0]) ){
                                $content[$lastIndex]['content_with_multiple_images_image_alt'] = $image->item(0)->getAttribute('alt');
                                $content[$lastIndex]['content_with_multiple_images_image'] = $image->item(0)->getAttribute('src');
								$image = '';
								
								if($content[$lastIndex]['content_with_multiple_images_image'] !== '' && $content[$lastIndex]['content_with_multiple_images_image'] !== '../#' && $content[$lastIndex]['content_with_multiple_images_image'] !== '../../#' ){
									$image = $downloadImage( $FILE_URL_PREFIX . $content[$lastIndex]['content_with_multiple_images_image'] );
									if(!$image || $image == null || $image == ''){				   
										$image = $downloadImage(str_replace(' ', '%20', strtolower('https://illinoistreasurergovprod.blob.core.usgovcloudapi.net' . $content[$lastIndex]['content_with_multiple_images_image'] )));
									}
									
									$content[$lastIndex]['content_with_multiple_images_image'] = $image;
									
								}							
								else{
									$content[$lastIndex]['content_with_multiple_images_image'] = '';
									$content[$lastIndex]['content_with_multiple_images_image_alt'] = '';
								}	
								
                            }
							else{
								$content[$lastIndex]['content_with_multiple_images_image'] = '';
								$content[$lastIndex]['content_with_multiple_images_image_alt'] = '';
							}
                        }

                    }
                }

                return $content;
            }
        ]
    ],
    'heritage-month-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
        'content_heritage' => [
            'selector' => ' div[@id="content_left"]',
            'type' => 'html'
        ],
        
    ],
     'categorized_documents-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
        'categorized_documents_content' => [
            'selector' => ' div[@class="content_left"]',
            'type' => 'html'
        ],      
        'categorized_documents_repeater_main' => [
            'selector' => ' div[contains(@class, "accrodation")',
            'type' => 'function',
            'function' => function ($xpath, $downloadImage, $FILE_URL_PREFIX ) {
                $accordions = [];
                $lastAccordianGroupIndex = 0;
                $lastAccordianIndex = 0;
                $accordion = $xpath->query('//div[contains(@class, "accrodation")]')->item(0);
           
                $childNodes = $accordion->childNodes;
                foreach ($childNodes as $childNode) {                  
                    if ($childNode->nodeType === XML_ELEMENT_NODE) {                        
                        if($childNode->nodeName === 'h5'){
                            if(isset($accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_section_name']))
                                $lastAccordianGroupIndex = $lastAccordianGroupIndex + 1;

                            $accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_section_name'] = $childNode->nodeValue;
                            $accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_items'] = [];
                        }                        
                        else if($childNode->nodeName === 'span'){
                            if(isset($accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_items'][$lastAccordianIndex]['categorized_documents_item_title']))
                                $lastAccordianIndex = $lastAccordianIndex +1;
    
                            $accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_items'][$lastAccordianIndex]['categorized_documents_item_title'] = $childNode->nodeValue;
                        }
                        else if($childNode->nodeName === 'div'){
                            if(isset($accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_items'][$lastAccordianIndex]['all_links']))
                                $lastAccordianIndex = $lastAccordianIndex +1;
                            
                            $linkTags = $childNode->getElementsByTagName('a');      

                            foreach($linkTags as $li => $linkTag){                                
                                $accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_items'][$lastAccordianIndex]['all_links'][$li]['categorized_documents_link_name'] = $linkTag->textContent;
                                $accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_items'][$lastAccordianIndex]['all_links'][$li]['categorized_documents_url'] = $linkTag->getAttribute('href');
                                if(strpos( $accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_items'][$lastAccordianIndex]['all_links'][$li]['categorized_documents_url'], '.pdf') !== false){
									if (strpos($accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_items'][$lastAccordianIndex]['all_links'][$li]['categorized_documents_url'], 'illinoistreasurergovprod.blob.core.usgovcloudapi.net') !== false ||
                                        strpos($accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_items'][$lastAccordianIndex]['all_links'][$li]['categorized_documents_url'], 'illinoistreasurer.gov') !== false
                                    ){
										$accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_items'][$lastAccordianIndex]['all_links'][$li]['upload_document'] = $downloadImage($accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_items'][$lastAccordianIndex]['all_links'][$li]['categorized_documents_url']);
                                    	$accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_items'][$lastAccordianIndex]['all_links'][$li]['categorized_documents_url'] = null;
                                    }
                                    else {
                                        $accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_items'][$lastAccordianIndex]['all_links'][$li]['upload_document'] = $downloadImage(str_replace(' ', '%20', strtolower('https://illinoistreasurergovprod.blob.core.usgovcloudapi.net' . $accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_items'][$lastAccordianIndex]['all_links'][$li]['categorized_documents_url']) ));
                                    	$accordions[$lastAccordianGroupIndex]['categorized_documents_repeater_items'][$lastAccordianIndex]['all_links'][$li]['categorized_documents_url'] = null;
                                    }
                                }
                            }
                        }
                    }
                }
                return $accordions;
            },       
        ]              
    ],
    'content-without-image-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
        'content_without_image' => [
            'selector' => ' div[@id="pageContent"]',
            'type' => 'html'
        ],
    ],
    'content-with-static-image-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
        'content_1_with_static_image' => [
            'selector' => ' div[@id="pageContent1"]',
            'type' => 'html'
        ],
        'content_2_with_static_image' => [
            'selector' => ' div[@id="pageContent2"]',
            'type' => 'html'
        ],
        'content_3_with_static_image' => [
            'selector' => ' div[@id="pageContent3"]',
            'type' => 'html'
        ],
        'sustainable_content_1' => [
            'selector' => ' div[@class="corporateGovernanceSection"]',
            'type' => 'html'
        ],
        'sustainable_content_2' => [
            'selector' => ' div[@class="enviormentalSection"]',
            'type' => 'html'
        ],
        'sustainable_content_3' => [
            'selector' => ' div[@class="socialCapitalSection"]',
            'type' => 'html'
        ],
        'sustainable_content_4' => [
            'selector' => ' div[@class="humanCapitalSection"]',
            'type' => 'html'
        ],
        'sustainable_content_5' => [
            'selector' => ' div[@class="businessModelSection"]',
            'type' => 'html'
        ],
        'sustainable_content_6' => [
            'selector' => ' div[@class="leadershipSection"]',
            'type' => 'html'
        ],
        'actions_taken_content_1' => [
            'selector' => ' div[@id="openingParagraph"]',
            'type' => 'html'
        ],
        'actions_taken_inner_heading_1' => [
            'selector' => ' div[@id="sections1_title_0"]',
            'type' => 'html'
        ],
        'actions_taken_inner_paragraph_1' => [
            'selector' => ' div[@id="sections1_pageContent_0"]',
            'type' => 'html'
        ],
        'actions_taken_inner_heading_2' => [
            'selector' => ' div[@id="sections2_title_0"]',
            'type' => 'html'
        ],
        'actions_taken_inner_paragraph_2' => [
            'selector' => ' div[@id="sections2_pageContent_0"]',
            'type' => 'html'
        ],
        'actions_taken_inner_heading_3' => [
            'selector' => ' div[@id="sections2_title_1"]',
            'type' => 'html'
        ],
        'actions_taken_inner_paragraph_3' => [
            'selector' => ' div[@id="sections2_pageContent_1"]',
            'type' => 'html'
        ],
        'sustainability_research_content_1' => [
            'selector' => ' div[@id="openingParagraph"]',
            'type' => 'html'
        ],
        
    ],
    'archived_documents-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
        'archived_documents_content' => [
            'selector' => ' div[@class="content_left"]',
            'type' => 'html'
        ],
        
    ],
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
            'selector' => ' div[@class="quoteContainer"]',
            'type' => 'html'
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
    'calender-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
        'calender_content' => [
            'selector' => ' div[@id="openingParagraph"]',
            'type' => 'html'
        ],
        
    ],
    'content-with-image-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
        'content_with_image' => [
            'selector' => ' img[@id="leftImage"]',
            'type' => 'image'
        ],
        'content_with_image_text' => [
            'selector' => ' div[@id="pageContent"]',
            'type' => 'html'
        ],
        
    ],  
    'media_gallery-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
    ],  
    'directories-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
		'sections_for_directories' => [
            'selector' => ' div[@class="content_left"] div[contains(@id,"directories_pageContent")]',
            'type' => 'function',
            'function' => function ($xpath, $downloadImage, $FILE_URL_PREFIX, $doc ) {
                $content = [];
                $contentHTML = $xpath->query('//div[@class="content_left"]//div[contains(@id,"directories_pageContent")]');
       
                foreach ($contentHTML as $childNode) {                  
                    if ($childNode->nodeType === XML_ELEMENT_NODE) {
                        $content[] = ['directories_text' => $doc->saveHTML($childNode)];
                    }
                }

                return $content;
            }
        ]
    ],   
        'content_with_multiple_images_invest-template.php' => [
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
									$image = $downloadImage( $FILE_URL_PREFIX . $content[$lastIndex]['content_with_multiple_images_invest_image'] );
									if(!$image || $image == null || $image == ''){				   
										$image = $downloadImage(str_replace(' ', '%20', strtolower('https://illinoistreasurergovprod.blob.core.usgovcloudapi.net' . $content[$lastIndex]['content_with_multiple_images_invest_image'] )));
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
    'content_with_images_documents-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
    ],    
    'content_with_multiple_images_ePAY-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],	
    ],     
    'The_Illinois_Funds-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
		'tabs_menu' => [
			'selector' => ' div[@class="button-group"]',
			'type' => 'function',
			'function' => function ($xpath, $downloadImage, $FILE_URL_PREFIX, $doc ) {
                $tabs = [];
                $tabsHTML = $xpath->query('//div[@class="button-group"]')->item(0);
                $childNodes = $tabsHTML->childNodes;

                foreach ($childNodes as $childNode) {                  
                    if ($childNode->nodeType === XML_ELEMENT_NODE) {
                         $menuItem = [];                        
                        if($childNode->nodeName === 'div'){
                            $dropdown = $childNode->getElementsByTagName('div');
                            if(isset($dropdown[0])){    
                                $dropdownLinks = $dropdown->item(0)->getElementsByTagName('a');
                                if(isset($dropdownLinks[0])){                                  
                                    $menuItem['menus_sub_menu'] = [];
                                    
                                    foreach($dropdownLinks as $ch){
           
                                        $dropdownItems = [
                                            'sub_menu_name' => $ch->nodeValue,
                                            'sub_menu_url' => $ch->getAttribute('href'),                                    
                                        ];
                                        $menuItem['menus_sub_menu'][] = $dropdownItems;                                
                                    }                                
                                }                        
                                foreach($childNode->childNodes as $l) {
                                    if($l->nodeName === 'a')
                                    $menuItem['menu_name'] = $l->nodeValue;  
                                }                                             
                            }
                        }
                        else if($childNode->nodeName === 'a'){
                            $menuItem = [
                                'menu_name' => $childNode->nodeValue,
                                'menu_url' => $childNode->getAttribute('href'),
                            ];
                        }
                        $tabs[] = $menuItem;
                    }
                }
                return $tabs;
            },      
		]	,
		'content_sections' => [
            'selector' => ' div[@class="content_left"] div[@class="col-md-12"]',
            'type' => 'function',
            'function' => function ($xpath, $downloadImage, $FILE_URL_PREFIX, $doc ) {
                $content = [];
                $contentHTML = $xpath->query('//div[@class="content_left"]//div[@class="col-md-12"]')->item(0);
                $childNodes = $contentHTML->childNodes;
                foreach ($childNodes as $childNode) {                  
                    if ($childNode->nodeType === XML_ELEMENT_NODE) {
                        $c = []       ;   
                        if($childNode->nodeName === 'h2'){
                            $c['content_sections_content_heading'] = $childNode->nodeValue;
                        }
                        if($childNode->nodeName === 'div'){
                            $idAttr = $childNode->getAttribute('id');

                            if(strpos($idAttr, 'sectionContent') !== false)
                                $c['content_sections_content'] = $doc->saveHTML($childNode);
                            elseif(strpos($idAttr, 'twoImageSection') !== false){
                                $images = $childNode->getElementsByTagName('img');
                                foreach($images as $image){
                                    if($image->getAttribute('class') === 'ms-staticImg--desk'){
                                        if(!isset($c['content_sections_image_1'])){
                                            $c['content_sections_image_1'] = $image->getAttribute('src');	
											$c['content_sections_image_1'] = $downloadImage( $FILE_URL_PREFIX . $c['content_sections_image_1']);
											if(!$c['content_sections_image_1']){				   
												$c['content_sections_image_1'] = $downloadImage(str_replace(' ', '%20', strtolower('https://illinoistreasurergovprod.blob.core.usgovcloudapi.net' . $c['content_sections_image_1'])));
											}	
											
											
                                            $c['content_sections_image_1_caption'] = $image->getAttribute('alt');
                                        }
                                        else{
											$c['content_sections_image_2'] = $image->getAttribute('src');	
											$c['content_sections_image_2'] = $downloadImage( $FILE_URL_PREFIX . $c['content_sections_image_2']);
											if(!$c['content_sections_image_2']){				   
												$c['content_sections_image_2'] = $downloadImage(str_replace(' ', '%20', strtolower('https://illinoistreasurergovprod.blob.core.usgovcloudapi.net' . $c['content_sections_image_2'])));
											}
                                            $c['content_sections_image_2_caption'] = $image->getAttribute('alt');
                                        }
                                    }
                                }

                                
                                $imgLinks = $childNode->getElementsByTagName('a');
                                foreach($imgLinks as $l){
                                    if(!isset($c['content_sections_image_1_link']))
                                        $c['content_sections_image_1_link'] = $l->getAttribute('href');                                         
                                    else
                                        $c['content_sections_image_2_link'] = $l->getAttribute('href');
                                    
                                }
                            }
                        }
                        if(!empty($c))
                        $content[] = $c;
                    }
                }

                return $content;
            }
        ],
		'banner_slider' => [
            'selector' => ' div[contains(@id, "ilFunds-slider") a',
            'type' => 'function',
            'function' => function ($xpath, $downloadImage, $FILE_URL_PREFIX ) {
                $slider = [];
                $slideElements = $xpath->query('//div[contains(@id, "ilFunds-slider")]//div[contains(@class, "slides")]')->item(0);
                $slides = $slideElements->childNodes;
                foreach ($slides as $k => $slide) {                  
                    if ($slide->nodeType === XML_ELEMENT_NODE) {                        
                        if($slide->nodeName === 'div'){
                            $image = $slide->getElementsByTagName('img');
                            if(isset($image[0])){
                                $slider[$k]['banner_image'] = $image->item(0)->getAttribute('src');
								
								$slider[$k]['banner_image'] = $downloadImage( $FILE_URL_PREFIX .  $slider[$k]['banner_image']);
							   if(!$slider[$k]['banner_image']){				   
								   $slider[$k]['banner_image'] = $downloadImage(str_replace(' ', '%20', strtolower('https://illinoistreasurergovprod.blob.core.usgovcloudapi.net' .  $slider[$k]['banner_image'])));
							   }								
                                $slider[$k]['banner_image_link'] = '';
                                }
                            }     
                        elseif($slide->nodeName === 'a'){
                            $image = $slide->getElementsByTagName('img');
                            if(isset($image[0])){
                                $slider[$k]['banner_image'] = $image->item(0)->getAttribute('src');
								
								$slider[$k]['banner_image'] = $downloadImage( 'https://illinoistreasurer.gov' .  $slider[$k]['banner_image']);
							   if(!$slider[$k]['banner_image']){				   
								   $slider[$k]['banner_image'] = $downloadImage(str_replace(' ', '%20', strtolower('https://illinoistreasurergovprod.blob.core.usgovcloudapi.net' .  $slider[$k]['banner_image'])));
							   }	
                                $slider[$k]['banner_image_link'] = $slide->getAttribute('href');
                            }
                        }                   
                    }
                }
                return $slider;
            }, 
        ],      
    ],     
    'historical_daily_rates-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
    ],     
    'content-with-banner-image-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
		'content_with_banner_image' => [
			'selector' => ' img[@id="sections_image_0"]',
			'type' => 'image',
		],
		'content_with_banner_image_content_1' => [
			'selector' => ' div[@id="sections_pageContent_1"]',
			'type' => 'html',
		],
		'content_with_banner_image_2' => [
			'selector' => ' img[@id="sections_image_1"]',
			'type' => 'image',
		],
		'content_with_banner_image_content_2' => [
			'selector' => ' div[@id="sections_pageContent_2"]',
			'type' => 'html',
		],
		'content_with_banner_image_3' => [
			'selector' => ' img[@id="sections_image_2"]',
			'type' => 'image',
		],
		'content_with_banner_image_3_link' => [
			'selector' => ' a[@id="sections_imageLink_2"]',
			'type' => 'link',
		],
		'content_with_banner_image_content_3' => [
			'selector' => ' div[@id="sections_pageContent_3"]',
			'type' => 'html',
		],
		'youtube_1_iframe' => [
			'selector' => ' iframe[@id="sections_video1_3"]',
			'type' => 'iframe',
		],
		'youtube_1_iframe_caption' => [
			'selector' => ' div[@id="sections_video1Description_3"]',
			'type' => 'text',
		],
		'youtube_2_iframe' => [
			'selector' => ' iframe[@id="sections_video2_3"]',
			'type' => 'iframe',
		],
		'youtube_2_iframe_caption' => [
			'selector' => ' div[@id="sections_video2Description_3"]',
			'type' => 'text',
		],		
		'content_with_banner_image_content_4' => [
			'selector' => ' div[@id="sections_pageContent_4"]',
			'type' => 'html',
		],		
		'youtube_3_iframe' => [
			'selector' => ' iframe[@id="sections_video1_4"]',
			'type' => 'iframe',
		],
		'youtube_3_iframe_caption' => [
			'selector' => ' div[@id="sections_video1Description_4"]',
			'type' => 'text',
		],		
		'youtube_4_iframe' => [
			'selector' => ' iframe[@id="sections_video2_4"]',
			'type' => 'iframe',
		],
		'youtube_4_iframe_caption' => [
			'selector' => ' div[@id="sections_video2Description_4"]',
			'type' => 'text',
		],	
		'content_with_banner_image_content_5' => [
			'selector' => ' div[@id="sections_pageContent_5"]',
			'type' => 'html',
		],
		
		'content_with_banner_image_4' => [
			'selector' => ' img[@id="sections_image_5"]',
			'type' => 'image',
		],
		
		'image_4_caption' => [
			'selector' => " p i[normalize-space()!='']",
			'type' => 'text',
		],
		'content_with_banner_image_content_6' => [
			'selector' => ' div[@id="sections_pageContent_6"]',
			'type' => 'html',
		],
		
		'content_with_banner_image_5' => [
			'selector' => ' img[@id="sections_image_6"]',
			'type' => 'image',
		],
		
    ],     
    'daily_rates-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
		'content_above_table' => [
			'selector' => ' div[@id="pageContent"]',
			'type' => 'html',
		],
// 		'content_below_table' => [
// 			'selector' => ' div[@id="pageContent2"]',
// 			'type' => 'html',
// 		],
    ],     
    'financial_institutions-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
    ],     
    'income_limits-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
    ],     
    'content_with_table_RIP-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
    ],     
    'admin_dashboard-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
    ],     
    'home-template.php' => [
        'title' => [
            'selector' => ' div[@class="page_title2"] div[@class="title"] h1',
            'type' => 'text',
        ],
    ],
];



function getAllPages(){
    $args = [
        'post_type'      => 'page',   
        'posts_per_page' => -1,
		'post__in' => [82, 97, 98, 100, 149, 4983]
    ];
    $query = new WP_Query($args);
    $pages = [
        'not_assigned_templates'=> [],
        'not_have_scrapper_link'=> [],
        'ready_for_scrape' => [],
        'templates' => [],
    ];


    if ($query->have_posts()) {
        while ($query->have_posts()) {
			$ignore = [
				// Missed 				
				'content_with_table_RIP-template.php',
				'content_with_multiple_images_ePAY-template.php',
				'content-with-static-image-template.php',
				'heritage-month-template.php',
				'calender-template.php',
				'The_Illinois_Funds-template.php',
				'home-template.php',

				// Problems				
				'archived_documents-template.php',
				'content_with_images_documents-template.php',
				'College_Savings-template.php',
				'historical_daily_rates-template.php',
				'media_gallery-template.php',
				'heritage-month-template.php',
				'styled_table-template.php',
				'daily_rates-template.php',
				'contact_form-template.php',
				'categorized_documents-template.php'
			];
			
			$tm = ['content_with_multiple_images_invest-template.php'];
			
            $query->the_post();
            $scappe_from = get_post_meta( get_the_ID(), 'scrapper_field', TRUE );
            $t = get_the_title();
            $template_file = get_post_meta( get_the_ID(), '_wp_page_template', TRUE );
// 			if($template_file === 'The_Illinois_Funds-template.php'){
            if(!in_array($template_file, $ignore)){
//             if(in_array($template_file, $tm)){
			if( $scappe_from !== false &&  $scappe_from !== null &&  $scappe_from !== '' ){
                if($template_file && $template_file !== 'default' ){
                    if(!isset($templates[$template_file])){
						$url =  $scappe_from;
						$pageName = basename(parse_url($url, PHP_URL_PATH));
						$pages['ready_for_scrape'][] = [
							'wp_pageid' => get_the_ID(),
							'scrap_from' =>  $scappe_from ,
							'template' => $template_file,
							'title' => $t,
							'slug' => $pageName
						];
					}
                    if(!isset($pages['templates'][$template_file]))
                        $pages['templates'][$template_file] = 'te';

                }
                else{
                    $pages['not_assigned_templates'][] = [
                        'wp_pageid' => get_the_ID(),
                        'scrap_from' =>  $scappe_from ,
                        'template' => $template_file,
                        'title' => $t
                    ];
                }

            }
            else{
                if(!$template_file && $template_file === 'default'){
                    $pages['not_assigned_templates'][] = [
                        'wp_pageid' => get_the_ID(),
                        'title' => $t
                    ];
                }
                else
                    if(!isset($pages['templates'][$template_file]))
                    $pages['templates'][$template_file] = 'te';

                    $pages['not_have_scrapper_link'][] = [
                    'wp_pageid' => get_the_ID(),
                    'template' => $template_file,
                    'title' => $t
                ];
            }
		}
            
            wp_reset_postdata();
        }
    }
    return $pages;
}




