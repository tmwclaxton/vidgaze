<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Request from {{$data['name']}}</title>
</head>
<body>
<h1>Support Request</h1>

<p>Email: {{ $data['email'] }}</p>
<p>Subject: {{ $data['subject'] }}</p>

<hr>

<p>Message:</p>
<p>{{ $data['message'] }}</p>

</body>
</html>
