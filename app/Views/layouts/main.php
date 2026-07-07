<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Home | Group Yupana</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="group yupana - Admin & Dashboard Template">
    <meta name="keywords" content="group yupana, admin, dashboard, bootstrap, template, responsive, css, sass, html, theme, front-end, ui kit, web">
    <meta name="author" content="Coderthemes">

    <!-- App favicon -->
    <link rel="shortcut icon" href="<?= base_url() ?>assets/images/favicon.ico">

    <?= $this->renderSection('styles') ?>

    <!-- Theme Config Js -->
    <script src="<?= base_url() ?>assets/js/config.js"></script>

    <!-- Vendor css -->
    <link href="<?= base_url() ?>assets/css/vendors.min.css" rel="stylesheet" type="text/css">

    <!-- App css -->
    <link href="<?= base_url() ?>assets/css/app.min.css" rel="stylesheet" type="text/css">

    <script src="<?= base_url() ?>assets/plugins/lucide/lucide.min.js"></script>

    <?= $this->renderSection('styles') ?>

</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        
        <?= $this->include('layouts/header') ?>

        <!-- Sidenav Menu Start -->
        <?= $this->include('layouts/menu') ?>
        <!-- Sidenav Menu End -->


        <!-- ============================================================== -->
        <!-- Start Main Content -->
        <!-- ============================================================== -->

        <div class="content-page">

            <div class="container-fluid">

                <?= $this->renderSection('content') ?>

            </div>
            <!-- container -->

            <!-- Footer Start -->
            <?= $this->include('layouts/footer') ?>
            <!-- end Footer -->

        </div>

        <!-- ============================================================== -->
        <!-- End of Main Content -->
        <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->

    <!-- Vendor js -->
    <script src="<?= base_url() ?>assets/js/vendors.min.js"></script>

    <!-- App js -->
    <script src="<?= base_url() ?>assets/js/app.js"></script>

    <?= $this->renderSection('scripts') ?>

    <?= $this->renderSection('scripts') ?>

</body>

</html>