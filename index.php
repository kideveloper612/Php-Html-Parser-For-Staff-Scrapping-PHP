<?php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;


$url = $_GET['url'];
function send_request($url){
	try {
		$headers = array(
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36'
		);
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET"
		));

		$response = curl_exec($curl);

		if (curl_errno($curl)) {
		    throw new Exception(curl_error($ch), 1);
		}
		curl_close($curl);
		return $response;
	}
	catch (Exception $e) {
		echo $e->getMessage();
		sleep(3);
		return send_request(url);
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
				$title = '';
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

			if (! in_array($line, $result)) {
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
				$title = '';
			}
			$name_dom = $staff_text_wrap -> find('[class*=staff-info__name]');
			if ($name_dom) {
				$name = trim($name_dom -> firstChild() -> text());
			} else {
				$name = '';
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
			$title = $name = $email = '';
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
		if (! in_array($line, $result)) {
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
				$title = '';
			}
			$name_dom = $uabb_team_content -> find('[class*=uabb-team-name-text]', 0);
			if ($name_dom) {
				$name = trim($name_dom -> text());
			} else {
				$name = '';
			}
			$description_dom = $uabb_team_content -> find('[class*=uabb-team-desc-text]', 0);
			if ($description_dom) {
				$description = trim($description_dom -> text());
			} else {
				$description = '';
			}
		} else {
			$title = $name = $description = '';
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
		if (! in_array($line, $result)) {
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
				$title = '';
			}
			$name_dom = $text_dom -> find('h3');
			if ($name_dom) {
				$name = trim($name_dom -> text());
			} else {
				$name = '';
			}
			$phone_dom = $text_dom -> find('[class*=staffphone]', 0);
			if ($phone_dom) {
				$phone = trim($phone_dom -> text());
			} else {
				$phone = '';
			}
		} else {
			$title = $name = $phone = '';
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
		if (! in_array($line, $result)) {
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
				$title = '';
			}
			$name_dom = $details_sect -> find('.info .name');
			if ($name_dom) {
				$name = trim($name_dom -> text());
			} else {
				$name = '';
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
			$title = $name = $description = $phone = $email = '';
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
		if (! in_array($line, $result)) {
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
	if (count($staff_info_items) > 0) {
		$output = staff_info_items($staff_info_items);
	} elseif (count($uabb_wraps) > 0) {
		$output = uabb_wraps($uabb_wraps);
	} elseif (count($staff_items) > 0) {
		$output = staff_items($staff_items);
	} elseif (count($box_containers) > 0) {
		$output = box_containers($box_containers);
	}
	elseif (count($content_doms) > 0) {
		$output = contents($content_doms);
	}
	
	echo "<pre>";
	print_r($output);
	echo "</pre>";
}

$dom = new Dom;
$response = send_request($url);
$dom->load($response);
dom_parse();


?>