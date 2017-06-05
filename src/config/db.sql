CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `author` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `publication_date` int(11) NOT NULL,
  `last_modification_date` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `author`, `publication_date`, `last_modification_date`, `title`, `introduction`, `content`) VALUES
(1, 'Antoine Bernay', 1496683342, 1496687717, 'Lancement du site', 'C\'est un petit pas pour l\'Homme et pas grand chose pour l\'Humanité mais ça fait quand même plaisir. Déjà que ça me trottait dans la tête depuis un moment, il fallait que je fasse mon propre site perso tôt ou tard.', 'Qui a dit que les cordonniers étaient les plus mal chaussés ? En tout cas, c\'est pareil pour les développeurs.\r\nAprès toutes ces années à développer, intégrer, fignoler des applications en tout genre, je n\'avais toujours pas mon propre site. Je n\'arrêtais pas de repousser, en me disant que ça allait sûrement m\'être demandé lors de ma formation à Openclassrooms.\r\nEh ben BIM ! Dans le mille Lucille !\r\nComme quoi, procrastiner, ça ne mène pas toujours à des mauvaises choses.\r\n...\r\nJe crois que j\'aurais dû peut-être trouver une conclusion plus appropriée à un site professionnel.');

--
-- Index pour les tables exportées
--

--
-- Index pour la table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;