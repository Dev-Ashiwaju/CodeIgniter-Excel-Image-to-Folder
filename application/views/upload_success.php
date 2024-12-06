<!DOCTYPE html>
<html>
<head>
    <title>Upload Success</title>
</head>
<body>
    <h1>Excel File Uploaded and Processed Successfully!</h1>

    <h2>Data Uploaded:</h2>
    <table border="1">
        <thead>
            <tr>
                <th>S.No</th>
                <th>Name</th>
                <th>Image</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
            <tr>
                <td><?php echo $row['sno']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td>
                    <img src="<?php echo base_url($row['image']); ?>" width="100" height="100">
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="<?php echo base_url('FileUploadController/upload_form'); ?>">Upload Another File</a></p>
</body>
</html>
