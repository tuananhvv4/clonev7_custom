<?php

function generateSecretKey_Google2FA()
{
    $google2fa = new PragmaRX\Google2FAQRCode\Google2FA();
    return $google2fa->generateSecretKey();
}

?>