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
		return send_request(url, method, payload);
	}
		
}

function dom_parse($request_url){
	$dom = new Dom;
	$response = send_request($request_url);
	$dom->load($response);
	$content_doms = $dom -> find('div[class=content]');
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
			$mail_dom = $content_doms[$i] -> find('a[href*=mailto:]', 0);
			if ($mail_dom && $mail_dom -> hasAttribute('href')) {
				$mail = str_replace('mailto:', '', $mail_dom -> getAttribute('href'));
			} else {
				$mail = '';
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
				'email' => $mail,
				'image' => $image
			);
			array_push($result, json_encode($line));
		}
	}
	echo "<pre>";
	print_r($result);
	echo "</pre>";
}

dom_parse($url);
?>