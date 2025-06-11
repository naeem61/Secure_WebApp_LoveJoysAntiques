<?php
//prevents against clickjacking
header("X-Frame-Options: SAMEORIGIN");

//restricts the referrer information to same origin requests
header("Referrer-Policy: same-origin");


header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");


//sets CORS headers for api requests
header("Access-Control-Allow-Origin: http://localhost"); 
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

?>
