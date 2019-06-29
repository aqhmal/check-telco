<?php
/**
 * Coded by Aqhmal Hafizi
 * 29 June 2019
 *
 * Check phone number telco using HLRLookup.
 * API Documentation: https://www.hlr-lookups.com/en/api-docs
 */

# HLR Lookup Username & Password.
# CHANGE THESE USING YOUR HLRLOOKUP CREDENTIAL.
$username = "";
$password = "";

# Check if username or password is blank.
if($username == "" || $password == "") {
	die("Username or password cannot be blank.\n");
}

#
# 1. Check account credit balance, if balance is insufficient, exit the script.
#

# Initialize cURL session.
$ch = curl_init();
# Array of cURL options.
$options = [
	CURLOPT_URL => "https://www.hlr-lookups.com/api?action=getBalance&username=".$username."&password=".$password,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_SSL_VERIFYPEER => false,
	CURLOPT_SSL_VERIFYHOST => 2,
	CURLOPT_USERAGENT => "Mozilla/5.0 (iPhone; CPU iPhone OS 11_0_3 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko)"
];
# Set cURL options from the array above.
curl_setopt_array($ch, $options);
# Execute the cURL session then convert the result to JSON.
$result = json_decode(curl_exec($ch));
# Close cURL session.
curl_close($ch);
# Convert account balance result from string to float.
$balance = (float)$result->results->balance;
# Check balance, exit if insufficient.
if($balance <= 0) {
	die("Insufficient HLR Lookup credit balance.\n");
}

#
# 2. Get phone number input from user.
#

echo "Enter phone number you want to check (without +60): ";
# Get user input, trim the input and remove first character.
$input = substr(trim(fread(STDIN, 13)), 1);
# Add +60 prefix for Malaysia number.
$phone_no = "+60" . $input;
# Verify the number.
echo "Do you want to check for phone number, " . $phone_no . " ? (y/n): ";
if(strtolower(trim(fread(STDIN, 1))) == "n") {
	die("Script exited.\n");
}

#
# 3. Retrieve phone number information, and check if number is ported out to another telco.
#

# Start another cURL session
$ch = curl_init();
# Array of cURL options.
$options = [
	CURLOPT_URL => "https://www.hlr-lookups.com/api?action=submitSyncLookupRequest&msisdn=".$phone_no."&username=".$username."&password=".$password,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_SSL_VERIFYPEER => false,
	CURLOPT_SSL_VERIFYHOST => 2,
	CURLOPT_USERAGENT => "Mozilla/5.0 (iPhone; CPU iPhone OS 11_0_3 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko)"
];
# Set cURL options from the array above.
curl_setopt_array($ch, $options);
# Execute the cURL session then convert the result to JSON.
$result = json_decode(curl_exec($ch));
# Close cURL session.
curl_close($ch);
# Check if lookup is valid.
if(isset($result->results[0]->isvalid)) {
	if($result->results[0]->isvalid == "No") {
		die("Phone number is not valid\n");
	} else {
		if($result->results[0]->isported == "Yes") {
			$telco = $result->results[0]->portednetworkname;
		} else {
			$telco = $result->results[0]->originalnetworkname;
		}
	}
	echo "Telco : " . $telco;
} else {
	echo "There's a problem connecting to the API.";
}
# Print a newline at the end of the script.
echo "\n";
