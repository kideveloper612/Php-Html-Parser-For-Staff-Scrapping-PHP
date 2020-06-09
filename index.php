<?php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;


if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
	echo("Just GET REQUESTS are allowed!");
	return;
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['url'])) {
	$url = $_GET['url'];
} else {
	echo("Please put your url as request parameter");
	return;
}

function send_request($url){
	try {
	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	    $res = curl_exec($curl);

		if (curl_errno($curl)) {
		    throw new Exception(curl_error($ch), 1);
		}
		curl_close($curl);
		return $res;
	}
	catch (Exception $e) {
		echo $e->getMessage();
		sleep(3);
		return send_request($url);
	}
}

function contents($content_doms) {
	$result = array();
	for ($i=0; $i < count($content_doms); $i++) { 
		$employeeTitle = $content_doms[$i] -> find('[template="employeeTitle"]', 0);
		if ($employeeTitle) {
			$title_dom = $employeeTitle -> find('[itemprop="jobTitle"]', 0);
			if ($title_dom) {
				$title = $title_dom -> text(true);
			} else {
				continue;
			}
			$givenName_dom = $employeeTitle -> find('[itemprop="givenName"]', 0);
			if ($givenName_dom) {
				$givenName = $givenName_dom -> text(true);
			} else {
				$givenName = '';
			}
			$familyName_dom = $employeeTitle -> find('[itemprop="familyName"]', 0);
			if ($familyName_dom) {
				$familyName = $familyName_dom -> text(true);
			} else {
				$familyName = $familyName_dom -> text(true);
			}
			$description_dom = $content_doms[$i] -> find('[itemprop="description"]', 0);
			if ($description_dom) {
				$description = $description_dom -> text(true);
			} else {
				$description = '';
			}
			$phone_dom = $content_doms[$i] -> find('[itemprop="telephone"]', 0);
			if ($phone_dom && $phone_dom -> hasAttribute('href')) {
				$phone = str_replace('tel:', '', $phone_dom -> getAttribute('href'));
			} else {
				$phone = '';
			}
			$email_dom = $content_doms[$i] -> find('a[href*=mailto:]', 0);
			if ($email_dom && $email_dom -> hasAttribute('href')) {
				$email = str_replace('mailto:', '', $email_dom -> getAttribute('href'));
			} else {
				$email = '';
			}
			$employeeMedia_dom = $content_doms[$i] -> find('[template="employeeMedia"]', 0);
			if ($employeeMedia_dom) {
				$image_dom = $employeeMedia_dom -> find('img', 0);
				if ($image_dom && $image_dom -> hasAttribute('data-src')) {
					$image = $image_dom -> getAttribute('data-src');
				} elseif ($image_dom && $image_dom -> hasAttribute('src')) {
					$image = $image_dom -> getAttribute('src');
				}
				 else {
					$image = '';
				}
			} else {
				$image = '';
			}
			if ($givenName !== '' && $familyName !== '') {
				$name = $givenName.' '.$familyName;
			} elseif ($givenName !== '') {
				$name = $givenName;
			} elseif ($familyName !== '') {
				$name = $familyName;
			} else {
				continue;
			}
			$line = array(
				'name' => $name,
				'title' => $title,
				'description' => $description,
				'phone' => $phone,
				'email' => $email,
				'image' => $image
			);

			if (array_filter($line) && !in_array($line, $result)) {
				array_push($result, json_encode($line));
			}
		}
	}
	return $result;
}

function staff_info_items($staff_items) {
	$result = array();
	for ($i=0; $i < count($staff_items); $i++) { 
		$staff_text_wrap = $staff_items[$i] -> find('[class*=staff-info__text-wrap]', 0);
		if ($staff_text_wrap) {
			$title_dom = $staff_text_wrap -> find('[class*=staff-info__job-title]', 0);
			if ($title_dom) {
				$title = trim($title_dom -> text());
			} else {
				continue;
			}
			$name_dom = $staff_text_wrap -> find('[class*=staff-info__name]');
			if ($name_dom) {
				$name = trim($name_dom -> firstChild() -> text());
			} else {
				continue;
			}
			$email_wrap = $staff_text_wrap -> find('[class*=staff-info__email-wrap]', 0);
			if ($email_wrap) {
				$email_dom = $email_wrap -> find('a[href*=mailto:]', 0);
				if ($email_dom) {
					$email = trim(str_replace('mailto:', '', $email_dom -> getAttribute('href')));
				} else {
					$email = '';
				}
			} else {
				$email = '';
			}
		} else {
			continue;
		}
		$staff_info_image = $staff_items[$i] -> find('[class*=staff-info__image]', 0);
		if ($staff_info_image) {
			$image_dom = $staff_info_image -> find('img', 0);
			if ($image_dom && $image_dom -> hasAttribute('data-src')) {
				$image = $image_dom -> getAttribute('data-src');
			} elseif ($image_dom && $image_dom -> getAttribute('src')) {
				$image = $image_dom -> getAttribute('src');
			} else {
				$image = '';
			}
		} else {
			$image = '';
		}
		$line = array(
			'name' => $name, 
			'title' => $title,
			'description' => '',
			'phone' => '',
			'email' => $email,
			'image' => $image
		);
		if (array_filter($line) && !in_array($line, $result)) {
			array_push($result,json_encode($line));
		}
	}
	return $result;
}

