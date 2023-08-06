terraform {
  backend "s3" {
    bucket = "vidgaze-tfstate-prod"
    key    = "terraform.tfstate"
    region = "eu-west-2"
  }

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 3.0"
    }
  }
}

provider "aws" {
  region = var.aws_region

  # You can use access keys exported in shell
  #  access_key = var.aws_access_key
  #  secret_key = var.aws_secret_key

  # Or specify an aws profile, instead.
  # profile = "<aws profile>"
}
