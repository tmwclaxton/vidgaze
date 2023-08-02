
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
    service_name = "my-laravel-app"
    source_configuration {
        authentication_configuration {
            access_role_arn = aws_iam_role.my_apprunner_role.arn
        }
        auto_deployments_enabled = true
        code_repository {
            repository_url = aws_codecommit_repository.my_codecommit_repository.clone_url_http
            source_code_version {
                type  = ""
                value = ""
            }
        }
        image_repository {
            image_identifier = aws_ecr_repository.my_ecr_repository.repository_url
            image_configuration {
                port = "80"
            }
            image_repository_type = ""
        }
    }
}



