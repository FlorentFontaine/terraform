-- ----------------------------
-- --- PURGE MyReport DEMO   --
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE balance;
ALTER TABLE balance AUTO_INCREMENT = 1;

TRUNCATE TABLE balanceimport;

TRUNCATE TABLE benchprocharge;

TRUNCATE TABLE carburantvolumes;

TRUNCATE TABLE commentaires_libre;
ALTER TABLE commentaires_libre AUTO_INCREMENT = 1;

TRUNCATE TABLE commentaires_tableau;
ALTER TABLE commentaires_tableau AUTO_INCREMENT = 1;

TRUNCATE TABLE comptepostedetail;
ALTER TABLE comptepostedetail AUTO_INCREMENT = 1;

TRUNCATE TABLE chefsecteur;
ALTER TABLE chefsecteur AUTO_INCREMENT = 1;

TRUNCATE TABLE chefregion;
ALTER TABLE chefregion AUTO_INCREMENT = 1;

TRUNCATE TABLE crp;
ALTER TABLE crp AUTO_INCREMENT = 1;

TRUNCATE TABLE crp_detail;

TRUNCATE TABLE dossier;
ALTER TABLE dossier AUTO_INCREMENT = 1;

TRUNCATE TABLE facturation;
ALTER TABLE facturation AUTO_INCREMENT = 1;

TRUNCATE TABLE gerant;
ALTER TABLE gerant AUTO_INCREMENT = 1;

TRUNCATE TABLE comptable;
ALTER TABLE comptable AUTO_INCREMENT = 1;

TRUNCATE TABLE liaisoncompte;
ALTER TABLE liaisoncompte AUTO_INCREMENT = 1;

TRUNCATE TABLE lieu;
ALTER TABLE lieu AUTO_INCREMENT = 1;

-- TODO Table à delete apres suppression du code inutile de l'application
-- TRUNCATE TABLE li_quotepart_poste;

TRUNCATE TABLE quotepart_empute;

TRUNCATE TABLE resultatposte;

TRUNCATE TABLE rgdivers;

TRUNCATE TABLE saison;

TRUNCATE TABLE station;
ALTER TABLE station AUTO_INCREMENT = 1;

TRUNCATE TABLE stationcc;

TRUNCATE TABLE tmp_benchlieu;

TRUNCATE TABLE tmp_benchlieuin;

TRUNCATE TABLE user;
ALTER TABLE user AUTO_INCREMENT = 1;

TRUNCATE TABLE userhasrole;

SET FOREIGN_KEY_CHECKS = 1;




