<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($this->get('page_title', '')) ?></title>
    <meta name="description" content="<?= $this->e($this->get('page_description', ''), 'attr') ?>">

    <link rel="stylesheet" href="<?= $this->e($this->get('pff_path_css')) ?>main.css">
</head>

<body>
    <!-- Add your site or application content here -->
    <?php $this->content(0) ?>

    <script src="<?= $this->e($this->get('pff_path_js')) ?>main.js"></script>
</body>

</html>