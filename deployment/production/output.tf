# output variable definitions



output "ecr_repository_arn" {
  value = aws_ecr_repository.vidgaze_ecr_repository.arn
}

# output the app runner service arn
output "app_runner_service_arn" {
  value = aws_apprunner_service.vidgaze_apprunner_service[0].arn ? aws_apprunner_service.vidgaze_apprunner_service[0].arn : ""
}

# output the app runner service url
output "app_runner_service_url" {
  value = aws_apprunner_service.vidgaze_apprunner_service[0].service_url ? aws_apprunner_service.vidgaze_apprunner_service[0].service_url : ""
}

# output rds instance endpoint
output "rds_instance_endpoint" {
  value = aws_db_instance.vidgaze_db.endpoint
}
