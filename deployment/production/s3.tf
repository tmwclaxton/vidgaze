# Define a map with the bucket names as keys and corresponding resource blocks
locals {
    buckets = {
        "vidgaze-profile-pictures-bucket" = "vidgaze_profile_pictures",
        "vidgaze-profile-banners-bucket"  = "vidgaze_profile_banners",
        "vidgaze-videos-bucket"           = "vidgaze_videos"
    }
}

# Create the S3 buckets using a for_each block to iterate over the local map
resource "aws_s3_bucket" "s3_buckets" {
    for_each = local.buckets
    bucket = each.key
}

# To reference the individual S3 buckets, you can use the resource blocks like this:
# aws_s3_bucket.s3_buckets["vidgaze-profile-pictures-bucket"]
# aws_s3_bucket.s3_buckets["vidgaze-profile-banners-bucket"]
# aws_s3_bucket.s3_buckets["vidgaze-videos-bucket"]

# Add bucket policy to allow public read access
resource "aws_s3_bucket_policy" "s3_bucket_policies" {
    for_each = local.buckets

    bucket = each.key

    policy = jsonencode({
        Version = "2012-10-17"
        Statement = [
            {
                Sid = "PublicReadGetObject"
                Effect = "Allow"
                Principal = "*"
                Action = "s3:GetObject"
                Resource = "arn:aws:s3:::${each.key}/*"
            },
        ]
    })
}
