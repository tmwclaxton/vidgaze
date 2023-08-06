# output variable definitions

output "ecr_repository_arn" {
    value = aws_ecr_repository.my_ecr_repository.arn
}
