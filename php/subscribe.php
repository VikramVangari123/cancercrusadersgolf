<?php
  $apiKey         = '7ab076f72e6314818d714550ba6918ff-us7'; // Edit me
  $listId         = 'e4c2f5d455'; // Edit me
  $double_optin   = true;
  $send_welcome   = true;
  $email_type     = 'html';
  $email          = $_POST['email'];

  // Replace us7 with your datacentre, usually found at end of api key
  $submit_url     = "http://us7.api.mailchimp.com/1.3/?method=listSubscribe";

  $data = array(
      'email_address'=>$email,
      'apikey'=>$apiKey,
      'id' => $listId,
      'double_optin' => $double_optin,
      'send_welcome' => $send_welcome,
      'email_type' => $email_type
  );
  $payload = json_encode($data);
   
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $submit_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($payload));
   
  $result = curl_exec($ch);
  curl_close ($ch);
  $data = json_decode($result);

  if ($data->error){
    echo $data->error;
  } else {
    echo "Thanks, confirmation on its way!";
  }
?>