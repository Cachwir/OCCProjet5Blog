# OCCProjet5Blog

Projet n°5 du parcours Développeur PHP/Symfony : Création d'un blog en PHP

Version 1.0

Author: Cachwir

### how to install

- Pull the project (git clone https://github.com/Cachwir/OCCProjet5Blog.git)
- you can rename the folder or leave it as it is.
- cd OCCProject5Blog or whatever you named it
- run composer install to install the dependancies
- run chmod -R 777 var/cache for Twig cache to be store
- cd src/config
- cp config.php.dist config.php
- vim config.php (or whatever text editor you like)
- edit the config file by entering your database info, the contact mail info (put perform_shoot to false if you don't want the mailer to be used) and choose if you want the debug mode to be enabled or not (might be useful to leave it at true until you're sure the site runs nicely)
- create your database and use the db.sql dump file or the schema.mwb to create the blog_posts table and add a default post to it
- configure your virtual server if you need it. It needs to point to the web folder at the root of the project.
- enjoy~