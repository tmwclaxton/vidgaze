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
