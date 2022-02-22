<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "verticaliq_generators";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

//census bureau api key matt@viq
$viq_cb_api_key = 'd62184e651f42ac47cd87e3b7f4bd8aeef681e13';

//$viq_bls_api_key = '0ab0ca8759e44f8ca57a8bf11a9e726f';
$ba_bls_api_key = 'a1c35ef89c054e6fb341a3f0e92b40b8';

$matteus10_bls_api_key = '70a962e319204bf6ac8bf697ce4b7526';
$bagmail_bls_api_key_alt = 'e15dc533a54a4e18bf813911734990fd';

$agilefy_bls_api_key = 'cd9ef87fb6d249679f9737a7a635d325';
$agilefy_bls_api_key2 = 'a124101be4b742199bbbb98915d2e2c0';
$agilefy_bls_api_key3 = 'cd4864f8eaa2496c8cea47233732a009';

$matticloud_bls_emp_api_key = 'c46f9122983640e6a7e7a29fb8203bed';
//matteus10@icloud.com / c46f9122983640e6a7e7a29fb8203bed
  
$matticloud_bls_ear_api_key = '4354dc1e9263446da5c2c471d387824c';
//matteus10+emp@icloud.com / 4354dc1e9263446da5c2c471d387824c

$mattviqppi_bls_ppi_api_key = '6bc354d20f9a4dff9b2a4bc841cbde24';
//matt+ppi@verticaliq.com / 6bc354d20f9a4dff9b2a4bc841cbde24

//matt+bls@bespokearsenal.com
$key_2021_1 = '4ae63499217e4fba89e6f7de3786e26d';

//matt+bls2@bespokearsenal.com
$key_2021_2 = '36fddf64ef24454d9274851b837b3f3c';

//matt+bls3@bespokearsenal.com
$key_2021_3 = '84817dad47dd4eb0af1ecaa11f787447';

//VIQ
//matt+bls1@verticaliq.com
$bls2021_1 = '9b073036cded4937a648e64f743b906d';

//matt+bls2@verticaliq.com
$bls2021_2 = '3f6c2fe5b8514a9da3450b5c44b79a75';

//matt+bls3@verticaliq.com
$bls2021_3 = '4bc0d4ec7d45421190a921a6ce36fe92';
//matt+bls4@verticaliq.com
$bls2021_4 = 'a7c64c713a314bb68206a865c3843a42';

//matt+bls5@verticaliq.com
$bls2021_5 = 'b38cc4ed2aeb4d909e60bc4d280759ee';
//matt+bls6@verticaliq.com
$bls2021_6 = '3c1abcc77673476f9de7052515ddb29b';

?>