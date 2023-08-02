# IAM Role for GitHub Actions (Assume this role in your GitHub Actions workflow)
resource "aws_iam_role" "github_actions_role" {
    name = "my-github-actions-role"

    assume_role_policy = jsonencode({
        Version = "2012-10-17"
        Statement = [
            {
                Action = "sts:AssumeRole"
                Effect = "Allow"
                Principal = {
                    Service = "codebuild.amazonaws.com"  # GitHub Actions uses CodeBuild service
                }
            }
        ]
    })
}

# IAM Policy for GitHub Actions to push to ECR

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
                Resource = aws_ecr_repository.my_ecr_repository.arn
            }
        ]
    })
}

# Attach the policy to the GitHub Actions role
resource "aws_iam_role_policy_attachment" "github_actions_ecr_attachment" {
    policy_arn = aws_iam_policy.github_actions_ecr_policy.arn
    role       = aws_iam_role.github_actions_role.name
}

# IAM Role for AppRunner
resource "aws_iam_role" "apprunner_role" {
    name = "my-apprunner-role"

    assume_role_policy = jsonencode({
        Version = "2012-10-17"
        Statement = [
            {
                Action = "sts:AssumeRole"
                Effect = "Allow"
                Principal = {
                    Service = "build.apprunner.amazonaws.com"
                }
            }
        ]
    })
}

# IAM Policy for AppRunner to pull from ECR
resource "aws_iam_policy" "apprunner_ecr_policy" {
    name = "apprunner-ecr-policy"

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
                Action   = "ecr:GetRepositoryPolicy"
                Effect   = "Allow"
                Resource = aws_ecr_repository.my_ecr_repository.arn
            },
            {
                Action   = "ecr:DescribeRepositories"
                Effect   = "Allow"
                Resource = "*"
            },
            {
                Action   = "ecr:ListImages"
                Effect   = "Allow"
                Resource = aws_ecr_repository.my_ecr_repository.arn
            }
        ]
    })
}

# Attach the policy to the AppRunner role
resource "aws_iam_role_policy_attachment" "apprunner_ecr_attachment" {
    policy_arn = aws_iam_policy.apprunner_ecr_policy.arn
    role       = aws_iam_role.apprunner_role.name
}
