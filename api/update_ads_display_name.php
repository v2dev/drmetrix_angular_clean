<?php
// zoho workflow - UPDATE DISPLAY NAME AND ALIASES
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/functions.php';
ignore_user_abort();
$_account_details = $_REQUEST;
updateAdvDisplayName($_account_details);
function updateAdvDisplayName($_account_details)
{
    //file_put_contents('/www/html/api_response/account_details.txt', print_r($_account_details,true));
    if (isset($_account_details['DISPLAYNAME']) && !empty($_account_details['DISPLAYNAME']) && isset($_account_details['ADVID']) && !empty($_account_details['ADVID'])) {
        $db = getConnection();
        $aliases = 'NULL';
        if (!empty(trim($_account_details['ALIASES']))) {
            $aliases = "'" . addslashes(trim($_account_details['ALIASES'])) . "'";
        }
        $need_help = 0;
        if ($_account_details['NEEDHELP'] == 'true' || $_account_details['NEEDHELP'] == '1') {
            $need_help = 1;
        }

        $sql = "UPDATE advertiser SET display_name = '" . addslashes($_account_details['DISPLAYNAME']) . "', alt_adv_names = " . $aliases . ", need_help = '" . $need_help . "'  WHERE adv_id='" . $_account_details['ADVID'] . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        // file_put_contents('/www/html/api_response/account_details.txt', 'date - ' .  date('Y-m-d H:i:s') . $sql);
        closeConnection();
    }
}
