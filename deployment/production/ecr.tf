# Create an ECR repository and make it private
resource "aws_ecr_repository" "vidgaze_ecr_repository" {
  name                 = "vidgaze-laravel-app"
  image_tag_mutability = "MUTABLE"

  image_scanning_configuration {
    scan_on_push = true
  }

}

# Define the ECR repository lifecycle policy
resource "aws_ecr_lifecycle_policy" "my_ecr_lifecycle_policy" {
  depends_on = [aws_ecr_repository.vidgaze_ecr_repository]
  repository = aws_ecr_repository.vidgaze_ecr_repository.name
  policy = jsonencode({
    rules = [
      {
        rulePriority = 1,
        description  = "Keep last 10 images",
        selection = {
          tagStatus     = "tagged",
          tagPrefixList = ["v"],
          countType     = "imageCountMoreThan",
          countNumber   = 10
        },
        action = {
          type = "expire"
        }
      }
    ]
  })

}
