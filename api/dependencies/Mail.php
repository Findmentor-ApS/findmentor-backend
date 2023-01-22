<?php
use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    private PHPMailer $mail;

    public function __construct()
    {
        $MAILER = DI::env('MAILER');

        $this->mail = new PHPMailer();
        $this->mail->IsSMTP();
        $this->mail->SMTPAuth = true;
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Host = $MAILER['HOST'];
        $this->mail->Username = $MAILER['USER'];
        $this->mail->Password = $MAILER['PASS'];
        $this->mail->isHTML(true);
        $this->mail->Port = $MAILER['PORT'];
        $this->mail->setFrom($MAILER['MAIL']);
        $this->mail->addReplyTo($MAILER['MAIL'], DI::env('APP'));
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
