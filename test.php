<?php
$to      = 'schmidtg.w1@gmail.com';
$subject = 'the subject';
$message = 'hello';
$headers = 'From: info@schmidtg.com' . "\r\n" .
        'Reply-To: info@schmidtg.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
?>
