<?php
use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    private PHPMailer $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer();
        $this->mail->IsSMTP();
        $this->mail->SMTPAuth = true;
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Host = DI::env('MAIL_HOST');
        $this->mail->Username = DI::env('MAIL_USER');
        $this->mail->Password = DI::env('MAIL_PASS');
        $this->mail->isHTML(true);
        $this->mail->Port = DI::env('MAIL_PORT');
        $this->mail->setFrom(DI::env('MAIL_REPLY'));
        $this->mail->addReplyTo(DI::env('MAIL_REPLY'), DI::env('APP_NAME'));
    }

    public function send($to, $subject, $body)
    {
        try {
            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            if (!$this->mail->send()) {
                throw new Exception;
            }
        } catch (Exception $e) {
            DI::logger()->log('Could not send email', [$this->mail->ErrorInfo], LOGGERS::email, LEVELS::error);
            return false;
        }
    }
}