function uabb_wraps($uabb_wraps) {
	$result = [];
	for ($i=0; $i < count($uabb_wraps); $i++) { 
		$uabb_team_content = $uabb_wraps[$i] -> find('[class*=uabb-team-content]', 0);
		if ($uabb_team_content) {
			$title_dom = $uabb_team_content -> find('[class*=uabb-team-desgn-text]', 0);
			if ($title_dom) {
				$title = trim($title_dom -> text());
			} else {
				continue;
			}
			$name_dom = $uabb_team_content -> find('[class*=uabb-team-name-text]', 0);
			if ($name_dom) {
				$name = trim($name_dom -> text());
			} else {
				continue;
			}
			$description_dom = $uabb_team_content -> find('[class*=uabb-team-desc-text]', 0);
			if ($description_dom) {
				$description = trim($description_dom -> text());
			} else {
				$description = '';
			}
		} else {
			continue;
		}
		$uabb_image_content = $uabb_wraps[$i] -> find('[class*=uabb-image-content]', 0);
		if ($uabb_image_content) {
			$image_dom = $uabb_image_content -> find('img[class*=uabb-photo-img]', 0);
			if ($image_dom && $image_dom -> hasAttribute('data-src')) {
				$image = $image_dom -> getAttribute('data-src');
			} elseif ($image_dom && $image_dom -> hasAttribute('src')) {
				$image = $image_dom -> getAttribute('src');
			} else {
				$image = '';
			}
		} else {
			$image = '';
		}
		$line = array(
			'name' => $name, 
			'title' => $title,
			'description' => $description,
			'phone' => '',
			'email' => '',
			'image' => $image
		);
		if (array_filter($line) && !in_array($line, $result)) {
			array_push($result, json_encode($line));
		}
	}
	return $result;
}

function staff_items($staff_items) {
	$result = [];
	for ($i=0; $i < count($staff_items); $i++) { 
		$text_dom = $staff_items[$i] -> find('div');
		if ($text_dom) {
			$title_dom = $text_dom -> find('h4');
			if ($title_dom) {
				$title = trim($title_dom -> text());
			} else {
				continue;
			}
			$name_dom = $text_dom -> find('h3');
			if ($name_dom) {
				$name = trim($name_dom -> text());
			} else {
				continue;
			}
			$phone_dom = $text_dom -> find('[class*=staffphone]', 0);
			if ($phone_dom) {
				$phone = trim($phone_dom -> text());
			} else {
				$phone = '';
			}
		} else {
			continue;
		}
		$image_dom = $staff_items[$i] -> find('img');
		if ($image_dom) {
			if ($image_dom -> hasAttribute('data-src')) {
				$image = $image_dom -> getAttribute('data-src');
			} elseif ($image_dom -> hasAttribute('src')) {
				$image = $image_dom -> getAttribute('src');
			} else {
				$image = '';
			}
		} else {
			$image = '';
		}
		$line = array(
			'name' => $name, 
			'title' => $title,
			'description' => '',
			'phone' => $phone,
			'email' => '',
			'image' => $image
		);
		if (array_filter($line) && !in_array($line, $result)) {
			array_push($result, json_encode($line));
		}
	}
	return $result;
}

