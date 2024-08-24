<?php
/*
Firstly let me put a Yeshou Xianbei here😋
    　　 　  ▃▆█▇▄▖
　　 　▟◤▖　　　◥█▎
　　◢◤　 ▐　　　　▐▉
　▗◤　　　▂　▗▖　▕█▎
　◤　▗▅▖◥▄　▀◣　　█▊
▐　▕▎◥▖◣◤　　　　◢██
█◣　◥▅█▀　　　　▐██◤
▐█▙▂　　　      ◢██◤
　◥██◣　　　◢▄◤
　　▀██▅▇▀
*/
// ———————————— Configuration ————————————
// Path of the data file. It should be readable and writable. I think you understand that.😊
define('DATA_FILE_PATH', __DIR__.'/data.json');
// Have you enabled CloudFlare's proxy? Tell me here.🧐
define('CF_PROXY_ENABLED', false);
// ————————————— Source code —————————————
$response_data = array(
  'successful' => false,
  'message' => 'Unknown error',
  'count' => -114514
);
if (php_sapi_name() == 'cli') {
  die('Error: You should not run this program in command line. Run it in a web server please.'.PHP_EOL);
}
header('Content-Type: text/json');
$data_file_father_path = dirname(DATA_FILE_PATH);
$counter_data = array(
  'version' => 1,
  'user' => array(),
  'ip' => array(),
  'count' => array()
);
if (!file_exists($data_file_father_path)) {
  if (!@mkdir($data_file_father_path, 0777, true)) {
    $response_data['message'] = 'Error: Unable to create the father directory of data file.';
    goto output;
  }
}
if (!is_readable($data_file_father_path)||!is_writable($data_file_father_path)) {
  $response_data['message'] = 'Error: The father directory of data file is not readable or writable.';
  goto output;
}
if (!file_exists(DATA_FILE_PATH)) {
  if (!@file_put_contents(DATA_FILE_PATH, json_encode($counter_data))) {
    $response_data['message'] = 'Error: Unable to write empty data to the data file.';
    goto output;
  }
  goto addCount;
}
if (!is_readable(DATA_FILE_PATH) || !is_writable(DATA_FILE_PATH)) {
  $response_data['message'] = 'Error: The data file is not readable or writable.';
  goto output;
}
$counter_data = json_decode(file_get_contents(DATA_FILE_PATH), true);
addCount:
  //print_r($counter_data);
  $ip = $_SERVER['REMOTE_ADDR'];
  if (CF_PROXY_ENABLED) {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
  }
  if (!array_key_exists('user',$_GET)) {
    $response_data['message'] = 'Error: The argument "user" is required.';
    goto output;
  }
  $user = $_GET['user'];
  if (empty($user)) {
    $response_data['message'] = 'Error: The argument "user" should not be empty.';
    goto output;
  }
  if (!preg_match('/^[a-zA-Z0-9_]+$/', $user) || substr($user, -1) === '_' || strpos($user, '_') === 0) {
    $response_data['message'] = 'Error: The username is invalid. It should only contain numbers, letters and underline. And it should not start or end with a underline.';
    goto output;
  }
  if (!in_array($user, $counter_data['user'])) {
    array_push($counter_data['user'], $user);
    array_push($counter_data['count'], 0);
  }
  $count=$counter_data['count'][array_search($user, $counter_data['user'])];
  if (!in_array($ip, $counter_data['ip'])) {
    $count++;
  } else {
    goto updateData;
  }
  array_push($counter_data['ip'], $ip);
  updateData:
  $response_data['successful'] = true;
  $response_data['message'] = "User {$user} has requested this counter using {$count} unique IP address".(($count >= 2) ? 'es' : '').'.';
  $response_data['count'] = $count;
  $counter_data['count'][array_search($user, $counter_data['user'])] = $count;
  @file_put_contents(DATA_FILE_PATH, json_encode($counter_data));
output:
  echo json_encode($response_data);
?>