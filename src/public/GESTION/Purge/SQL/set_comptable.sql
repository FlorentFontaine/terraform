

-- Définition des comptables

-- ------------------------------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="01-MYGEST Comptable", CC_MAIL="01comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="01-MYGEST", USER_PRENOM="Comptable", USER_MAIL="01comptable-mygest@cicd.biz", USER_LOCKERS_ID="143ceffd-f691-48c5-943a-d21e63d6fa3d";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="02-MYGEST Comptable", CC_MAIL="02comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="02-MYGEST", USER_PRENOM="Comptable", USER_MAIL="02comptable-mygest@cicd.biz", USER_LOCKERS_ID="cffaeda9-ca1d-4ba5-b87e-abdafc65ca46";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="03-MYGEST Comptable", CC_MAIL="03comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="03-MYGEST", USER_PRENOM="Comptable", USER_MAIL="03comptable-mygest@cicd.biz", USER_LOCKERS_ID="a93ff206-4278-4045-a111-cb6e9f5d9be1";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="04-MYGEST Comptable", CC_MAIL="04comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="04-MYGEST", USER_PRENOM="Comptable", USER_MAIL="04comptable-mygest@cicd.biz", USER_LOCKERS_ID="2d13ced8-0fd9-41ab-8d15-1f53dfe1bdd1";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="05-MYGEST Comptable", CC_MAIL="05comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="05-MYGEST", USER_PRENOM="Comptable", USER_MAIL="05comptable-mygest@cicd.biz", USER_LOCKERS_ID="4443c0c0-3814-463c-8ec0-c997e587d424";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="06-MYGEST Comptable", CC_MAIL="06comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="06-MYGEST", USER_PRENOM="Comptable", USER_MAIL="06comptable-mygest@cicd.biz", USER_LOCKERS_ID="1dc03e60-c2f8-4dab-92f3-af5adb5adbe8";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="07-MYGEST Comptable", CC_MAIL="07comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="07-MYGEST", USER_PRENOM="Comptable", USER_MAIL="07comptable-mygest@cicd.biz", USER_LOCKERS_ID="3504dc94-0df1-4f51-8ad0-ed9970f0a93e";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="08-MYGEST Comptable", CC_MAIL="08comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="08-MYGEST", USER_PRENOM="Comptable", USER_MAIL="08comptable-mygest@cicd.biz", USER_LOCKERS_ID="27cafb58-3933-41e9-983c-1dd2fd48a50a";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="09-MYGEST Comptable", CC_MAIL="09comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="09-MYGEST", USER_PRENOM="Comptable", USER_MAIL="09comptable-mygest@cicd.biz", USER_LOCKERS_ID="562deeb4-497a-4b0e-9cbf-cd7ec96cbc45";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="10-MYGEST Comptable", CC_MAIL="10comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="10-MYGEST", USER_PRENOM="Comptable", USER_MAIL="10comptable-mygest@cicd.biz", USER_LOCKERS_ID="7842b024-f454-4675-8f03-035947e1e341";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="11-MYGEST Comptable", CC_MAIL="11comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="11-MYGEST", USER_PRENOM="Comptable", USER_MAIL="11comptable-mygest@cicd.biz", USER_LOCKERS_ID="ba5e6ec2-e77d-455c-8ee9-df3f459ff06a";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="12-MYGEST Comptable", CC_MAIL="12comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="12-MYGEST", USER_PRENOM="Comptable", USER_MAIL="12comptable-mygest@cicd.biz", USER_LOCKERS_ID="6b42b9a1-c7d1-45d8-a483-529e89e1b844";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="13-MYGEST Comptable", CC_MAIL="13comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="13-MYGEST", USER_PRENOM="Comptable", USER_MAIL="13comptable-mygest@cicd.biz", USER_LOCKERS_ID="f1bb88f2-8183-4aa4-a751-f08cd7f3ade6";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="14-MYGEST Comptable", CC_MAIL="14comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="14-MYGEST", USER_PRENOM="Comptable", USER_MAIL="14comptable-mygest@cicd.biz", USER_LOCKERS_ID="57838ab9-4028-4a8d-9ed3-34ab81b12b4e";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="15-MYGEST Comptable", CC_MAIL="15comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="15-MYGEST", USER_PRENOM="Comptable", USER_MAIL="15comptable-mygest@cicd.biz", USER_LOCKERS_ID="e886dafd-15b0-4ba7-9ad8-688e38d141d4";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="16-MYGEST Comptable", CC_MAIL="16comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="16-MYGEST", USER_PRENOM="Comptable", USER_MAIL="16comptable-mygest@cicd.biz", USER_LOCKERS_ID="7448dcc0-d2a1-42d3-b225-368b699364b0";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="17-MYGEST Comptable", CC_MAIL="17comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="17-MYGEST", USER_PRENOM="Comptable", USER_MAIL="17comptable-mygest@cicd.biz", USER_LOCKERS_ID="24b465b9-bb04-4a87-a433-cb96e51294b7";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="18-MYGEST Comptable", CC_MAIL="18comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="18-MYGEST", USER_PRENOM="Comptable", USER_MAIL="18comptable-mygest@cicd.biz", USER_LOCKERS_ID="f63faebe-05d8-4f9c-b3d3-4d12aeaca45a";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="19-MYGEST Comptable", CC_MAIL="19comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="19-MYGEST", USER_PRENOM="Comptable", USER_MAIL="19comptable-mygest@cicd.biz", USER_LOCKERS_ID="dcfc4ccd-6d75-4565-95fb-70bb37db5f02";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="20-MYGEST Comptable", CC_MAIL="20comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="20-MYGEST", USER_PRENOM="Comptable", USER_MAIL="20comptable-mygest@cicd.biz", USER_LOCKERS_ID="7145ac59-0222-4e9f-b83c-910008d77a5e";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="21-MYGEST Comptable", CC_MAIL="21comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="21-MYGEST", USER_PRENOM="Comptable", USER_MAIL="21comptable-mygest@cicd.biz", USER_LOCKERS_ID="d978543a-9209-4ce7-ad2d-c805f8bcd6e6";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="22-MYGEST Comptable", CC_MAIL="22comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="22-MYGEST", USER_PRENOM="Comptable", USER_MAIL="22comptable-mygest@cicd.biz", USER_LOCKERS_ID="f4670606-b901-4c24-81d6-be67703af845";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="23-MYGEST Comptable", CC_MAIL="23comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="23-MYGEST", USER_PRENOM="Comptable", USER_MAIL="23comptable-mygest@cicd.biz", USER_LOCKERS_ID="88b64c82-012f-4eab-b59b-6db3f6168ca4";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="24-MYGEST Comptable", CC_MAIL="24comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="24-MYGEST", USER_PRENOM="Comptable", USER_MAIL="24comptable-mygest@cicd.biz", USER_LOCKERS_ID="bb40e210-2e3b-4261-92d1-60e70b8aacb3";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="25-MYGEST Comptable", CC_MAIL="25comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="25-MYGEST", USER_PRENOM="Comptable", USER_MAIL="25comptable-mygest@cicd.biz", USER_LOCKERS_ID="678ed2c2-f1dd-4d7e-a15f-f362295cf086";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="26-MYGEST Comptable", CC_MAIL="26comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="26-MYGEST", USER_PRENOM="Comptable", USER_MAIL="26comptable-mygest@cicd.biz", USER_LOCKERS_ID="6b5c3c54-4078-4fa2-af10-164d6c227fd8";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="27-MYGEST Comptable", CC_MAIL="27comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="27-MYGEST", USER_PRENOM="Comptable", USER_MAIL="27comptable-mygest@cicd.biz", USER_LOCKERS_ID="1fd8b792-0709-4658-9b02-f51d549a0efd";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="28-MYGEST Comptable", CC_MAIL="28comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="28-MYGEST", USER_PRENOM="Comptable", USER_MAIL="28comptable-mygest@cicd.biz", USER_LOCKERS_ID="64364e10-0cdd-4b50-81c8-f03e938adea7";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="29-MYGEST Comptable", CC_MAIL="29comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="29-MYGEST", USER_PRENOM="Comptable", USER_MAIL="29comptable-mygest@cicd.biz", USER_LOCKERS_ID="3ca02a50-ffa2-4f94-95ac-ef97365e9c78";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------


INSERT INTO comptable SET CAB_NUM=1, CC_NOM="30-MYGEST Comptable", CC_MAIL="30comptable-mygest@cicd.biz";
SET @id_comptable = LAST_INSERT_ID();

-- Ajout à la table "user"

INSERT INTO user SET USER_NOM="30-MYGEST", USER_PRENOM="Comptable", USER_MAIL="30comptable-mygest@cicd.biz", USER_LOCKERS_ID="c57fc1a7-a3cf-4217-944d-728ae72727b7";
SET @id_user_comptable = LAST_INSERT_ID();

-- Mise en lien dans la table "userhasrole"

INSERT INTO userhasrole SET USER_NUM=@id_user_comptable, ROLE_TYPE="COMPTABLE", ROLE_NUM=@id_comptable;

-- ----------------------------------------------------------------------------------------

