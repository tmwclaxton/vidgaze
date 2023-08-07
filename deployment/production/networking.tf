# Create a VPC
module "vpc" {
  source             = "terraform-aws-modules/vpc/aws"
  name               = "vidgaze-vpc"
  cidr               = "10.0.0.0/16"
  azs                = ["eu-west-1a", "eu-west-1b", "eu-west-1c"]
  private_subnets    = ["10.0.0.0/24", "10.0.1.0/24"] # Two private subnets with /24 CIDR
  public_subnets     = ["10.0.2.0/24", "10.0.3.0/24"] # Two public subnets with /24 CIDR
  enable_nat_gateway = true
  enable_vpn_gateway = true
}

output "vpc_id" {
  value = module.vpc.vpc_id
}

output "public_subnets" {
  value = module.vpc.public_subnets
}
