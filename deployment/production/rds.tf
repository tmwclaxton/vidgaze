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

#
## Create a CloudWatch Alarm to monitor RDS storage space
#resource "aws_cloudwatch_metric_alarm" "rds_storage_alarm" {
#    comparison_operator = "LessThanOrEqualToThreshold"
#    evaluation_periods  = "1"
#    metric_name         = "FreeStorageSpace"
#    namespace           = "AWS/RDS"
#    period              = "300" # 5 minutes
#
#    dimensions = {
#        DBInstanceIdentifier = aws_db_instance.vidgaze_db.identifier
#    }
#
#    statistic   = "Average"
#    threshold   = "5000000000" # 5 GB (adjust the threshold based on your needs)
#    alarm_description = "RDS Storage Space Low"
#    alarm_name  = "RDS_Storage_Low_Alarm"
#    alarm_actions = [var.notification_topic_arn] # Replace with your SNS topic ARN for receiving notifications
#}
