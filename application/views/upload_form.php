<html>
<head>
<title>Upload Form</title>
</head>
<body>

<?php echo $error;?>

<form action="/index.php/welcome/do_upload" enctype="multipart/form-data" method="post" accept-charset="utf-8">

<input type="file" name="userfile" size="20" />

<br /><br />

<input type="submit" value="upload" />

</form>

</body>
</html>