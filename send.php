<?php
if ($_POST) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    $to = "mm.saeidi@gmail.com"; // ایمیل مدرسه
    $subject = "پیام جدید از سایت توانش";
    $body = "نام: $name\nایمیل: $email\nپیام: $message";
    mail($to, $subject, $body);
    echo "پیام با موفقیت ارسال شد!";
}
?>