<?php

use Illuminate\Support\Facades\URL;

$token = base64_encode("{$validationToken}:{$emailAddress}");

$activationLink = URL::to('/users/activate');
$activationLink .= '?t='.urlencode($token);

?>

Please click <a href="{{ $activationLink }}">here</a> to verify your email
