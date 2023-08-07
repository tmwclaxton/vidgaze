# Create a security group for the RDS instance (private)
resource "aws_security_group" "rds_sg" {
  name_prefix = "vidgaze-rds-sg"
  vpc_id      = module.vpc.vpc_id

  # Allow inbound traffic from the AppRunner service
  ingress {
    from_port       = 3306
    to_port         = 3306
    protocol        = "tcp"
    security_groups = [aws_security_group.vidgaze_app_runner_sg.id]
  }

  # Allow outbound traffic to the internet (for RDS updates, etc.)
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

# Create a subnet group for the rdbs instance (private)
resource "aws_db_subnet_group" "vidgaze_db_subnet_group" {
  name       = "vidgaze-db-subnet-group"
  subnet_ids = module.vpc.private_subnets
}

# set up rds mysql server
resource "aws_db_instance" "vidgaze_db" {
  identifier                  = "vidgaze-db"
  instance_class              = "db.t2.micro"
  allocated_storage           = 20    # GB
  storage_type                = "gp2" # General Purpose SSD
  engine                      = "mysql"
  engine_version              = "5.7"
  username                    = var.db_username
  password                    = var.db_password
  publicly_accessible         = false
  allow_major_version_upgrade = true

  # Enable automated backups with a retention period of 7 days
  backup_retention_period = 7

  # Enable manual snapshots by setting the snapshot_identifier
  #  snapshot_identifier = "vidgaze-db-snapshot" # if we fuck up, we can restore from this snapshot

  # vpc_security_group_ids = module.vpc.public_subnets # if you want to allow access from the internet
  # vpc_security_group_ids = module.vpc.private_subnets # if you want to allow access from the private subnets
  vpc_security_group_ids = [aws_security_group.rds_sg.id] # if you only want to allow access from the AppRunner service

  db_subnet_group_name = "vidgaze-db-subnet-group"

  # final snapshot name, when db is deleted
  final_snapshot_identifier = "vidgaze-db-final-snapshot"

  apply_immediately = true
}

