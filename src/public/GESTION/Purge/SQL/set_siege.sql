
-- Définition de l'accès CDS

INSERT INTO chefsecteur SET Nom="DURAND", Prenom="John", E_Mail="j.durand@example.fr";
SET @id_chefsecteur = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="DURAND", USER_PRENOM="John", USER_MAIL="j.durand@example.fr", USER_LOCKERS_ID="123456789";
SET @id_user_cds = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_cds, ROLE_TYPE="CDS", ROLE_NUM=@id_chefsecteur;


-- ---------------------------------------------------------------------------

-- Définition de l'accès CDR

INSERT INTO chefregion SET Nom="DUPOND", Prenom="Tom", E_Mail="j.durand@example.fr";
SET @id_chefregion = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="DUPOND", USER_PRENOM="Tom", USER_MAIL="t.dupond@example.fr", USER_LOCKERS_ID="123456789";
SET @id_user_cdr = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_cdr, ROLE_TYPE="CDR", ROLE_NUM=@id_chefregion;
