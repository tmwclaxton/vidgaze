
variable "aws_region" {
  default     = "eu-west-1" // Ireland
  description = "AWS Region"
  type        = string
}

variable "create_apprunner_service" {
  default     = false
  description = "Build App Runner"
  type        = bool
}

variable "db_username" {
  description = "Database username"
  type        = string
}

variable "db_password" {
  description = "Database password"
  type        = string
}
