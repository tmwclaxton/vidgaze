# output variable definitions



output "ecr_repository_arn" {
  value = aws_ecr_repository.vidgaze_ecr_repository.arn
}

# output the app runner service arn
output "app_runner_service_arn" {
  value = aws_apprunner_service.vidgaze_apprunner_service.arn
}

# output the app runner service url
output "app_runner_service_url" {
  value = aws_apprunner_service.vidgaze_apprunner_service.service_url
}
