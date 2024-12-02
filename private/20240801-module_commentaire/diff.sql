-- Création des tables pour le module de commentaire
--
-- Table structure for table `commentaires_libre`
--
CREATE TABLE `commentaires_libre` (
    `CML_ID` int(11) NOT NULL AUTO_INCREMENT,
    `DOS_NUM` int(11) NOT NULL,
    `CMS_ID` int(11) NOT NULL,
    `CML_MOIS` date NOT NULL,
    `CML_INTITULE` mediumtext,
    `CML_COMMENTAIRE` mediumtext,
    `CML_FIXED` tinyint(1) DEFAULT '0',
    PRIMARY KEY (`CML_ID`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

--
-- Table structure for table `commentaires_structure`
--
CREATE TABLE `commentaires_structure` (
    `CMS_ID` int(11) NOT NULL AUTO_INCREMENT,
    `CMS_ONGLET` varchar(45) DEFAULT NULL,
    `CMS_CATEGORIE` varchar(45) DEFAULT NULL,
    `CMS_ORDRE` int(11) DEFAULT NULL,
    PRIMARY KEY (`CMS_ID`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

--
-- Table structure for table `commentaires_tableau`
--
CREATE TABLE `commentaires_tableau` (
    `CMT_ID` int(11) NOT NULL AUTO_INCREMENT,
    `DOS_NUM` int(11) NOT NULL,
    `CMT_MOIS` date NOT NULL,
    `CMT_TYPE` enum('ms', 'charges', 'bilan', 'produits') NOT NULL,
    `CMT_COMMENTAIRE` mediumtext,
    `CPB_NUM` int(11) unsigned DEFAULT NULL,
    `code_compte` int(11) unsigned DEFAULT NULL,
    `codePoste` int(11) unsigned DEFAULT NULL,
    `CMT_TOVALIDATE` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`CMT_ID`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;


-- Création des relations

ALTER TABLE
    `myreport`.`commentaires_libre`
ADD
    INDEX `fk_cms_id_idx` (`CMS_ID` ASC);

;

ALTER TABLE
    `myreport`.`commentaires_libre`
ADD
    CONSTRAINT `fk_cms_id` FOREIGN KEY (`CMS_ID`) REFERENCES `myreport`.`commentaires_structure` (`CMS_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE
    `myreport`.`commentaires_tableau`
ADD
    INDEX `fk_dos_num_idx` (`DOS_NUM` ASC),
ADD
    INDEX `fk_code_compte_idx` (`code_compte` ASC),
ADD
    INDEX `fk_codePoste_idx` (`codePoste` ASC);

;

ALTER TABLE
    `myreport`.`commentaires_tableau`
ADD
    CONSTRAINT `fk_dos_num` FOREIGN KEY (`DOS_NUM`) REFERENCES `myreport`.`dossier` (`DOS_NUM`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD
    CONSTRAINT `fk_code_compte` FOREIGN KEY (`code_compte`) REFERENCES `myreport`.`comptes` (`code_compte`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD
    CONSTRAINT `fk_codePoste` FOREIGN KEY (`codePoste`) REFERENCES `myreport`.`compteposte` (`codePoste`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE
    `myreport`.`commentaires_tableau` CHANGE COLUMN `CPB_NUM` `CPB_NUM` INT(11) NULL DEFAULT NULL,
ADD
    INDEX `fk_cpb_num_idx` (`CPB_NUM` ASC);
;
ALTER TABLE
    `myreport`.`commentaires_tableau`
ADD
    CONSTRAINT `fk_cpb_num` FOREIGN KEY (`CPB_NUM`) REFERENCES `myreport`.`comptebilan` (`CPTB_NUM`) ON DELETE NO ACTION ON UPDATE NO ACTION;