-- PHP Version: 7.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `blog`
--

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `id` int(11) NOT NULL,
  `idPost` int(11) NOT NULL,
  `text` mediumtext NOT NULL,
  `status` enum('created','validated','cancelled','') NOT NULL DEFAULT 'created',
  `idUser` int(11) DEFAULT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `validation_date` datetime DEFAULT NULL,
  `suppression_date` datetime DEFAULT NULL,
  `last_update` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `comment`
--

INSERT INTO `comment` (`id`, `idPost`, `text`, `status`, `idUser`, `created_date`, `validation_date`, `suppression_date`, `last_update`) VALUES
(42, 4, 'interessant', 'cancelled', 1, '2021-05-18 21:21:17', NULL, '2021-05-18 21:27:54', '2021-05-18 21:27:54'),
(43, 12, 'ok!!', 'created', 1, '2021-05-18 21:24:13', NULL, NULL, '2021-05-18 21:24:13'),
(44, 13, 'En effet', 'validated', 1, '2021-05-18 21:27:21', '2021-05-18 21:27:47', NULL, '2021-05-18 21:27:47'),
(45, 13, 'Commentaire test', 'created', 1, '2021-05-18 21:28:22', NULL, NULL, '2021-05-18 21:28:22'),
(46, 13, 'Commentaire test2', 'created', 1, '2021-05-18 21:28:39', NULL, NULL, '2021-05-18 21:28:39'),
(47, 13, 'ok..&amp;', 'cancelled', 2, '2021-05-19 06:54:58', NULL, '2021-05-19 06:55:07', '2021-05-19 06:55:07'),
(48, 13, 'ok..', 'created', 2, '2021-05-19 06:55:24', NULL, NULL, '2021-05-19 06:55:24');

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE `post` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `stand_first` text NOT NULL,
  `text` mediumtext NOT NULL,
  `userId` int(11) NOT NULL,
  `creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `file_attached` blob
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `post`
--

INSERT INTO `post` (`id`, `title`, `stand_first`, `text`, `userId`, `creation_date`, `last_update`, `file_attached`) VALUES
(4, 'Uml', 'Vous souhaitez concevoir un programme qui s\'adapte parfaitement aux besoins en ??volution permanente de vos clients ? Challenge accept??.  C\'est un gros d??fi, mais heureusement, nous avons le Domain-Driven Design pour nous venir en aide.', 'En tant que d??veloppeurs, nous aimons nous plonger dans le code. Nous avons h??te de tester les derni??res technologies et de nous confronter ?? de nouvelles probl??matiques. Je le comprends tout ?? fait. Mais coder sans prendre un peu de hauteur aboutit la plupart du temps ?? des solutions fragiles et difficiles ?? modifier.\r\n\r\nOr, vous savez ce que font les clients en permanence ? Ils changent d\'avis ! Si vous voulez ??viter de vous arracher les cheveux ?? chaque fois que vous devez modifier votre application, il faut pouvoir l\'anticiper.\r\n\r\nDans ce cours, nous allons d??couvrir le DDD, Domain-Driven Design (conception pilot??e par le domaine), une approche compl??te nous permettant de concevoir des logiciels qui r??pondent ?? des probl??matiques complexes.\r\n\r\nDans la premi??re partie du cours, nous d??couvrirons en quoi consiste le DDD. Nous aborderons la collaboration avec les clients, et en particulier comment appr??hender une application selon leur point de vue, en comprenant la logique m??tier. Nous verrons comment cr??er un vocabulaire commun qui nous permettra de transposer la logique m??tier au mieux dans notre code.\r\n\r\nDans la deuxi??me partie, nous ??laborerons ensemble notre premier mod??le de domaine, en utilisant des diagrammes de cas d\'utilisation UML et de classes. Les notions d\'objets entit??s, valeurs et agr??gats seront nos alli??es pour nous accompagner dans notre programmation.\r\n\r\nCroyez-moi,  cette approche vous ??vitera de vous faire des cheveux blancs quand, juste apr??s avoir livr?? une application, le client vous demande : ?? Mais j\'ai oubli?? ! Vous pouvez ??galement lui faire faire ??a ? ??a ne devrait pas ??tre trop compliqu?? ??.', 6, '2021-05-06 16:26:48', '2021-05-18 18:42:43', ''),
(7, 'Le principe d\'encapsulation', 'L\'un des gros avantages de la POO est que l\'on peut masquer le code ?? l\'utilisateur (l\'utilisateur est ici celui qui se servira de la classe, pas celui qui chargera la page depuis son navigateur). ', 'Le principe d\'encapsulation\r\n\r\nL\'un des gros avantages de la POO est que l\'on peut masquer le code ?? l\'utilisateur (l\'utilisateur est ici celui qui se servira de la classe, pas celui qui chargera la page depuis son navigateur). Le concepteur de la classe a englob?? dans celle-ci un code qui peut ??tre assez complexe et il est donc inutile voire dangereux de laisser l\'utilisateur manipuler ces objets sans aucune restriction. Ainsi, il est important d\'interdire ?? l\'utilisateur de modifier directement les attributs d\'un objet. L\'encapsulation garantit ainsi la validit?? des types et des valeurs des donn??es des objets.\r\n\r\nPrenons l\'exemple d\'un avion o?? sont disponibles des centaines de boutons. Chacun de ces boutons constituent des actions que l\'on peut effectuer sur l\'avion. C\'est l\'interface de l\'avion. Le pilote se moque de quoi est compos?? l\'avion : son r??le est de le piloter. Pour cela, il va se servir des boutons afin de manipuler les composants de l\'avion. Le pilote ne doit pas se charger de modifier manuellement ces composants : il pourrait faire de grosses b??tises.\r\n\r\nLe principe est exactement le m??me pour la POO : l\'utilisateur de la classe doit se contenter d\'invoquer les m??thodes en ignorant les attributs. Comme le pilote de l\'avion, il n\'a pas ?? les trifouiller. Pour instaurer une telle contrainte, on dit que les attributs sont priv??s. Pour l\'instant, ceci peut sans doute vous para??tre abstrait, mais nous y reviendrons.', 2, '2021-05-06 20:48:53', '2021-05-18 18:40:57', ''),
(12, 'La base de donn??es', 'Une base de donn??es permet d\'enregistrer des donn??es de fa??on organis??e et hi??rarchis??e. Certes, vous connaissez les variables, mais celles-ci restent en m??moire seulement le temps de la g??n??ration de la page. Vous avez aussi appris ?? ??crire dans des fichiers, mais cela devient vite tr??s compliqu??, d??s que vous avez beaucoup de donn??es ?? enregistrer.', 'Une base de donn??es permet d\'enregistrer des donn??es de fa??on organis??e et hi??rarchis??e. Certes, vous connaissez les variables, mais celles-ci restent en m??moire seulement le temps de la g??n??ration de la page. Vous avez aussi appris ?? ??crire dans des fichiers, mais cela devient vite tr??s compliqu??, d??s que vous avez beaucoup de donn??es ?? enregistrer.\r\n\r\nOr, il va bien falloir stocker quelque part la liste de vos membres, les messages de vos forums, les options de navigation des membres??? Les bases de donn??es constituent le meilleur moyen de faire cela de fa??on simple et propre. Nous allons les ??tudier durant toute cette partie du cours !\r\n\r\nLa base de donn??es (BDD) est un syst??me qui enregistre des informations. Un peu comme un fichier texte ? Non, pas vraiment. Ce qui est tr??s important ici, c\'est que ces informations sont toujours class??es. Et c\'est ??a qui fait que la BDD est si pratique : c\'est un moyen simple de ranger des informations\r\n\r\nC\'est un peu ce que je me disais au d??but??? Classer certaines choses, d\'accord, mais il me semblait que je n\'en aurais besoin que tr??s rarement.\r\nGrave erreur ! Vous allez le voir : 99 % du temps, on range ses informations dans une base de donn??es. Pour le reste, on peut les enregistrer dans un fichier comme on a appris ?? le faire??? mais quand on a go??t?? aux bases de donn??es, on peut difficilement s\'en passer ensuite !\r\n\r\nImaginez par exemple une armoire, dans laquelle chaque dossier est ?? sa place.\r\nQuand tout est ?? sa place, il est beaucoup plus facile de retrouver un objet, n\'est-ce pas ? Eh bien l??, c\'est pareil : en classant les informations que vous collectez (concernant vos visiteurs, par exemple), il vous sera tr??s facile de r??cup??rer plus tard ce que vous cherchez.', 3, '2021-05-18 21:23:56', '2021-05-18 19:23:56', ''),
(13, 'Coder proprement', 'En programmation comme partout ailleurs, il y a deux types de personnes : celles qui effectuent leur travail rapidement, mais ne se soucient pas de la qualit??, de la lisibilit??, et de l\'??volutivit?? de leur code, et celles qui font l\'effort de soigner un peu leur travail, car elles ont conscience que ce petit travail suppl??mentaire sera un gain de temps ??norme ?? l\'avenir.', 'En programmation comme partout ailleurs, il y a deux types de personnes : celles qui effectuent leur travail rapidement, mais ne se soucient pas de la qualit??, de la lisibilit??, et de l\'??volutivit?? de leur code, et celles qui font l\'effort de soigner un peu leur travail, car elles ont conscience que ce petit travail suppl??mentaire sera un gain de temps ??norme ?? l\'avenir.\r\n\r\nQuand on d??bute, on a tendance ?? se dire ?? ??a marche, parfait, ne touchons plus ?? rien et laissons comme ??a ??. C\'est un mauvais r??flexe, et je ne serai pas le seul ?? vous le dire : n\'importe quel programmeur PHP ayant un peu d\'exp??rience sait qu\'un code qui fonctionne n\'est pas forc??ment bon.\r\n\r\nCette annexe est en fait une suite de petits conseils apparemment peu importants, sur lesquels je voudrais que vous portiez toute votre attention.\r\nC\'est peu de chose, et c\'est pourtant ce qui fait la distinction entre un ?? bon ?? programmeur et euh??? un programmeur du dimanche !\r\nQuand vous cr??ez un script PHP, vous devez inventer des noms. Vous allez devoir donner des noms ?? diff??rents types d\'??l??ments \r\n\r\nL\'id??e est simple : il faut que vous fassiez l\'effort de choisir des noms de variables et de fonctions clairs et compr??hensibles.\r\n\r\nPassez ne serait-ce qu\'une seconde de plus ?? r??fl??chir ?? des noms clairs. N\'ayez pas peur de choisir des noms un peu longs, ce n\'est pas une perte de temps, bien au contraire.\r\nVous pouvez utiliser le symbole underscore ?? _ ?? pour remplacer les espaces, qui sont, je vous le rappelle, interdits dans les noms de variables et de fonctions.', 6, '2021-05-18 21:26:59', '2021-05-19 04:54:13', '');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `pseudo` varchar(55) NOT NULL,
  `role` enum('user','admin','superAdmin','') NOT NULL DEFAULT 'user',
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_update` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `email`, `password`, `pseudo`, `role`, `created_date`, `last_update`, `token`) VALUES
(1, 'test@test.fr', '$2y$10$PMdNms8KwVyoZTJCy2QLmOoTb/NBgH/tJshzSap4cYDBPfEBdr4VS', 'test', 'superAdmin', '2021-05-04 08:36:13', '2021-05-19 06:46:50', 'b477d3ef732ab62684230b5524f29412'),
(2, 'test1@test.fr', '$2y$10$KJvsOtwBBcqR1H5Zu6d4i.1AVw7uyY.40I9AUgMjxVnHzTKbDadZy', 'test1', 'admin', '2021-05-04 09:06:42', '2021-05-19 06:47:50', 'daaca9824f97dfd8713a77a1307b0299'),
(3, 'test3@test.fr', '$2y$10$UMNLK4SQ0IEcQTozANvDReneVvNy0M/AHIvFuYBy5X1IRuEWtaT3K', 'test3', 'user', '2021-05-04 09:41:12', '2021-05-19 06:53:38', 'ef988aa17455373a6f509a8589c9554f'),
(6, 'test2@test.fr', '$2y$10$AiYm7HCDzqyOIH69NxDGJubx4lrdW4.u26LDH.9yUDJODGOiL5srW', 'test2', 'admin', '2021-05-06 15:52:38', '2021-05-19 06:52:32', '4629418804e0460e12818883cf68ca35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idPost_comment` (`idPost`),
  ADD KEY `idUser_comment` (`idUser`);

--
-- Indexes for table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId_post` (`userId`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique` (`email`),
  ADD UNIQUE KEY `unique pseudo` (`pseudo`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `idPost_comment` FOREIGN KEY (`idPost`) REFERENCES `post` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `idUser_comment` FOREIGN KEY (`idUser`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `userId_post` FOREIGN KEY (`userId`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;