function box_containers($box_containers) {
	$result = [];
	for ($i=0; $i < count($box_containers); $i++) { 
		$details_sect = $box_containers[$i] -> find('.details-sect');
		if ($details_sect) {
			$title_dom = $details_sect -> find('.info > .title');
			if ($title_dom) {
				$title = trim($title_dom -> text());
			} else {
				continue;
			}
			$name_dom = $details_sect -> find('.info .name');
			if ($name_dom) {
				$name = trim($name_dom -> text());
			} else {
				continue;
			}
			$description_dom = $details_sect -> find('.info .description');
			if ($description_dom) {
				$description = trim($description_dom -> text());
			} else {
				$description = '';
			}
			$phone_dom = $details_sect -> find('.phone-num');
			if (count($phone_dom) && $phone_dom -> hasAttribute('href')) {
				$phone = str_replace('tel:', '', $phone_dom -> getAttribute('href'));
			} else {
				$phone = '';
			}
			$email_dom = $details_sect -> find('.email');
			if (count($email_dom) && $email_dom -> hasAttribute('href')) {
				$email = str_replace('mailto:', '', $email_dom -> getAttribute('href'));
			} else {
				$email = '';
			}
		} else {
			continue;
		}
		$img_sect = $box_containers[$i] -> find('.img-sect');
		if ($img_sect) {
			$image_dom = $img_sect -> find('img');
			if ($image_dom && $image_dom -> hasAttribute('data-src')) {
				$image = $image_dom -> getAttribute('data-src');
			} elseif ($image_dom && $image_dom -> hasAttribute('src')) {
				$image = $image_dom -> getAttribute('src');
			} else {
				$image = '';
			}
		} else {
			$image = '';
		}
		$line = array(
			'name' => $name, 
			'title' => $title,
			'description' => '',
			'phone' => $phone,
			'email' => '',
			'image' => $image
		);
		if (array_filter($line) && !in_array($line, $result)) {
			array_push($result, json_encode($line));
		}
	}
	return $result;
}

function yui3_u_1_6_vcards($yui3_u_1_6_vcards) {
	$result = array();
	for ($i=0; $i < count($yui3_u_1_6_vcards); $i++) { 
		$vcard = $yui3_u_1_6_vcards[$i];
		$name_dom = $vcard -> find('.fn a');
		if (count($name_dom) > 0){
			$name = trim($name_dom -> text());
		} else {
			continue;
		}
		$title_dom = $vcard -> find('dd.title');
		if (count($title_dom) > 0) {
			$title = $title_dom -> text();
		} else {
			continue;
		}
		$image_dom = $vcard -> find('img');
		if (count($image_dom) > 0) {
			if ($image_dom -> hasAttribute('data-src')) {
				$image = $image_dom -> getAttribute('data-src');
			} elseif ($image_dom -> hasAttribute('src')) {
				$image = $image_dom -> getAttribute('src');
			} else {
				$image = '';
			}
		} else {
			$image = '';
		}
		$email_dom = $vcard -> find('.email');
		if (count($email_dom)) {
			$email = trim($email_dom -> text());
		} else {
			$email = '';
		}
		$phone_dom = $vcard -> find('.phone');
		if (count($phone_dom) > 0) {
			$phone = trim($phone_dom -> text());
		} else {
			$phone = '';
		}
		$line = array(
			'name' => $name, 
			'title' => $title,
			'description' => '',
			'phone' => $phone,
			'email' => $email,
			'image' => $image
		);
		if (array_filter($line) && !in_array($line, $result)) {
			array_push($result, json_encode($line));
		}
	}
	return $result;
}

function isDisplayable_contents($isDisplayable_contents) {
	$result = array();
	for ($i=0; $i < count($isDisplayable_contents); $i++) { 
		$content = $isDisplayable_contents[$i];
		$text_dom = $content -> find('.text');
		if (count($text_dom) > 0) {
			$name_dom = $text_dom -> find('[if^=title]');
			if (count($name_dom) > 0) {
				$name = trim($name_dom -> text());
			} else {
				continue;
			}
			$title_dom = $text_dom -> find('[if^=subTitle]');
			if (count($title_dom) > 0) {
				$title = trim($title_dom -> text());
			} else {
				continue;
			}
		} else {
			continue;
		}
		$media_dom = $content -> find('.media');
		if (count($media_dom) > 0) {
			$image_dom = $media_dom -> find('img');
			if (count($image_dom) > 0 && $image_dom -> hasAttribute('src')) {
				$image = $image_dom -> getAttribute('src');
			} else {
				$image = '';
			}
		} else {
			$image = '';
		}
		$line = array(
			'name' => $name, 
			'title' => $title,
			'description' => '',
			'phone' => '',
			'email' => '',
			'image' => $image
		);
		if (array_filter($line) && !in_array($line, $result)) {
			array_push($result, json_encode($line));
		}
	}
	return $result;
}

