
# Create a VPC
resource "aws_vpc" "my_vpc" {
    cidr_block = "10.0.0.0/16"  # Replace this with your desired IP range for the VPC
}

# Create public and private subnets within the VPC
resource "aws_subnet" "public_subnet" {
    vpc_id            = aws_vpc.my_vpc.id
    cidr_block        = "10.0.1.0/24"  # Replace this with a unique CIDR block for the public subnet
    map_public_ip_on_launch = true
}

resource "aws_subnet" "private_subnet" {
    vpc_id            = aws_vpc.my_vpc.id
    cidr_block        = "10.0.2.0/24"  # Replace this with a unique CIDR block for the private subnet
}

# Create an RDS MySQL instance in the private subnet
resource "aws_db_subnet_group" "my_db_subnet_group" {
    name       = "my-laravel-db-subnet-group"
    subnet_ids = [aws_subnet.private_subnet.id]
}
