provider "aws" {
    // London
    region = "eu-west-2"
}

# Create a VPC
resource "aws_vpc" "my_vpc" {
    cidr_block = "10.0.0.0/16"  # Replace this with your desired IP range for the VPC
}

# Create public and private subnets within the VPC
resource "aws_subnet" "public_subnet" {
    vpc_id            = aws_vpc.my_vpc.id
    cidr_block        = "10.0.1.0/24"  # Replace this with a unique CIDR block for the public subnet
    map_public_ip_on_launch = true
}

resource "aws_subnet" "private_subnet" {
    vpc_id            = aws_vpc.my_vpc.id
    cidr_block        = "10.0.2.0/24"  # Replace this with a unique CIDR block for the private subnet
}

# Create a security group for the AppRunner service (public)
resource "aws_security_group" "app_runner_sg" {
    name_prefix = "my-laravel-apprunner-sg"
    vpc_id      = aws_vpc.my_vpc.id

    # Allow inbound HTTP traffic from the internet (GitHub Actions)
    ingress {
        from_port   = 80
        to_port     = 80
        protocol    = "tcp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    # Allow outbound traffic to the internet
    egress {
        from_port   = 0
        to_port     = 0
        protocol    = "-1"
        cidr_blocks = ["0.0.0.0/0"]
    }
}

# Create a security group for the RDS instance (private)
resource "aws_security_group" "rds_sg" {
    name_prefix = "my-laravel-rds-sg"
    vpc_id      = aws_vpc.my_vpc.id

    # Allow inbound traffic from the AppRunner service
    ingress {
        from_port   = 3306
        to_port     = 3306
        protocol    = "tcp"
        security_groups = [aws_security_group.app_runner_sg.id]
    }

    # Allow outbound traffic to the internet (for RDS updates, etc.)
    egress {
        from_port   = 0
        to_port     = 0
        protocol    = "-1"
        cidr_blocks = ["0.0.0.0/0"]
    }
}

# Create a security group for the Redis ElastiCache cluster (private)
resource "aws_security_group" "redis_sg" {
    name_prefix = "my-laravel-redis-sg"
    vpc_id      = aws_vpc.my_vpc.id

    # Allow inbound traffic from the AppRunner service
    ingress {
        from_port   = 6379
        to_port     = 6379
        protocol    = "tcp"
        security_groups = [aws_security_group.app_runner_sg.id]
    }

    # Allow outbound traffic to the internet (for Redis updates, etc.)
    egress {
        from_port   = 0
        to_port     = 0
        protocol    = "-1"
        cidr_blocks = ["0.0.0.0/0"]
    }
}

# Create an ECR repository and make it public
resource "aws_ecr_repository" "my_ecr_repository" {
    name = "my-laravel-app"

    # Make the repository public
    image_tag_mutability = "MUTABLE"
    lifecycle_policy {
        lifecycle_policy_text = jsonencode({
            rules = [{
                rule_priority = 1
                selection = {
                    tag_status = "any"
                }
                action = {
                    type = "expire"
                }
            }]
        })
    }
    policy = jsonencode({
        Version = "2012-10-17"
        Statement = [{
            Sid       = "PublicAccess"
            Effect    = "Allow"
            Principal = "*"
            Action    = ["ecr:GetDownloadUrlForLayer", "ecr:BatchGetImage", "ecr:GetAuthorizationToken"]
        }]
    })
}

# Create an RDS MySQL instance in the private subnet
resource "aws_db_subnet_group" "my_db_subnet_group" {
    name       = "my-laravel-db-subnet-group"
    subnet_ids = [aws_subnet.private_subnet.id]
}

resource "aws_db_instance" "my_rds_instance" {
    identifier            = "my-laravel-db"
    engine                = "mysql"
    instance_class        = "db.t2.micro"
    username              = "laravel_user"
    password              = "your_laravel_db_password"
    allocated_storage     = 20
    storage_type          = "gp2"
    engine_version        = "5.7"
    multi_az              = false
    vpc_security_group_ids = [aws_security_group.rds_sg.id]
    subnet_group_name      = aws_db_subnet_group.my_db_subnet_group.name
}

# Create an S3 bucket
resource "aws_s3_bucket" "my_s3_bucket" {
    bucket = "my-laravel-bucket"
    acl    = "private"
}

# Create a Redis ElastiCache cluster in the private subnet
resource "aws_elasticache_subnet_group" "my_redis_subnet_group" {
    name       = "my-laravel-redis-subnet-group"
    subnet_ids = [aws_subnet.private_subnet.id]
}

resource "aws_elasticache_cluster" "my_redis_cluster" {
    cluster_id           = "my-laravel-redis"
    engine               = "redis"
    node_type            = "cache.t2.micro"
    num_cache_nodes      = 1
    parameter_group_name = "default.redis5.0"
    security_group_ids   = [aws_security_group.redis_sg.id]
    subnet_group_name    = aws_elasticache_subnet_group.my_redis_subnet_group.name
}

# Create an AppRunner service in the public subnet
resource "aws_apprunner_service" "my_apprunner_service" {
    name        = "my-laravel-apprunner"
    role_arn    = aws_iam_role.my_apprunner_role.arn
    vpc_configuration {
        subnet_ids = [aws_subnet.public_subnet.id]
        security_group_ids = [aws_security_group.app_runner_sg.id]
    }
    source_code {
        image_repository_type = "ECR"
        image_uri              = aws_ecr_repository.my_ecr_repository.repository_url
    }
}

# IAM Role for GitHub Actions (Assume this role in your GitHub Actions workflow)
resource "aws_iam_role" "github_actions_role" {
    name = "my-github-actions-role"

    assume_role_policy = jsonencode({
        Version = "2012-10-17"
        Statement = [
            {
                Action = "sts:AssumeRole"
                Effect = "Allow"
                Principal = {
                    Service = "codebuild.amazonaws.com"  # GitHub Actions uses CodeBuild service
                }
            }
        ]
    })
}

# IAM Policy for GitHub Actions to push to ECR

# IAM Policy for GitHub Actions to push to ECR
resource "aws_iam_policy" "github_actions_ecr_policy" {
    name = "github-actions-ecr-policy"

    policy = jsonencode({
        Version = "2012-10-17"
        Statement = [
            {
                Action   = "ecr:GetAuthorizationToken"
                Effect   = "Allow"
                Resource = "*"
            },
            {
                Action   = "ecr:BatchCheckLayerAvailability"
                Effect   = "Allow"
                Resource = "*"
            },
            {
                Action   = "ecr:GetDownloadUrlForLayer"
                Effect   = "Allow"
                Resource = "*"
            },
            {
                Action   = "ecr:PutImage"
                Effect   = "Allow"
                Resource = aws_ecr_repository.my_ecr_repository.arn
            }
        ]
    })
}

# Attach the policy to the GitHub Actions role
resource "aws_iam_role_policy_attachment" "github_actions_ecr_attachment" {
    policy_arn = aws_iam_policy.github_actions_ecr_policy.arn
    role       = aws_iam_role.github_actions_role.name
}

# IAM Role for AppRunner
resource "aws_iam_role" "apprunner_role" {
    name = "my-apprunner-role"

    assume_role_policy = jsonencode({
        Version = "2012-10-17"
        Statement = [
            {
                Action = "sts:AssumeRole"
                Effect = "Allow"
                Principal = {
                    Service = "build.apprunner.amazonaws.com"
                }
            }
        ]
    })
}

# IAM Policy for AppRunner to pull from ECR
resource "aws_iam_policy" "apprunner_ecr_policy" {
    name = "apprunner-ecr-policy"

    policy = jsonencode({
        Version = "2012-10-17"
        Statement = [
            {
                Action   = "ecr:GetAuthorizationToken"
                Effect   = "Allow"
                Resource = "*"
            },
            {
                Action   = "ecr:BatchCheckLayerAvailability"
                Effect   = "Allow"
                Resource = "*"
            },
            {
                Action   = "ecr:GetDownloadUrlForLayer"
                Effect   = "Allow"
                Resource = "*"
            },
            {
                Action   = "ecr:GetRepositoryPolicy"
                Effect   = "Allow"
                Resource = aws_ecr_repository.my_ecr_repository.arn
            },
            {
                Action   = "ecr:DescribeRepositories"
                Effect   = "Allow"
                Resource = "*"
            },
            {
                Action   = "ecr:ListImages"
                Effect   = "Allow"
                Resource = aws_ecr_repository.my_ecr_repository.arn
            }
        ]
    })
}

# Attach the policy to the AppRunner role
resource "aws_iam_role_policy_attachment" "apprunner_ecr_attachment" {
    policy_arn = aws_iam_policy.apprunner_ecr_policy.arn
    role       = aws_iam_role.apprunner_role.name
}


