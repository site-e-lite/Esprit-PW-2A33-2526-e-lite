<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $target = 'uploads/profile_pictures/test_' . time() . '.jpg';
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        echo "OK: file saved to $target";
    } else {
        echo "FAILED: move_uploaded_file returned false. Check permissions.";
    }
    exit;
}
?>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="file">
    <button type="submit">Upload test</button>
</form>
