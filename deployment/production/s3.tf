# S3 bucket for profile pictures
resource "aws_s3_bucket" "vidgaze_profile_pictures" {
    bucket = "vidgaze-profile-pictures-bucket"
    aws_s3 = "private"
}

# S3 bucket for profile banners
resource "aws_s3_bucket" "vidgaze_profile_banners" {
    bucket = "vidgaze-profile-banners-bucket"
    aws_s3 = "private"
}

# S3 bucket for videos
resource "aws_s3_bucket" "vidgaze_videos" {
    bucket = "vidgaze-videos-bucket"
    aws_s3 = "private"
}
