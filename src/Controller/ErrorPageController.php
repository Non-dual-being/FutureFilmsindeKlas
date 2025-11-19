<?php
$errorCode = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT) ?: 500;
$defaultMsg = 'In the future there may be no more mistakes';
$errorMessage = filter_input(INPUT_GET, 'message', FILTER_UNSAFE_RAW) ?: $defaultMsg;
http_response_code($errorCode);
?>