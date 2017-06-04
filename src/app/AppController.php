<?php

class AppController extends App {

	public static $pages = [
		'home',
	];

	public function homeAction() {

		$params = $this->params;
		$template_params = [];

        $ContactForm = $this->createForm([], 'login');
        $ContactForm->add('name', 's', function ($v) {
            if (empty($v)) return "Indiquez votre nom/prénom";
        });
        $ContactForm->add('email', 's', function ($v) {
            if (empty($v)) return "Indiquez votre adresse e-mail";
            if (!filter_var($v, FILTER_VALIDATE_EMAIL)) return "Adresse e-mail invalide";
        });
        $ContactForm->add('message', 's', function ($v) {
            if (empty($v)) return "Indiquez votre message";
        });

        if ($this->method == "post") {
            $ContactForm->bind($this->params);
            if ($ContactForm->isValid()) {

                $Config = $this->getConfig();
                $mailer_config =  $Config['mailing']['contact_mail'];

                $data = [];
                $data['Date'] = date("d/m/Y à H:i:s");
                $data['Nom'] = $params['name'];
                $data['Email'] = $params['email'];
                $data['Message'] = $params['message'];

                $body = "";
                foreach($data as $key => $value){
                    $body .= $key . " : " . htmlspecialchars($value) . "\r\n";
                }

                $subject = "Site d'Antoine Bernay : un message à votre attention";

                $transport = new Swift_SmtpTransport($mailer_config['host'], $mailer_config['port'], $mailer_config['encryption']);
                $transport->setUsername($mailer_config['username'])
                    ->setSourceIp('0.0.0.0')
                    ->setPassword($mailer_config['password'])
                ;

                $mailer = new Swift_Mailer($transport);

                $message = new Swift_Message($subject);
                $message->setFrom(array($mailer_config['from']))
                    ->setTo(array($mailer_config['to']))
                    ->setBody($body)
                ;

                $result = $mailer->send($message);

                if ($result) {
                    $ContactForm->addSuccess(null, "Merci de m'avoir contacté. Je reviens vers vous dans les plus brefs délais.");
                } else {
                    $ContactForm->addError(null, "Une erreur est survenue lors de l'envoi du message.");
                }
            }
            $this->method = 'get';
        }

        $template_params['ContactForm'] = $ContactForm;

		return $this->render('home', $template_params);
	}
}