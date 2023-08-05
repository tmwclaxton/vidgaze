terraform {
    backend "s3" {
        bucket = "terraform-state-laravel"
        key    = "terraform.tfstate"
        region = "us-east-1"
    }

    required_providers {
        aws = {
            source  = "hashicorp/aws"
            version = "~> 3.0"
        }
    }
}



provider "aws" {
    region  = var.aws_region

    # You can use access keys exported in shell
    access_key = var.aws_access_key
    secret_key = var.aws_secret_key

    # Or specify an aws profile, instead.
    # profile = "<aws profile>"
}
