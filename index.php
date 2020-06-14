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
		if (count($details_sect) > 0) {
			$title_dom = $details_sect -> find('.info > .title');
			if (count($title_dom)) {
				$title = trim($title_dom -> text());
			} else {
				$title = '';
			}
			$name_dom = $details_sect -> find('.info .name');
			if (count($name_dom) > 0) {
				$name = trim($name_dom -> text());
			} else {
				$name = '';
			}
			$description_dom = $details_sect -> find('.info .description');
			if (count($description_dom) > 0) {
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
			$img_sect = $box_containers[$i] -> find('.img-sect');
			if (count($img_sect) > 0) {
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
		} else {
			$name = $title = $phone = $email = '';
		}
		

		$top_box  = $box_containers[$i] -> find('.top-box');
		if (count($details_sect) <= 0 && count($top_box) > 0) {
			$name_dom = $box_containers[$i] -> find('.name');
			if (count($name_dom) > 0) {
				$name = trim($name_dom -> text());
			} else {
				$name = '';
			}
			$title_dom = $box_containers[$i] -> find('.title');
			if (count($title_dom) > 0) {
				$title = trim($title_dom -> text());
			} else {
				$title = '';
			}
			$phone_dom = $box_containers[$i] -> find('.phone-num');
			if (count($phone_dom) > 0) {
				if ($phone_dom -> hasAttribute('href')) {
					$phone = trim(str_replace('tel:', '', $phone_dom -> getAttribute('href')));
				} else {
					$phone = '';
				}
			} else {
				$phone = '';
			}
			$email_dom = $box_containers[$i] -> find('.email');
			if (count($email_dom) > 0) {
				if ($email_dom -> hasAttribute('href')) {
					$email = trim(str_replace('mailto:', '', $email_dom -> getAttribute('href')));
				} else {
					$email = '';
				}
			} else {
				$email = '';
			}
			$image_dom = $box_containers[$i] -> find('img');
			if (count($image_dom) > 0) {
				if ($image_dom -> hasAttribute('data-src')) {
					$image = parse_url($GLOBALS['url'])['host'] . $image_dom -> getAttribute('data-src');
				} elseif ($image_dom -> hasAttribute('src')) {
					$image = parse_url($GLOBALS['url'])['host'] . $image_dom -> getAttribute('src');
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
		} elseif (count($vcard -> find('.fn a span')) > 0) {
			$title = $vcard -> find('.fn a span') -> text();
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
			$email = '';
			$pre_name_dom = $text_dom -> find('.copy b');
			if (count($pre_name_dom) > 0) {
				$name = trim($pre_name_dom -> text());
			} else {
				$name = '';
			}
			$pre_title_dom = $text_dom -> find('.copy div > i');
			if (count($pre_title_dom) > 0) {
				$title = trim($pre_title_dom -> text());
			} else {
				$title = '';
			}
			$pre_email_dom = $text_dom -> find('.copy div:last-child');
			if (count($pre_email_dom) > 0) {
				$email = trim($pre_email_dom -> text());
			} else {
				$email = '';
			}
			if (empty($name)) {
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
			'email' => $email,
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

function listing_employee_items($listing_employee_items) {
	$result = array();
	for ($i=0; $i < count($listing_employee_items); $i++) { 
		$text_dom = $listing_employee_items[$i] -> find('.listing-employee__department-employee-description');
		if (count($text_dom) > 0) {
			$name_dom = $text_dom -> find('.listing-employee__department-employee-name');
			if (count($name_dom) > 0) {
				$name = trim($name_dom -> text());
			} else {
				continue;
			}
			$title_dom = $text_dom -> find('.listing-employee__department-employee-job');
			if (count($title_dom) > 0) {
				$title = trim($title_dom -> text());
			} else {
				continue;
			}
			$email_dom = $text_dom -> find('a');
			if (count($email_dom) > 0 && $email_dom -> hasAttribute('href')) {
				$email = trim(str_replace('mailto:', '', $email_dom -> getAttribute('href')));
			} else {
				$email = '';
			}
		} else {
			continue;
		}
		$image_dom = $listing_employee_items[$i] -> find('img');
		if (count($image_dom) > 0 ){
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
			'phone' => '',
			'email' => $email,
			'image' => $image
		);
		if (array_filter($line) && !in_array($line, $result)) {
			array_push($result, json_encode($line));
		}
	}
	return $result;
}

function listing_alpha_items($listing_alpha_items) {
	$result = array();
	for ($i=0; $i < count($listing_alpha_items); $i++) { 
		$text_dom = $listing_alpha_items[$i] -> find('.listing-employee-alpha__department-employee-description');
		if (count($text_dom) > 0) {
			$name_dom = $text_dom -> find('.listing-employee-alpha__department-employee-name');
			if (count($name_dom) > 0) {
				$name = trim($name_dom -> text());
			} else {
				continue;
			}
			$title_dom = $text_dom -> find('.listing-employee-alpha__department-employee-job');
			if (count($title_dom) > 0) {
				$title = trim($title_dom -> text());
			} else {
				continue;
			}
			$email_dom = $text_dom -> find('.listing-employee-alpha__department-employee-email');
			if (count($email_dom) > 0 && $email_dom -> hasAttribute('href')) {
				$email = trim(str_replace('mailto:', '', $email_dom -> getAttribute('href')));
			} else {
				$email = '';
			}
			$phone_dom = $text_dom -> find('.listing-employee-alpha__department-employee-phone');
			if (count($phone_dom) > 0 && $phone_dom -> hasAttribute('href')) {
				$phone = trim(str_replace('tel:', '', $phone_dom -> getAttribute('href')));
			} else {
				$phone = '';
			}
		} else {
			continue;
		}
		$image_dom = $listing_alpha_items[$i] -> find('img');
		if (count($image_dom) > 0) {
			if ($image_dom -> hasAttribute('data-src')) {
				$image = trim($image_dom -> getAttribute('data-src'));
			} elseif ($image_dom -> hasAttribute('src')) {
				$image = trim($image_dom -> getAttribute('src'));
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
			'email' => $email,
			'image' => $image
		);
		if (array_filter($line) && !in_array($line, $result)) {
			array_push($result, json_encode($line));
		}
	}
	return $result;
}

function salesman_maincards($salesman_maincards) {
	$result = array();
	for ($i=0; $i < count($salesman_maincards); $i++) { 
		$salesman_maincard = $salesman_maincards[$i];
		$name_dom = $salesman_maincard -> find('.salesman__name');
		if (count($name_dom) > 0) {
			$name = $name_dom -> text();
		} else {
			continue;
		}
		$title_dom = $salesman_maincard -> find('.salesman__position');
		if (count($title_dom) > 0) {
			$title = $title_dom -> text();
		} else {
			continue;
		}
		$image_dom = $salesman_maincard -> find('img');
		if (count($image_dom) > 0) {
			if ($image_dom -> hasAttribute('data-src')) {
				$image = $image_dom -> getAttribute('data-src');
			} elseif ($image_dom -> hasAttribute('src')) {
				$image = $image_dom -> getAttribute('src');
			} else {
				$image = '';
			}
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

function wpstaff_persons($wpstaff_persons) {
	$result = [];
	for ($i=0; $i < count($wpstaff_persons); $i++) { 
		$wpstaff_person = $wpstaff_persons[$i];
		$text_dom = $wpstaff_person -> find('.wpstaff-person-info');
		if (count($text_dom) > 0) {
			$name_dom = $text_dom -> find('.wpstaff-person-info-name');
			if (count($name_dom) > 0) {
				$name = trim($name_dom -> text());
			} else {
				continue;
			}
			$title_dom = $text_dom -> find('.wpstaff-person-info-title');
			if (count($title_dom) > 0) {
				$title = trim($title_dom -> text());
			} else {
				continue;
			}
			$email_dom = $text_dom -> find('.wpstaff-person-info-email');
			if (count($email_dom) > 0 && $email_dom -> hasAttribute('href')) {
				$email = trim(str_replace('mailto:', '', $email_dom -> getAttribute('href')));
			} else {
				$email = '';
			}
			$phone_dom = $text_dom -> find('.wpstaff-person-info-phone');
			if (count($phone_dom) > 0 && $phone_dom -> hasAttribute('href')) {
				$phone = trim(str_replace('tel:', '', $phone_dom -> getAttribute('href')));
			} else {
				$phone = '';
			}
		}
		$image_dom = $wpstaff_person -> find('img');
		if (count($image_dom) > 0) {
			if ($image_dom -> hasAttribute('data-src')) {
				$image = $image_dom -> getAttribute('data-src');
			} elseif ($image_dom -> hasAttribute('src')) {
				$image = $image_dom -> getAttribute('src');
			} else {
				$image = '';
			}
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

function isDisplayable_video_contents($isDisplayable_video_contents) {
	$result = array();
	for ($i=0; $i < count($isDisplayable_video_contents); $i++) { 
		$text_dom = $isDisplayable_video_contents[$i] -> find('.text');
		if (count($text_dom) > 0) {
			$title_name_dom = $text_dom -> find('[if^="title"]');
			if (count($title_name_dom) > 0) {
				$title_name = explode(',', $title_name_dom -> text()) ;
				if (sizeof($title_name) > 1) {
					$name = $title_name[0];
					$title = $title_name[1];
				} else {
					continue;
				}
			} else {
				continue;
			}
		}
		$image_dom = $isDisplayable_video_contents[$i] -> find('.video-youTube iframe');
		if (count($image_dom) > 0 && $image_dom -> hasAttribute('src')) {
			$video = $image_dom -> getAttribute('src');
		} else {
			$video = '';
		}
		$line = array(
			'name' => $name, 
			'title' => $title,
			'description' => '',
			'phone' => '',
			'email' => '',
			'image' => $video
		);
		if (array_filter($line) && !in_array($line, $result)) {
			array_push($result, json_encode($line));
		}
	}
	return $result;
}

function contentGrid_staffListWrapper_staff_items($staff_items) {
	$result = array();
	for ($i=0; $i < count($staff_items); $i++) { 
		$staff_item = $staff_items[$i];
		$text_dom = $staff_item -> find('.staff');
		if (count($text_dom) > 0) {
			$name_dom = $text_dom -> find('.staffName');
			if (count($name_dom) > 0) {
				$name = trim($name_dom -> text());
			} else {
				continue;
			}
			$title_dom = $text_dom -> find('.staffJobTitle');
			if (count($title_dom) > 0) {
				$title = trim($title_dom -> text());
			} else {
				continue;
			}
			$phone_dom = $text_dom -> find('.staffPhone');
			if (count($phone_dom) > 0) {
				$phone = trim($phone_dom -> text());
			} else {
				$phone = '';
			}
			$email_dom = $text_dom -> find('.staffEmail');
			if (count($email_dom) > 0) {
				$email = trim($email_dom -> text());
			} else {
				$email = '';
			}
		} else {
			continue;
		}
		$media_dom = $staff_item -> find('.staffImage');
		$base_url = parse_url($GLOBALS['url'])['host'];
		if (count($media_dom) > 0) {
			if ($media_dom -> hasAttribute('onerror')) {
				$image = 'https://'.$base_url.'/'.ltrim($media_dom -> getAttribute('src'), '/');
			} else {
				$image = '';
			}
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

function yui3_u_1_cards($yui3_u_1_cards) {
	$result = array();
	for ($i=0; $i < count($yui3_u_1_cards); $i++) { 
		$text_dom = $yui3_u_1_cards[$i] -> find('.contain');
		if (count($text_dom) > 0) {
			$name_dom = $text_dom -> firstChild();
			if (count($name_dom) > 0) {
				$name = trim($name_dom -> text());
			} else {
				continue;
			}
			$title_dom = $text_dom -> find('.title');
			if (count($title_dom) > 0) {
				$title = trim($title_dom -> text());
			} else {
				continue;
			}
			$email_dom = $text_dom -> find('a:first-child');
			if (count($email_dom) > 0 && $email_dom -> hasAttribute('href')) {
				$email = trim(str_replace('mailto:', '', $email_dom -> getAttribute('href')));
			} else {
				$email = '';
			}
			$phone_dom = $text_dom -> find('a:nth-child(2)');
			if (count($phone_dom) > 0) {
				$phone = trim($phone_dom -> text());
			} else {
				$phone = '';
			}
		} else {
			continue;
		}
		$media_dom = $yui3_u_1_cards[$i] -> find('img');
		if (count($media_dom) > 0) {
			if ($media_dom -> hasAttribute('src')) {
				$image = 'https://'.parse_url($GLOBALS['url'])['host'].'/'.ltrim($media_dom -> getAttribute('src'), '/');
			}
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

function ddc_MS_staffs($ddc_MS_staffs) {
	$result = array();
	for ($i=0; $i < count($ddc_MS_staffs); $i++) { 
		$text_dom = $ddc_MS_staffs[$i] -> find('section');
		if (count($text_dom) > 0) {
			$name_dom = $text_dom -> find('h3');
			if (count($name_dom) > 0) {
				$name = trim($name_dom -> text());
			} else {
				continue;
			}
			$title_dom = $text_dom -> find('h4');
			if (count($title_dom) > 0) {
				$title = trim($title_dom -> text());
			} else {
				continue;
			}
		} else {
			continue;
		}
		$media_dom = $ddc_MS_staffs[$i] -> find('img');
		if (count($media_dom) > 0) {
			if ($media_dom -> hasAttribute('data-src')) {
				$image = 'https:' . $media_dom -> getAttribute('data-src');
			} elseif ($media_dom -> hasAttribute('src')) {
				$image = 'https:' . $media_dom -> getAttribute('src');
			} else {
				$image = '';
			}
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

function archive_posts_staffs($archive_posts_staffs) {
	print('sd');
	$result = array();
	for ($i=0; $i < count($archive_posts_staffs); $i++) { 
		$text_dom = $archive_posts_staffs[$i] -> find('.staff-archive-info-wrap');
		if (count($text_dom) > 0) {
			$name_dom = $text_dom -> find('.staff-archive-name');
			if (count($name_dom) > 0) {
				$name = trim($name_dom -> text());
			} else {
				$name = '';
			}
			$title_dom = $text_dom -> find('.staff-archive-title');
			if (count($title_dom) > 0) {
				$title = trim($title_dom -> text());
			} else {
				$title = '';
			}
			$email_dom = $text_dom -> find('.staff-archive-email');
			if (count($email_dom) > 0) {
				$email = trim($email_dom -> text());
			} else {
				$email = '';
			}
			$phone_dom = $text_dom -> find('.staff-archive-phone a');
			if (count($phone_dom) > 0 && $phone_dom -> hasAttribute('href')) {
				$phone = trim(str_replace('tel:', '', $phone_dom -> getAttribute('href')));
			} else {
				$phone = '';
			}
		}
		$media_dom = $archive_posts_staffs[$i] -> find('.staff-archive-image-wrap');
		if (count($media_dom) > 0) {
			$image_dom = $media_dom -> find('img');
			if (count($image_dom) > 0) {
				if ($image_dom -> hasAttribute('data-src')) {
					$image = trim($image_dom -> getAttribute('data-src'));
					if (!strpos($image, 'https')) {
						$image = 'https:' . parse_url($GLOBALS['url'])['host'] . $image;
					}
				} elseif ($image_dom -> hasAttribute('src')) {
					$image = trim($image_dom -> getAttribute('src'));
					if (!strpos($image, 'https')) {
						$image = 'https:' . parse_url($GLOBALS['url'])['host'] . $image;
					}
				} else {
					$image = '';
				}
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
			'email' => $email,
			'image' => $image
		);
		if (array_filter($line) && !in_array($line, $result)) {
			array_push($result, json_encode($line));
		}
	}
	return $result;
}

function grid_infos($grid_infos) {
	$result = array();
	for ($i=0; $i < count($grid_infos); $i++) { 
		$name_dom = $grid_infos[$i] -> find('.name .headline-3');
		if (count($name_dom) > 0) {
			$name = trim($name_dom -> text());
		} else {
			$name = '';
		}
		$title_dom = $grid_infos[$i] -> find('.title .headline-4');
		if (count($title_dom) > 0) {
			$title = trim($title_dom -> text());
		} else {
			$title = '';
		}
		$phone_dom = $grid_infos[$i] -> find('.phone-num');
		if (count($phone_dom) > 0 && $phone_dom -> hasAttribute('href')) {
			$phone = trim(str_replace('tel:', '', $phone_dom -> getAttribute('href')));
		} else {
			$phone = '';
		}
		$email_dom = $grid_infos[$i] -> find('.email');
		if (count($email_dom) > 0 && $email_dom -> hasAttribute('href')) {
			$email = trim(str_replace('mailto:', '', $email_dom -> getAttribute('href')));
		} else {
			$email = '';
		}
		$image_dom = $grid_infos[$i] -> find('img');
		if (count($image_dom) > 0) {
			if ($image_dom -> hasAttribute('data-src')) {
				$image = 'https://' . parse_url($GLOBALS['url'])['host'] . $image_dom -> getAttribute('data-src');
			} elseif ($image_dom -> hasAttribute('src')) {
				$image = 'https://' . parse_url($GLOBALS['url'])['host'] . $image_dom -> getAttribute('src');
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
			'email' => $email,
			'image' => $image
		);
		if (array_filter($line) && !in_array($line, $result)) {
			array_push($result, json_encode($line));
		}
	}
	return $result;
}

function StaffMemberBoxs($StaffMemberBoxs) {
	$result = array();
	for ($i=0; $i < count($StaffMemberBoxs); $i++) { 
		$name_dom = $StaffMemberBoxs[$i] -> find('font');
		if (count($name_dom) > 0) {
			$name = trim($name_dom -> text());
		} else {
			$name = '';
		}
		$title_dom = $StaffMemberBoxs[$i] -> find('p');
		if (count($title_dom) > 0) {
			$title = trim($title_dom -> text());
		} else {
			$title = '';
		}
		$image_dom = $StaffMemberBoxs[$i] -> find('img');
		if (count($image_dom) > 0) {
			if ($image_dom -> hasAttribute('data-src')) {
				$image = trim($image_dom -> getAttribute('data-src'));
			} elseif ($image_dom -> hasAttribute('src')) {
				$image = trim($image_dom -> getAttribute('src'));
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

function team_members($team_members) {
	function get_string_between($string, $start, $end){
	    $string = ' ' . $string;
	    $ini = strpos($string, $start);
	    if ($ini == 0) return '';
	    $ini += strlen($start);
	    $len = strpos($string, $end, $ini) - $ini;
	    return substr($string, $ini, $len);
	}
	$result = array();
	for ($i=0; $i < count($team_members); $i++) { 
		$team_meta = $team_members[$i] -> find('.team-meta');
		if (count($team_meta) > 0) {
			$name_dom = $team_meta -> find('h3');
			if (count($name_dom) > 0) {
				$name = trim($name_dom -> text());
			} else {
				$name = '';
			}
			$title_dom = $team_meta -> find('p');
			if (count($title_dom) > 0) {
				$title = trim($title_dom -> text());
			} else {
				$title = '';
			}
			$modal_id = $team_members[$i] -> find('a');	
			if (count($modal_id) > 0 && $modal_id -> hasAttribute('href')) {
				$id = $modal_id -> getAttribute('href');
				$modal_dom = $GLOBALS['dom']-> find($id);
				if (count($modal_dom) > 0) {
					$phone_dom = $modal_dom -> find('.phone1 a');
					if (count($phone_dom) > 0) {
						$phone = trim($phone_dom -> text());
					} else {
						$phone = '';
					}
					$email_dom = $modal_dom -> find('.email a');
					if (count($email_dom) > 0 && $email_dom -> hasAttribute('href')) {
						$email = trim(str_replace('mailto:', '', $email_dom -> getAttribute('href')));
					} else {
						$email = '';
					}
				} else {
					$phone = $email = '';
				}
			} else {
				$phone = $email = '';
			}
		}
		$image_dom = $team_members[$i] -> find('.team-member-image');
		if (count($image_dom) > 0 && $image_dom -> hasAttribute('style')) {
			$image = 'https://' . parse_url($GLOBALS['url'])['host'] . get_string_between($image_dom -> getAttribute('style'), "background-image: url('", "');");
		} else {
			$image = '';
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

function dom_parse(){
	$dom = $GLOBALS['dom'];
	$content_doms = $dom -> find('[template^="employeeTitle"]');
	$staff_info_items = $dom -> find('li[class*=staff-info__item]');
	$staff_items = $dom -> find('li[class*=staff-item]');
	$uabb_wraps = $dom -> find('[class*=uabb-team-member-wrap]');
	$box_containers = $dom -> find('#tabs-mtt .member-list [class*=box-container]');
	$yui3_u_1_6_vcards = $dom -> find('#staffList .yui3-u-1-6 .vcard');
	$isDisplayable_contents = $dom -> find('.deck section[if^=isDisplayable] .content');
	$isDisplayable_video = $dom -> find('.deck section[if^=isDisplayable] .content .video-youTube');
	$tabDisplay_cards = $dom -> find('#tabDisplay .staff-card');
	$listing_employee_items = $dom -> find('.listing-employee__department-employee-item');
	$listing_alpha_items = $dom -> find('.listing-employee-alpha__department-employee-item');
	$salesman_maincards = $dom -> find('#salesperson-connect .salesman.maincard');
	$wpstaff_persons = $dom -> find('.fusion-column-wrapper .wpstaff-person');
	$contentGrid_staffListWrapper_staff_items = $dom -> find('.contentGrid .staffListWrapper .staffItem');
	$yui3_u_1_cards = $dom -> find('.yui3-u-1 .inner .colum > .card');
	$ddc_MS_staffs = $dom -> find('.ddc-span12 .content .MS-staff');
	$archive_posts_staffs = $dom -> find('.archive-posts-wrap .staff-member');
	$grid_infos = $dom -> find('.meet-the-team #tabs-mtt .mtt-tab .box .grid-x.info');
	$StaffMemberBoxs = $dom -> find('.ddc-span12 .tab-content section.StaffMemberBox');
	$team_members = $dom -> find('#pageContent .team-member-overlay');
	if (count($content_doms) > 0) {
		if (count($dom -> find('div[class=content]')) > 0) {
			$output = contents($dom -> find('div[class=content]'));
		}
	}
	elseif (count($team_members) > 0) {
		$output = team_members($team_members);
	}
	elseif (count($StaffMemberBoxs) > 0) {
		$output = StaffMemberBoxs($StaffMemberBoxs);
	}
	elseif (count($grid_infos) > 0) {
		$output = grid_infos($grid_infos);
	}
	elseif (count($archive_posts_staffs) > 0) {
		$output = archive_posts_staffs($archive_posts_staffs);
	}
	elseif (count($ddc_MS_staffs) > 0 ){
		$output = ddc_MS_staffs($ddc_MS_staffs);
	}
	elseif (count($yui3_u_1_cards) > 0) {
		$output = yui3_u_1_cards($yui3_u_1_cards);
	}
	elseif (count($contentGrid_staffListWrapper_staff_items) > 0) {
		$output = contentGrid_staffListWrapper_staff_items($contentGrid_staffListWrapper_staff_items);
	}
	elseif (count($yui3_u_1_6_vcards) > 0) {
		$output = yui3_u_1_6_vcards($yui3_u_1_6_vcards);
	}
	elseif (count($wpstaff_persons)) {
		$output = wpstaff_persons($wpstaff_persons);
	}
	elseif (count($salesman_maincards) > 0) {
		$output = salesman_maincards($salesman_maincards);
	}
	elseif (count($listing_alpha_items) > 0) {
		$output = listing_alpha_items($listing_alpha_items);
	}
	elseif (count($listing_employee_items) > 0) {
		$output = listing_employee_items($listing_employee_items);
	} elseif (count($staff_info_items) > 0) {
		$output = staff_info_items($staff_info_items);
	} elseif (count($uabb_wraps) > 0) {
		$output = uabb_wraps($uabb_wraps);
	} elseif (count($staff_items) > 0) {
		$output = staff_items($staff_items);
	} elseif (count($box_containers) > 0) {
		$output = box_containers($box_containers);
	} elseif (count($isDisplayable_video) > 0) {
		$isDisplayable_video_contents = $dom -> find('.deck section[if^=isDisplayable] .content');
		$output = isDisplayable_video_contents($isDisplayable_video_contents);
	}
	 elseif (count($isDisplayable_contents) > 0) {
		$output = isDisplayable_contents($isDisplayable_contents);
	} elseif ($tabDisplay_cards) {
		$output = tabDisplay_cards($GLOBALS['url'], $tabDisplay_cards);
	}

	echo "<pre>";
	print_r($output);
	echo "</pre>";
}


$dom = new Dom;
$response = send_request($url);
$dom->load($response);
$r = dom_parse();

?>