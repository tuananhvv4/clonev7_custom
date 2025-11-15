<?php

ini_set("display_errors", 0);
if(!empty($_GET["account"])) {
    $account = explode("|", check_string($_GET["account"]));
    list($username, $password) = $account;
    $imapPath = "{outlook.office365.com:993/imap/ssl/novalidate-cert}";
    $inbox = imap_open($imapPath, $username, $password);
    if(!$inbox) {
        exit("DIE");
    }
    exit("LIVE");
}
exit("Thiếu account");
function check_string($data)
{
    return trim(htmlspecialchars(addslashes($data)));
}

?>