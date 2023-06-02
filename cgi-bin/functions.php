<?php

function sendEmail ($subject, $mess, $email, $name)
{
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/plain; charset=utf-8";
    $headers[] = "To: $name <$email>";
    $headers[] = "From: The Writers Arbor <admin@thewritersarbor.com>";
    mail($email, $subject, $mess, implode("\r\n", $headers));
}

function sendPWResetEmail ($toId, $name, $email, $salt)
{
    $link = hash('sha512', ($salt . $name . $email), FALSE);
    $mess = "$name,\n\n
        There has been a request on The Writes Arbor website for a password reset for this account.  If you initiated this request, click the link below, and you will be sent to a page where you will be able enter a new password. If you did not initiate this password reset request, simple ignore this email, and your password will not be changed.\n\n
        https://thewritersarbor.com/index.php?page=forgotpwd&id=$toId&ver=$link\n\n
        Thank you,\nAdmin\nThe Writers Arbor";
    $message = wordwrap($mess, 70);
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= 'Content-Type: text/plain; charset=utf-8' . "\r\n";
    $headers .= "From: The Writers Arbor Admin <admin@thewritersarbor.com>" .
            "\r\n";
    mail($email, 'The Writers Arbor website password reset request', $message,
            $headers);
}

function showDate ($t)
{
    return date("Y-m-d", $t);
}