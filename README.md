# Introduction

Tableau de reporting pour BP

# Docker compose

```yaml
version: '3'
services:
  application:
    build: ../myreport
    environment:
      - APACHE_HTPASSWD_USER=test
      - APACHE_HTPASSWD_PASSWORD=test
      - APP_DB_HOSTNAME=
      - APP_DB_NAME=
      - APP_DB_USERNAME=
      - APP_DB_PASSWORD=
      - APP_HTML2PDF_ADDRESS=
      - APP_HTML2PDF_PORT=
      - APP_AWS_ACCESS_KEY_ID=
      - APP_AWS_SECRET_ACCESS_KEY=
      - APP_S3_BUCKET=
      - APP_S3_PREFIX=
    volumes:
      - ./prog/:/app/public/
    ports:
      - 8080:80
```

# Usage

```bash
docker-compose build
docker-compose up -d
```

# Available Configuration Parameters

| Parameter | Required | Description |
|-----------|-------------|-------------|
| `APP_DB_DIALECT` || Set the database connection dialect. Default : `mysql` |
| `APP_DB_HOSTNAME` | `yes` | Sets the database hostname |
| `APP_DB_PORT` | `yes` | Sets the database port number |
| `APP_DB_NAME` | `yes` | Sets the database name |
| `APP_DB_USERNAME` | `yes` | Sets the database username |
| `APP_DB_PASSWORD` | `yes` | Sets the database password |
| `APP_DB_DEBUG` || Toggles the SQL query console logging. Default : `false` |
| `APP_HTML2PDF_SOCKET` || Activate realtime print. Default : `1` |
| `APP_HTML2PDF_ADDRESS` || Sets the HTML2PDF ADDRESS |
| `APP_HTML2PDF_PORT` || Sets the HTML2PDF port |
| `APP_AWS_ACCESS_KEY_ID` || AWS IAM access key |
| `APP_AWS_SECRET_ACCESS_KEY` || AWS IAM secret key |
| `APP_S3_BUCKET` || AWS S3 bucket name |
| `APP_S3_PREFIX` || AWS S3 bucket prefix |

# Test

In a web browser, use the following HTTP address :

```
Access the application login page using :
    http://localhost:8080
```
# Terraform


```markdown
## 1.2. Créer un repository ECR via AWS CLI

Si vous préférez utiliser la ligne de commande, vous pouvez créer un repository en utilisant la commande suivante :

```bash
aws ecr create-repository --repository-name myreport-repo
```

Cette commande renverra un résultat contenant des informations sur le repository, y compris le URI du repository que vous utiliserez pour pousser votre image.

### Étape 2 : Authentification à votre registre Docker

Avant de pouvoir pousser des images dans votre repository ECR, vous devez vous authentifier auprès du registre Docker. Pour ce faire, vous devez utiliser la commande AWS CLI suivante pour obtenir un jeton d'authentification.

#### 2.1. Authentification pour Amazon ECR

Exécutez la commande suivante pour obtenir un jeton d'authentification pour ECR et vous connecter au registre Docker :

```bash
aws ecr get-login-password --region <your-region> | docker login --username AWS --password-stdin <aws_account_id>.dkr.ecr.<your-region>.amazonaws.com
```

- Remplacez `<your-region>` par la région AWS que vous utilisez (par exemple : us-west-2).
- Remplacez `<aws_account_id>` par votre identifiant AWS.

### Étape 3 : Taguer l'image Docker

Une fois que vous êtes authentifié, vous devez taguer votre image Docker avec l'URI du repository ECR afin qu'elle puisse y être poussée. Utilisez la commande suivante pour cela :

#### 3.1. Taguer votre image Docker

```bash
docker tag gitlab.cicd.biz:5500/shared/php-server:7.4 <aws_account_id>.dkr.ecr.<your-region>.amazonaws.com/myreport-repo:latest
```

Assurez-vous de remplacer `<aws_account_id>`, `<your-region>`, et `myreport-repo` par les valeurs appropriées.

### Étape 4 : Pousser l'image vers ECR

Une fois que votre image est correctement taguée, vous pouvez la pousser vers votre repository ECR avec la commande suivante :

```bash
docker push <aws_account_id>.dkr.ecr.<your-region>.amazonaws.com/myreport-repo:latest
```

Cette commande poussera l'image Docker vers le repository ECR que vous avez créé.
```

Cela inclut les titres, les explications et les commandes nécessaires pour chaque étape.

# terraform
# terraform
# terraform
# terraform
