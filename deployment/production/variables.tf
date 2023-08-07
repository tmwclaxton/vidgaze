
variable "aws_region" {
  description = "AWS Region"
  type        = string
}

variable "create_apprunner_service" {
  description = "Build App Runner"
  type        = bool
}

variable "db_password" {
  description = "Database password"
  type        = string
}

variable "db_username" {
  description = "Database username"
  type        = string
}
