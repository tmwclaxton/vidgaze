resource "aws_elasticache_parameter_group" "default" {
  name        = "redis-7-0"
  family      = "redis7"
  description = "Parameter group for Redis 7.0 Managed by Terraform"
}

# Create a security group for the Redis ElastiCache cluster (private)
resource "aws_security_group" "redis_sg" {
  name_prefix = "vidgaze-redis-sg"
  vpc_id      = module.vpc.vpc_id

  # Allow inbound traffic from the AppRunner service
  ingress {
    from_port       = 6379
    to_port         = 6379
    protocol        = "tcp"
    security_groups = [aws_security_group.vidgaze_app_runner_sg.id]
  }

  # Allow outbound traffic to the internet (for Redis updates, etc.)
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

# Create a subnet group for the Redis ElastiCache cluster (private)
resource "aws_elasticache_subnet_group" "vidgaze_redis_subnet_group" {
  name       = "vidgaze-redis-subnet-group"
  subnet_ids = module.vpc.private_subnets
}


# Create a Redis ElastiCache cluster in the private subnet with the redis security group and the subnet group created above
resource "aws_elasticache_cluster" "vidgaze_redis_cluster" {
  cluster_id           = "vidgaze-redis"
  engine               = "redis"
  node_type            = "cache.t2.micro"
  num_cache_nodes      = 1
  parameter_group_name = aws_elasticache_parameter_group.default.name
  security_group_ids   = [aws_security_group.redis_sg.id]
  subnet_group_name    = aws_elasticache_subnet_group.vidgaze_redis_subnet_group.name
}

