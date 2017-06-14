<?php

namespace src\controllers;

use lib\FormFactory;
use lib\Request;
use lib\BasicController;
use src\data\BlogPost;
use src\forms\FrontFormFactory;
use src\handlers\MailHandler;

class FrontController extends BasicController {

	public static $pages = [
		'home',
        'blog',
        'blogPost',
        'manageBlogPost'
	];

	public function homeAction() {

		$params = $this->getParams();
		$template_params = [];

		$ContactForm = FrontFormFactory::createContactForm();

        if (Request::getMethod() == "post") {
            $ContactForm->bind($params);
            if ($ContactForm->isValid()) {
                $result = MailHandler::sendContactMail($ContactForm->get('name'), $ContactForm->get('email'), $ContactForm->get('message'));

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

        $page = "manageBlogPost";

        return $this->formStepAction(FrontFormFactory::createBlogPostForm($BlogPost, $page), $page, $page, [], $template_params, function($Form, &$next_params) use ($BlogPost, $mode) {
            if ($mode == "edit") {
                $BlogPost->set("last_modification_date", time());
                $BlogPost->save();
            }
            $next_params["id"] = $BlogPost->get("id");
        });
    }
}