<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="assets/css/main.css" media="screen" />
<?php
require 'bootstrap.php';

$cpanel = new CPANEL();
print $cpanel->header( "Change Primary Domain" );
$accountName = Account::name($cpanel);

require_once "/usr/local/cpanel/php/cpanel.php";

$response = $cpanel->uapi(
    'DomainInfo',
    'list_domains'
);

if ($response['cpanelresult']['result']['status']) {
    $data = $response['cpanelresult']['result']['data'];
    $main_domain = $data['main_domain'];
}
else {
    print to_json($response['cpanelresult']['result']['errors']);
}

// Retrieve the current primary domain name using WHM API1

// Display the form to change the primary domain
echo '<form method="post">';
echo '<div class="form-group">';
echo '<label for="current_primary_domain">Current Primary Domain Name:</label>';
echo '<input type="text" id="current_primary_domain" class="form-control" value="' . $main_domain . '" readonly>';
echo '</div>';
echo '<div class="form-group">';
echo '<label for="new_primary_domain">New Primary Domain Name:</label>';
echo '<input type="text" name="new_primary_domain" id="new_primary_domain" class="form-control">';
echo '</div>';
echo '<button type="submit" class="btn btn-primary">Change Primary Domain</button>';
echo '</form>';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['new_primary_domain'])) {
    // Retrieve the new primary domain name from the form
    $newPrimaryDomain = $_POST['new_primary_domain'];

    // Use the proc_open to run the WHM API1 command to change the primary domain name
    $whmApiCommand = "./wrapper {$accountName} {$_POST['new_primary_domain']}";
    
    echo '<div class="alert alert-info">Primary Domain name is being changed from ' . $main_domain . ' to ' . $newPrimaryDomain . ' please allow a few seconds for the process to complete.</div>';

    $descriptorspec = array(
        0 => array('pipe', 'r'), // stdin
        1 => array('pipe', 'w'), // stdout
        2 => array('pipe', 'w') // stderr
    );
    $process = proc_open($whmApiCommand, $descriptorspec, $pipes);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[0]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    if ($exitCode === 0) {
        echo '<div class="alert alert-success">Your primary domain name has been changed to ' . $newPrimaryDomain . '.</div>';
		echo "<script>document.getElementById('current_primary_domain').value='$newPrimaryDomain';</script>";
    } else {
        echo '<div class="alert alert-danger">Failed to change the primary domain name: ' . $stderr . '</div>';
    }
}

// TODO
// https://developers.whmcs.com/api-reference/updateclientproduct/

echo $cpanel->footer();
$cpanel->end();

