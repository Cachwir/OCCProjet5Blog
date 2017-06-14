<?php

namespace src\forms;

use lib\FormFactory;
use src\data\BlogPost;

class FrontFormFactory extends FormFactory
{
    public static function createContactForm()
    {
        $ContactForm = self::createForm([], 'login');
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

        return $ContactForm;
    }

    public static function createBlogPostForm(BlogPost $BlogPost, $page)
    {
        return self::createGenericForm(
            $BlogPost,
            [
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
                }]
            ],
            ['class' => $page],
            null
        );
    }
} 