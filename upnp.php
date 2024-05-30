<?php

$ip_address = '192.168.188.230';
$command = 'firmware_reboot';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$ip_address:1400/xml/device_description.xml");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "<u:action xmlns:u='urn:schemas-upnp-org:service:System:1'><u:serviceStateTable><stateVariable sendEvents='no'><name>CurrentFWVersion</name><dataType>string</dataType></stateVariable><stateVariable sendEvents='no'><name>TargetFWVersion</name><dataType>string</dataType></stateVariable><stateVariable sendEvents='no'><name>UpgradeFWState</name><dataType>ui2</dataVariable></stateVariable><stateVariable sendEvents='yes'><name>LastUpdateAttemptTime</name><dataType>dateTime.iso8601</dataType></stateVariable><stateVariable sendEvents='yes'><name>LastUpgradeSuccessTime</name><dataType>dateTime.iso8601</dataType></stateVariable><stateVariable sendEvents='yes'><name>LastUpgradeFailureTime</name><dataType>dateTime.iso8601</dataType></stateVariable><stateVariable sendEvents='yes'><name>LastUpgradeFailureReason</name><dataType>string</dataType></stateVariable><stateVariable sendEvents='yes'><name>UpgradeFWStateString</name><dataType>string</dataType></stateVariable></u:serviceStateTable></u:action>");
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
$output = curl_exec($ch);

if ($output === false) {
    echo 'Curl error: ' . curl_error($ch);
    curl_close($ch);
    exit;
}

curl_close($ch);

var_dump($output);

$xml = simplexml_load_string($output);

if ($xml === false) {
    echo "Failed to parse XML: ";
    foreach (libxml_get_errors() as $error) {
        echo "<br>", $error->message;
    }
    exit;
}

if (isset($xml->upgradeFWStateString) && $xml->upgradeFWStateString == 'Rebooting') {
    echo "The Sonos speaker is being rebooted.";
} else {
    echo "The reboot command was not successful.";
}
