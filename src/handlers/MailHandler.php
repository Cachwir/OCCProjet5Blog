<?php

namespace src\handlers;

use lib\App;
use lib\Config;
use lib\Singleton;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

/**
 * Class MailHandler
 *
 * This class sends mails using SwiftMailer
 */
class MailHandler {

    use Singleton;

    protected $default_from;
    protected $default_to;
    protected $host;
    protected $port;
    protected $encryption;
    protected $username;
    protected $password;

    public function __construct()
    {
        $Config = Config::getLocaleConfig();
        $mailer_config =  $Config['mailing']['contact_mail'];

        foreach ($mailer_config as $type => $value) {
            if (in_array($type, ["from", "to"])) {
                $value = "default_" . $value;
            }
            $this->$type = $value;
        }
    }

    public function send($subject, $message, $from = null, $to = null)
    {
        $from = $from === null ? array($this->default_from) : $from;
        $to = $to === null ? array($this->default_to) : $to;

        $Transport = new Swift_SmtpTransport($this->host, $this->port, $this->encryption);
        $Transport->setUsername($this->username)
            ->setSourceIp('0.0.0.0')
            ->setPassword($this->password)
        ;

        $Mailer = new Swift_Mailer($Transport);

        $Message = new Swift_Message($subject);
        $Message->setFrom($from)
            ->setTo($to)
            ->setBody($message)
        ;

        return $Mailer->send($Message);
    }
}