provider "aws" {
  access_key = aws_access_key_id
  secret_key = aws_secret_access_key
  region     = "eu-west-3"
}

# --- Créer une paire de clés SSH ---
resource "aws_key_pair" "my_key" {
  key_name   = "my-key"  # Nom de la clé
  public_key = file("~/.ssh/id_rsa.pub")
}

# --- VPC et sous-réseau ---
resource "aws_vpc" "main" {
  cidr_block           = "10.0.0.0/16"
  enable_dns_support   = true
  enable_dns_hostnames = true
  tags = {
    Name = "myreport-vpc"
  }
}

resource "aws_subnet" "public" {
  vpc_id                  = aws_vpc.main.id
  cidr_block              = "10.0.1.0/24"
  map_public_ip_on_launch = true
  availability_zone       = "eu-west-3a"
  tags = {
    Name = "myreport-public-subnet"
  }
}

# --- Internet Gateway ---
resource "aws_internet_gateway" "igw" {
  vpc_id = aws_vpc.main.id
  tags = {
    Name = "myreport-igw"
  }
}

# --- Table de routage et association ---
resource "aws_route_table" "public_rt" {
  vpc_id = aws_vpc.main.id

  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.igw.id
  }

  tags = {
    Name = "myreport-public-rt"
  }
}

resource "aws_route_table_association" "public_rt_assoc" {
  subnet_id      = aws_subnet.public.id
  route_table_id = aws_route_table.public_rt.id
}

# --- Groupe de sécurité pour EC2 ---
resource "aws_security_group" "ecs_sg" {
  name        = "ecs_sg"
  description = "Allow HTTP, SSH, and custom port traffic for ECS"
  vpc_id      = aws_vpc.main.id

  ingress {
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]  # Ou restreindre à votre IP spécifique
  }

  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 9090
    to_port     = 9090
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

# --- ECS Cluster ---
resource "aws_ecs_cluster" "main" {
  name = "myreport-cluster"
}

# --- Rôle IAM pour les instances ECS ---
resource "aws_iam_role" "ecs_instance_role" {
  name = "ecs_instance_role"

  assume_role_policy = jsonencode({
    Version = "2012-10-17",
    Statement = [
      {
        Action   = "sts:AssumeRole",
        Effect   = "Allow",
        Principal = {
          Service = "ec2.amazonaws.com"
        }
      }
    ]
  })
}

resource "aws_iam_policy" "ecs_instance_policy" {
  name = "ecs-instance-policy"

  policy = jsonencode({
    Version = "2012-10-17",
    Statement = [
      {
        Effect   = "Allow",
        Action   = [
          "ecr:GetAuthorizationToken",
          "ecr:BatchCheckLayerAvailability",
          "ecr:GetDownloadUrlForLayer",
          "ecr:BatchGetImage",
          "ecs:Describe*",
          "ecs:List*",
          "logs:CreateLogStream",
          "logs:PutLogEvents"
        ],
        Resource = "*"
      }
    ]
  })
}

resource "aws_iam_role_policy_attachment" "ecs_instance_policy_attach" {
  role       = aws_iam_role.ecs_instance_role.name
  policy_arn = aws_iam_policy.ecs_instance_policy.arn
}

resource "aws_iam_instance_profile" "ecs_instance_profile" {
  name = "ecs_instance_profile"
  role = aws_iam_role.ecs_instance_role.name
}

# --- Template de lancement pour EC2 ---
resource "aws_launch_template" "ecs_instance" {
  name_prefix   = "ecs-instance"
  image_id      = "ami-0e9323ca501858b1c"
  instance_type = "t3.micro"

  iam_instance_profile {
    name = aws_iam_instance_profile.ecs_instance_profile.name
  }

  user_data = base64encode(<<-EOF
    #!/bin/bash
    echo "ECS_CLUSTER=myreport-cluster" >> /etc/ecs/ecs.config
  EOF
  )

  network_interfaces {
    associate_public_ip_address = true
    security_groups             = [aws_security_group.ecs_sg.id]
  }
}

# --- Groupe de mise à l'échelle automatique ECS ---
resource "aws_autoscaling_group" "ecs_asg" {
  desired_capacity     = 1
  max_size             = 3
  min_size             = 1
  vpc_zone_identifier  = [aws_subnet.public.id]

  launch_template {
    id      = aws_launch_template.ecs_instance.id
    version = "$Latest"
  }
}

# --- ECS Task Definition ---
resource "aws_ecs_task_definition" "myreport" {
  family                   = "myreport-task"
  network_mode             = "bridge"
  requires_compatibilities = ["EC2"]
  cpu                      = "256"
  memory                   = "512"

  container_definitions = jsonencode([
    {
      name      = "php-server"
      image     = "471286450931.dkr.ecr.eu-west-3.amazonaws.com/myreport-repo:latest"
      essential = true
      environment = [
        { name = "APP_NAME", value = "MyReport" },
        { name = "APP_DB_HOSTNAME", value = "10.0.1.1" },
        { name = "APP_DB_PORT", value = "3404" },
        { name = "APP_DB_NAME", value = "myreport" },
        { name = "APP_DB_USERNAME", value = "root" },
        { name = "APP_DB_PASSWORD", value = "root" }
      ]
      port_mappings = [
        {
          containerPort = 80
          hostPort      = 9090
        }
      ]
    }
  ])
}

# --- ECS Service ---
resource "aws_ecs_service" "php_service" {
  name            = "myreport-service"
  cluster         = aws_ecs_cluster.main.id
  task_definition = aws_ecs_task_definition.myreport.arn
  desired_count   = 1
  launch_type     = "EC2"
}
