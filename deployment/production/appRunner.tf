
# define app runner role
resource "aws_iam_role" "vidgaze-apprunner-role" {
  name = "app-runner-role"

  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Effect = "Allow"
        Principal = {
          Service = "build.apprunner.amazonaws.com"
        }
        Action = "sts:AssumeRole"
      }
    ]
  })

}

# policy for app runner role
resource "aws_iam_role_policy" "app_runner_policy" {
  name = "app-runner-policy"
  role = aws_iam_role.vidgaze-apprunner-role.id

  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [{
      Effect = "Allow"
      Action = [
        "ecr:GetAuthorizationToken",
        "ecr:BatchCheckLayerAvailability",
        "ecr:GetDownloadUrlForLayer",
        "ecr:GetRepositoryPolicy",
        "ecr:DescribeRepositories",
        "ecr:ListImages",
        "ecr:DescribeImages",
        "ecr:BatchGetImage",
        "logs:CreateLogStream",
        "logs:PutLogEvents"
      ]
      Resource = "*"
    }]
  })
}


# Create a security group for the AppRunner service (public)
resource "aws_security_group" "vidgaze_app_runner_sg" {
  name_prefix = "vidgaze-apprunner-sg-"
  vpc_id      = module.vpc.vpc_id

  # Allow inbound HTTPS traffic from the internet (AppRunner)
  ingress {
    from_port   = 443
    to_port     = 443
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

resource "aws_apprunner_vpc_connector" "connector" {
  vpc_connector_name = "vpc-connector"
  subnets            = module.vpc.public_subnets
  security_groups    = [aws_security_group.vidgaze_app_runner_sg.id]
}


# Conditionally create the AppRunner service only if the image exists
resource "aws_apprunner_service" "vidgaze_apprunner_service" {
    count = var.create_apprunner_service ? 1 : 0

  service_name = "vidgaze-apprunner-service"

  source_configuration {
    image_repository {
      image_configuration {
        port = "80"
      }
      image_identifier      = aws_ecr_repository.vidgaze_ecr_repository.repository_url
      image_repository_type = "ECR"
    }
    authentication_configuration {
      access_role_arn = aws_iam_role.vidgaze-apprunner-role.arn
    }
    auto_deployments_enabled = true
  }

  health_check_configuration {
    healthy_threshold   = 1
    interval            = 5
    path                = "/health/apprunner"
    protocol            = "HTTP"
    timeout             = 5
    unhealthy_threshold = 3
  }

  network_configuration {
    egress_configuration {
      egress_type       = "VPC"
      vpc_connector_arn = aws_apprunner_vpc_connector.connector.arn
    }
  }

  tags = {
    Terraform   = "true"
    Environment = "production"
  }
}
