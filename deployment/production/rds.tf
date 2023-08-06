# set up rds mysql server
resource "aws_db_instance" "vidgaze_db" {
    identifier = "vidgaze-db"
    instance_class            = "db.t2.micro"
    allocated_storage         = 20 # GB
    storage_type              = "gp2" # General Purpose SSD
    engine                    = "mysql"
    engine_version            = "5.7"
    username                  = var.db_username
    password                  = var.db_password
    publicly_accessible       = true
    allow_major_version_upgrade = true

    # Enable automated backups with a retention period of 7 days
    backup_retention_period   = 7

    # Enable manual snapshots by setting the snapshot_identifier
    snapshot_identifier       = "vidgaze-db-snapshot"

    # use publicly accessible subnet group
    vpc_security_group_ids    = module.vpc.public_subnets
    db_subnet_group_name = "vidgaze-db-subnet-group"

    # final snapshot name, when db is deleted
    final_snapshot_identifier = "vidgaze-db-final-snapshot"

    apply_immediately = true
}
