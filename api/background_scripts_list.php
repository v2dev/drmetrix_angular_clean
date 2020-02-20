<?php
// shell_exec('nohup php -f /www/html/drmetrix/crons/zoho_sync/get_email_for_zoho_account_discrepancy.php > /dev/null &');
// echo 'i m completed';

define('PHP_COMMAND', 'nohup php -f ');

$array = array(
    array('name' => 'ZOHO Account discrepancy', 'path' => '/www/html/drmetrix/crons/zoho_sync/get_email_for_zoho_account_discrepancy.php', 'min_count' => '', 'current_count' => ''),
    array('name' => 'ZOHO Contact discrepancy', 'path' => '/www/html/drmetrix/crons/zoho_sync/get_email_for_zoho_contact_discrepancy.php', 'min_count' => '', 'current_count' => ''),
    array('name' => 'Advertiser Agency details', 'path' => '/www/html/drmetrix/api/zoho_adv_agency_data.php', 'min_count' => '', 'current_count' => ''),
    array('name' => 'New Advertiser Addition', 'path' => '/www/html/drmetrix/api/script_add_new_adv_zoho.php', 'min_count' => '', 'current_count' => ''),
    array('name' => 'Export Cron', 'path' => '/www/html/drmetrix/api/export_cron.php', 'min_count' => '', 'current_count' => ''),
    array('name' => 'Advertiser Lifetime Details', 'path' => '/www/html/drmetrix/api/cron_advertiser_lifetime_detail.php', 'min_count' => '', 'current_count' => ''),
    array('name' => 'Create Zoho Advertiser details Dump', 'path' => '/www/html/drmetrix/create_dumps/zoho_account_details.php', 'min_count' => '', 'current_count' => ''),
    array('name' => 'Create Zoho Contact details Dump', 'path' => '/www/html/drmetrix/create_dumps/zoho_contact_details.php', 'min_count' => '', 'current_count' => ''),
    array('name' => 'Sync contacts from ADS to ZOHO', 'path' => '/www/html/drmetrix/sync_scripts/adsphere_to_zoho_contacts.php', 'min_count' => '', 'current_count' => ''),
    // array('cron_file_expiry'),
);

echo "<br/><table border='1'><tr><td>Path</td><td>Name</td></tr>";
foreach ($array as $key => $value) {
    echo "<tr>";
    $command = PHP_COMMAND . $value["path"] . '  > /dev/null &';
    // echo "<td><a href='javascript:void(0);' onclick=\"open_close_window('background_scripts.php?command_NMMAMSDNSD=" . base64_encode($command) . "')\">Execute</a></td>";
    echo "<td><a href='background_scripts.php?command_NMMAMSDNSD=" . base64_encode($command) . "'>Execute</a></td>";    
    echo "<td>{$value['name']}</td>";
    echo "</tr>";
}
echo "</table><br/>";

?>

<script>
function open_close_window(url) {
    var newWindow = window.open(url, 'TheNewpop', 'toolbar=1,location=1,directories=1,status=1,menubar=1,scrollbars=1,resizable=1');
    newWindow.blur();
    alert("Executed");
    newWindow.close();
}
</script>
