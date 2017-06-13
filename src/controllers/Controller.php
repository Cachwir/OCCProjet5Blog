<?php

namespace src\controllers;

use lib\FormFactory;
use lib\Request;
use lib\BasicController;
use src\data\BlogPost;
use src\handlers\MailHandler;

class Controller extends BasicController {

	public static $pages = [
		'home',
        'blog',
        'blogPost',
        'manageBlogPost'
	];

	public function homeAction() {

		$params = $this->getParams();
		$template_params = [];

        $ContactForm = FormFactory::createForm([], 'login');
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

        if (Request::getMethod() == "post") {
            $ContactForm->bind($params);
            if ($ContactForm->isValid()) {

                $MailHandler = MailHandler::getInstance();

                $data = [];
                $data['Date'] = date("d/m/Y à H:i:s");
                $data['Nom'] = $params['name'];
                $data['Email'] = $params['email'];
                $data['Message'] = $params['message'];

                $message = "";
                foreach($data as $key => $value){
                    $message .= $key . " : " . htmlspecialchars($value) . "\r\n";
                }

                $subject = "Site d'Antoine Bernay : un message à votre attention";


                $result = $MailHandler->send($subject, $message);

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

    public function blogAction() {

        $params = $this->getParams();
        $template_params = [];

        $template_params["BlogPosts"] = BlogPost::findFromLast();

        return $this->render('blog', $template_params);
    }

    public function blogPostAction() {

        $params = $this->getParams();
        $template_params = [];

        $id = Request::get("id");
        $BlogPost = BlogPost::findById($id);

        if (!$BlogPost instanceof BlogPost) {
            $this->redirectToPage("blog");
        }

        $template_params["BlogPost"] = $BlogPost;

        return $this->render('blogPost', $template_params);
    }

    public function manageBlogPostAction() {

        $params = $this->getParams();
        $template_params = [];

        $id = Request::get("id");

        if ($id !== null) {
            $BlogPost = BlogPost::findById($id);
            if (!$BlogPost instanceof BlogPost) {
                $this->redirectToPage("blog");
            }
            $mode = "edit";
            $template_params["title"] = "Editer un post";
        } else {
            $BlogPost = new BlogPost();
            $BlogPost->set("publication_date", time());
            $mode = "new";
            $template_params["title"] = "Nouveau post";
        }

        $template_params["BlogPost"] = $BlogPost;

        return $this->formStepAction($BlogPost, 'manageBlogPost', 'manageBlogPost', [], [
            ['author', 's', function ($v) {
                if (empty($v)) return "Ce champ est requis";
                if (strlen($v) > 255) return "Ce champ ne peut contenir plus de 255 caractères";
            }],
            ['title', 's', function ($v) {
                if (empty($v)) return "Ce champ est requis";
                if (strlen($v) > 255) return "Ce champ ne peut contenir plus de 255 caractères";
            }],
            ['introduction', 's', function ($v) {
                if (empty($v)) return "Ce champ est requis";
            }],
            ['content', 's', function ($v) {
                if (empty($v)) return "Ce champ est requis";
            }],
        ], $template_params, null, function($Form, &$next_params) use ($BlogPost, $mode) {
            if ($mode == "edit") {
                $BlogPost->set("last_modification_date", time());
                $BlogPost->save();
            }
            $next_params["id"] = $BlogPost->get("id");
        });
    }
}