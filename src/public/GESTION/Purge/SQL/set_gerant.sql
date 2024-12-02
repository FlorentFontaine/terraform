

-- Définition des gérants

-- ------------------------------------------------------------------------------------------------------------

INSERT INTO gerant SET STA_NUM="1", GER_NUMBER="0", GER_NOM="01-MYGEST", GER_PRENOM="Gérant", GER_MAIL="01gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="01-MYGEST", USER_PRENOM="Gérant", USER_MAIL="01gerant-mygest@cicd.biz", USER_LOCKERS_ID="5f7198a2-f828-46ef-a5e8-cbf05a57697c";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="2", GER_NUMBER="0", GER_NOM="02-MYGEST", GER_PRENOM="Gérant", GER_MAIL="02gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="02-MYGEST", USER_PRENOM="Gérant", USER_MAIL="02gerant-mygest@cicd.biz", USER_LOCKERS_ID="145ec044-ea68-4af8-b3f6-559a4da4f329";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="3", GER_NUMBER="0", GER_NOM="03-MYGEST", GER_PRENOM="Gérant", GER_MAIL="03gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="03-MYGEST", USER_PRENOM="Gérant", USER_MAIL="03gerant-mygest@cicd.biz", USER_LOCKERS_ID="7a045be2-6575-4685-beb1-b76ad61ed3e0";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="4", GER_NUMBER="0", GER_NOM="04-MYGEST", GER_PRENOM="Gérant", GER_MAIL="04gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="04-MYGEST", USER_PRENOM="Gérant", USER_MAIL="04gerant-mygest@cicd.biz", USER_LOCKERS_ID="241ffbc8-f729-41c4-b637-aedd25ce50a0";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="5", GER_NUMBER="0", GER_NOM="05-MYGEST", GER_PRENOM="Gérant", GER_MAIL="05gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="05-MYGEST", USER_PRENOM="Gérant", USER_MAIL="05gerant-mygest@cicd.biz", USER_LOCKERS_ID="725e4a66-ba13-417c-af24-ccbc7ac643c2";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="6", GER_NUMBER="0", GER_NOM="06-MYGEST", GER_PRENOM="Gérant", GER_MAIL="06gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="06-MYGEST", USER_PRENOM="Gérant", USER_MAIL="06gerant-mygest@cicd.biz", USER_LOCKERS_ID="f548169a-dc30-4d47-aac6-ec13062d5cf6";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="7", GER_NUMBER="0", GER_NOM="07-MYGEST", GER_PRENOM="Gérant", GER_MAIL="07gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="07-MYGEST", USER_PRENOM="Gérant", USER_MAIL="07gerant-mygest@cicd.biz", USER_LOCKERS_ID="c5c079d3-5d77-4704-b0ad-488f4131b836";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="8", GER_NUMBER="0", GER_NOM="08-MYGEST", GER_PRENOM="Gérant", GER_MAIL="08gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="08-MYGEST", USER_PRENOM="Gérant", USER_MAIL="08gerant-mygest@cicd.biz", USER_LOCKERS_ID="f1ef6862-8a39-458e-825b-f1b965ee34c9";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="9", GER_NUMBER="0", GER_NOM="09-MYGEST", GER_PRENOM="Gérant", GER_MAIL="09gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="09-MYGEST", USER_PRENOM="Gérant", USER_MAIL="09gerant-mygest@cicd.biz", USER_LOCKERS_ID="59e679fa-c184-43f8-a7be-a5cfe4ae6bd4";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="10", GER_NUMBER="0", GER_NOM="10-MYGEST", GER_PRENOM="Gérant", GER_MAIL="10gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="10-MYGEST", USER_PRENOM="Gérant", USER_MAIL="10gerant-mygest@cicd.biz", USER_LOCKERS_ID="6441a9e8-8855-4fe5-ad5e-df00aa5f641d";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="11", GER_NUMBER="0", GER_NOM="11-MYGEST", GER_PRENOM="Gérant", GER_MAIL="11gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="11-MYGEST", USER_PRENOM="Gérant", USER_MAIL="11gerant-mygest@cicd.biz", USER_LOCKERS_ID="2335f200-7421-4015-8e54-757e7d3c7bbb";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="12", GER_NUMBER="0", GER_NOM="12-MYGEST", GER_PRENOM="Gérant", GER_MAIL="12gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="12-MYGEST", USER_PRENOM="Gérant", USER_MAIL="12gerant-mygest@cicd.biz", USER_LOCKERS_ID="31476665-8e3d-436e-bf56-e00dbe7622ab";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="13", GER_NUMBER="0", GER_NOM="13-MYGEST", GER_PRENOM="Gérant", GER_MAIL="13gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="13-MYGEST", USER_PRENOM="Gérant", USER_MAIL="13gerant-mygest@cicd.biz", USER_LOCKERS_ID="ed01a782-cb38-4ffe-8a72-2e620bbc1081";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="14", GER_NUMBER="0", GER_NOM="14-MYGEST", GER_PRENOM="Gérant", GER_MAIL="14gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="14-MYGEST", USER_PRENOM="Gérant", USER_MAIL="14gerant-mygest@cicd.biz", USER_LOCKERS_ID="a2645da5-1ed0-45bb-813f-a60e6b4dc112";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="15", GER_NUMBER="0", GER_NOM="15-MYGEST", GER_PRENOM="Gérant", GER_MAIL="15gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="15-MYGEST", USER_PRENOM="Gérant", USER_MAIL="15gerant-mygest@cicd.biz", USER_LOCKERS_ID="4b1a3165-a465-469a-a4ec-c959d6d5de8a";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="16", GER_NUMBER="0", GER_NOM="16-MYGEST", GER_PRENOM="Gérant", GER_MAIL="16gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="16-MYGEST", USER_PRENOM="Gérant", USER_MAIL="16gerant-mygest@cicd.biz", USER_LOCKERS_ID="dc7a2ec4-2964-4302-a22f-b7db07a15460";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="17", GER_NUMBER="0", GER_NOM="17-MYGEST", GER_PRENOM="Gérant", GER_MAIL="17gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="17-MYGEST", USER_PRENOM="Gérant", USER_MAIL="17gerant-mygest@cicd.biz", USER_LOCKERS_ID="712dec85-ca38-4b03-81ba-0a169a56733e";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="18", GER_NUMBER="0", GER_NOM="18-MYGEST", GER_PRENOM="Gérant", GER_MAIL="18gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="18-MYGEST", USER_PRENOM="Gérant", USER_MAIL="18gerant-mygest@cicd.biz", USER_LOCKERS_ID="3306ea87-af76-46c2-8191-ac7f57653933";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="19", GER_NUMBER="0", GER_NOM="19-MYGEST", GER_PRENOM="Gérant", GER_MAIL="19gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="19-MYGEST", USER_PRENOM="Gérant", USER_MAIL="19gerant-mygest@cicd.biz", USER_LOCKERS_ID="91bbd17d-09cd-400c-b1a9-44eaf581ef84";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="20", GER_NUMBER="0", GER_NOM="20-MYGEST", GER_PRENOM="Gérant", GER_MAIL="20gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="20-MYGEST", USER_PRENOM="Gérant", USER_MAIL="20gerant-mygest@cicd.biz", USER_LOCKERS_ID="1698f337-c1ed-40c0-8485-9129c7ed4bcc";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="21", GER_NUMBER="0", GER_NOM="21-MYGEST", GER_PRENOM="Gérant", GER_MAIL="21gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="21-MYGEST", USER_PRENOM="Gérant", USER_MAIL="21gerant-mygest@cicd.biz", USER_LOCKERS_ID="67cd8d9d-11d9-455e-9965-45203391fd45";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="22", GER_NUMBER="0", GER_NOM="22-MYGEST", GER_PRENOM="Gérant", GER_MAIL="22gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="22-MYGEST", USER_PRENOM="Gérant", USER_MAIL="22gerant-mygest@cicd.biz", USER_LOCKERS_ID="abdea037-d1ea-4c07-8226-dfd8f9e5f95f";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="23", GER_NUMBER="0", GER_NOM="23-MYGEST", GER_PRENOM="Gérant", GER_MAIL="23gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="23-MYGEST", USER_PRENOM="Gérant", USER_MAIL="23gerant-mygest@cicd.biz", USER_LOCKERS_ID="05b1cd9e-2fbb-477b-8a08-f653f2b54168";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="24", GER_NUMBER="0", GER_NOM="24-MYGEST", GER_PRENOM="Gérant", GER_MAIL="24gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="24-MYGEST", USER_PRENOM="Gérant", USER_MAIL="24gerant-mygest@cicd.biz", USER_LOCKERS_ID="ee1132b4-6c90-431f-acfa-47ed6bcae1a7";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="25", GER_NUMBER="0", GER_NOM="25-MYGEST", GER_PRENOM="Gérant", GER_MAIL="25gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="25-MYGEST", USER_PRENOM="Gérant", USER_MAIL="25gerant-mygest@cicd.biz", USER_LOCKERS_ID="1bfe72a4-5df3-46de-b713-f5103521b77b";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="26", GER_NUMBER="0", GER_NOM="26-MYGEST", GER_PRENOM="Gérant", GER_MAIL="26gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="26-MYGEST", USER_PRENOM="Gérant", USER_MAIL="26gerant-mygest@cicd.biz", USER_LOCKERS_ID="3874f03e-4fc8-442e-8a29-261de15b309c";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="27", GER_NUMBER="0", GER_NOM="27-MYGEST", GER_PRENOM="Gérant", GER_MAIL="27gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="27-MYGEST", USER_PRENOM="Gérant", USER_MAIL="27gerant-mygest@cicd.biz", USER_LOCKERS_ID="377d8e8e-a821-47e0-a0bd-c9fd0754ce2e";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="28", GER_NUMBER="0", GER_NOM="28-MYGEST", GER_PRENOM="Gérant", GER_MAIL="28gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="28-MYGEST", USER_PRENOM="Gérant", USER_MAIL="28gerant-mygest@cicd.biz", USER_LOCKERS_ID="21bb17a2-c47f-4053-91bc-22f7cad0d89b";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="29", GER_NUMBER="0", GER_NOM="29-MYGEST", GER_PRENOM="Gérant", GER_MAIL="29gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="29-MYGEST", USER_PRENOM="Gérant", USER_MAIL="29gerant-mygest@cicd.biz", USER_LOCKERS_ID="439fb25d-1c71-42c7-9222-097c4ffe28b9";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------


INSERT INTO gerant SET STA_NUM="30", GER_NUMBER="0", GER_NOM="30-MYGEST", GER_PRENOM="Gérant", GER_MAIL="30gerant-mygest@cicd.biz";
SET @id_gerant = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="30-MYGEST", USER_PRENOM="Gérant", USER_MAIL="30gerant-mygest@cicd.biz", USER_LOCKERS_ID="1d7286e3-2395-4532-8199-ff21224154ab";
SET @id_user_gerant = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_gerant, ROLE_TYPE="STATION", ROLE_NUM=@id_gerant;

-- ----------------------------------------------------------------------------------------

