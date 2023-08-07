# IAM Role for GitHub Actions (Assume this role in your GitHub Actions workflow)
resource "aws_iam_role" "github_actions_role" {
  name = "vidgaze-github-action-ecr-role"

  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action = "sts:AssumeRole"
        Effect = "Allow"
        Principal = {
          Service = "codebuild.amazonaws.com" # GitHub Actions uses CodeBuild service
        }
      }
    ]
  })
}

# IAM Policy for GitHub Actions to push to ECR
resource "aws_iam_policy" "github_actions_ecr_policy" {
  name = "github-actions-ecr-policy"

  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action   = "ecr:GetAuthorizationToken"
        Effect   = "Allow"
        Resource = "*"
      },
      {
        Action   = "ecr:BatchCheckLayerAvailability"
        Effect   = "Allow"
        Resource = "*"
      },
      {
        Action   = "ecr:GetDownloadUrlForLayer"
        Effect   = "Allow"
        Resource = "*"
      },
      {
        Action   = "ecr:PutImage"
        Effect   = "Allow"
        Resource = aws_ecr_repository.vidgaze_ecr_repository.arn
      }
    ]
  })
}

# Attach the policy to the GitHub Actions role
resource "aws_iam_role_policy_attachment" "github_actions_ecr_attachment" {
  policy_arn = aws_iam_policy.github_actions_ecr_policy.arn
  role       = aws_iam_role.github_actions_role.name
}



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