function tabDisplay_cards($site, $tabDisplay_cards) {
	$result = array();
	for ($i=0; $i < count($tabDisplay_cards); $i++) { 
		$staff_info = $tabDisplay_cards[$i] -> find('.staff-info');
		if (count($staff_info) > 0) {
			$name_dom = $staff_info -> find('.staff-title');
			if (count($name_dom) > 0) {
				$name = trim($name_dom -> text());
			} else {
				continue;
			}
			$title_dom = $staff_info -> find('.staff-desc');
			if (count($title_dom) > 0) {
				$title = trim($title_dom -> find('em') -> text());
			} else {
				continue;
			}
			$phone_dom = $staff_info -> find('[aria-label="Phone"]');
			if (count($phone_dom) > 0) {
				if ($phone_dom -> hasAttribute('href')) {
					$phone = trim(str_replace('tel:', '', $phone_dom -> getAttribute('href')));
				} else {
					$phone = '';
				}
			} else {
				$phone = '';
			}
		} else {
			continue;
		}
		$staff_img = $tabDisplay_cards[$i] -> find('.staff-img');
		if (count($staff_img) > 0) {
			$image_dom = $staff_img -> find('img');
			if (count($image_dom) > 0) {
				$base_url = parse_url($site)['host'];
				$image = join('/', array('http://'.parse_url($site)['host'], $image_dom -> getAttribute('src')));
			} else {
				$image = '';
			}
		} else {
			$image = '';
		}
		$line = array(
			'name' => $name, 
			'title' => $title,
			'description' => '',
			'phone' => '',
			'email' => '',
			'image' => $image
		);
		if (array_filter($line) && !in_array($line, $result)) {
			array_push($result, json_encode($line));
		}
	}
	return $result;
}
function dom_parse(){
	$dom = $GLOBALS['dom'];
	$content_doms = $dom -> find('div[class=content]');
	$staff_info_items = $dom -> find('li[class*=staff-info__item]');
	$staff_items = $dom -> find('li[class*=staff-item]');
	$uabb_wraps = $dom -> find('[class*=uabb-team-member-wrap]');
	$box_containers = $dom -> find('#tabs-mtt .member-list [class*=box-container]');
	$yui3_u_1_6_vcards = $dom -> find('#staffList .yui3-u-1-6 .vcard');
	$isDisplayable_contents = $dom -> find('.deck section[if^=isDisplayable] .content');
	$tabDisplay_cards = $dom -> find('#tabDisplay .staff-card');
	if (count($staff_info_items) > 0) {
		$output = staff_info_items($staff_info_items);
	} elseif (count($uabb_wraps) > 0) {
		$output = uabb_wraps($uabb_wraps);
	} elseif (count($staff_items) > 0) {
		$output = staff_items($staff_items);
	} elseif (count($box_containers) > 0) {
		$output = box_containers($box_containers);
	} elseif (count($yui3_u_1_6_vcards) > 0) {
		$output = yui3_u_1_6_vcards($yui3_u_1_6_vcards);
	} elseif (count($isDisplayable_contents) > 0) {
		$output = isDisplayable_contents($isDisplayable_contents);
	} elseif ($tabDisplay_cards) {
		$output = tabDisplay_cards($GLOBALS['url'], $tabDisplay_cards);
	}
	elseif (count($content_doms) > 0) {
		$output = contents($content_doms);
	}

	// if (empty($output)) {
	// 	return 'no';
	// } else {
	// 	return 'yes';
	// }
	
	echo "<pre>";
	print_r($output);
	echo "</pre>";
}


// function read_urls() {
// 	$file_name = './urls.csv';
// 	$file = fopen($file_name, 'r');
// 	$result = [];
// 	while (! feof($file)) {
// 		$line = fgetcsv($file);
// 		if (is_array($line) && ! empty($line)) {
// 			if (! in_array($line[0], $result)) {
// 				array_push($result, $line[0]);
// 			}
// 		}
// 	}
// 	fclose($file);
// 	return $result;
// }

// $urls = read_urls();
// $urls = ['http://www.tonkinchevy.com/MeetOurDepartments'];
// $dom = new Dom;
// $fp = fopen('yet.csv', 'a');
// $count = 0;
// foreach ($urls as $url) {
// 	$count += 1;
// 	echo($count.' '.$url."\n");
// 	$response = send_request($url);
// 	$dom->load($response);
// 	$r = dom_parse();
// 	echo($r);
// 	if ($r === 'no') {
// 		fputcsv($fp, [$url]);
// 	}
// }
// fclose($fp);


$dom = new Dom;
$response = send_request($url);
$dom->load($response);
$r = dom_parse();

?>