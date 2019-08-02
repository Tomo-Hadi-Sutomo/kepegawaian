<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>sicawan</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="<?php echo site_url('resources/css/bootstrap.min.css'); ?>">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo site_url('resources/css/font-awesome.min.css'); ?>">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Datetimepicker -->
    <link rel="stylesheet" href="<?php echo site_url('resources/css/bootstrap-datetimepicker.min.css'); ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo site_url('resources/css/AdminLTE.min.css'); ?>">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
		 folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="<?php echo site_url('resources/css/_all-skins.min.css'); ?>">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <header class="main-header">
        <!-- Logo -->
        <a href="" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini">SN</span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg">SIM CAWAN</span> </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button"> <span
                        class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span
                        class="icon-bar"></span> <span class="icon-bar"></span> </a>
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <img
                                    src="<?php echo site_url('resources/img/user.png'); ?>" class="user-image"
                                    alt="User Image"> <span class="hidden-xs">Online</span> </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="<?php echo site_url('resources/img/user.png'); ?>" class="img-circle"
                                     alt="User Image">
                                <p>
                                    Pratama Studio - Web App Developer
                                    <small>Since, 2018</small>
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="#" class="btn btn-default btn-flat">Profile</a>
                                </div>
                                <div class="pull-right">
                                    <a href="#" class="btn btn-default btn-flat">Sign out</a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="<?php echo site_url('resources/img/user.png'); ?>" class="img-circle"
                         alt="User Image">
                </div>
                <div class="pull-left info">
                    <p>Pratama Studio</p>
                    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
            </div>
            <!-- sidebar menu: : style can be found in sidebar.less -->
            <ul class="sidebar-menu">
                <li class="header">MAIN NAVIGATION</li>
                <li>
                    <a href="<?php echo site_url(); ?>"> <i class="fa fa-dashboard"></i> <span>Dashboard</span> </a>
                </li>
                <li>
                    <a href="#"> <i class="fa fa-desktop"></i> <span>Admin</span> </a>
                    <ul class="treeview-menu">
                        <li class="active">
                            <a href="<?php echo site_url('admin/add'); ?>"><i class="fa fa-plus"></i> Add</a>
                        </li>
                        <li>
                            <a href="<?php echo site_url('admin/index'); ?>"><i class="fa fa-list-ul"></i> Listing</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#"> <i class="fa fa-desktop"></i> <span>Berka</span> </a>
                    <ul class="treeview-menu">
                        <li class="active">
                            <a href="<?php echo site_url('berka/add'); ?>"><i class="fa fa-plus"></i> Add</a>
                        </li>
                        <li>
                            <a href="<?php echo site_url('berka/index'); ?>"><i class="fa fa-list-ul"></i> Listing</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#"> <i class="fa fa-desktop"></i> <span>Nilai Berka</span> </a>
                    <ul class="treeview-menu">
                        <li class="active">
                            <a href="<?php echo site_url('nilai_berka/add'); ?>"><i class="fa fa-plus"></i> Add</a>
                        </li>
                        <li>
                            <a href="<?php echo site_url('nilai_berka/index'); ?>"><i class="fa fa-list-ul"></i> Listing</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#"> <i class="fa fa-desktop"></i> <span>Nilai Pengetahuan</span> </a>
                    <ul class="treeview-menu">
                        <li class="active">
                            <a href="<?php echo site_url('nilai_pengetahuan/add'); ?>"><i
                                        class="fa fa-plus"></i> Add</a>
                        </li>
                        <li>
                            <a href="<?php echo site_url('nilai_pengetahuan/index'); ?>"><i
                                        class="fa fa-list-ul"></i> Listing</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#"> <i class="fa fa-desktop"></i> <span>Nilai Psikote</span> </a>
                    <ul class="treeview-menu">
                        <li class="active">
                            <a href="<?php echo site_url('nilai_psikote/add'); ?>"><i class="fa fa-plus"></i> Add</a>
                        </li>
                        <li>
                            <a href="<?php echo site_url('nilai_psikote/index'); ?>"><i
                                        class="fa fa-list-ul"></i> Listing</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#"> <i class="fa fa-desktop"></i> <span>Pendaftaran</span> </a>
                    <ul class="treeview-menu">
                        <li class="active">
                            <a href="<?php echo site_url('pendaftaran/add'); ?>"><i class="fa fa-plus"></i> Add</a>
                        </li>
                        <li>
                            <a href="<?php echo site_url('pendaftaran/index'); ?>"><i class="fa fa-list-ul"></i> Listing</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#"> <i class="fa fa-desktop"></i> <span>Pengetahuan</span> </a>
                    <ul class="treeview-menu">
                        <li class="active">
                            <a href="<?php echo site_url('pengetahuan/add'); ?>"><i class="fa fa-plus"></i> Add</a>
                        </li>
                        <li>
                            <a href="<?php echo site_url('pengetahuan/index'); ?>"><i class="fa fa-list-ul"></i> Listing</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#"> <i class="fa fa-desktop"></i> <span>Pengumuman</span> </a>
                    <ul class="treeview-menu">
                        <li class="active">
                            <a href="<?php echo site_url('pengumuman/add'); ?>"><i class="fa fa-plus"></i> Add</a>
                        </li>
                        <li>
                            <a href="<?php echo site_url('pengumuman/index'); ?>"><i class="fa fa-list-ul"></i> Listing</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#"> <i class="fa fa-desktop"></i> <span>Pengumuman Berka</span> </a>
                    <ul class="treeview-menu">
                        <li class="active">
                            <a href="<?php echo site_url('pengumuman_berka/add'); ?>"><i class="fa fa-plus"></i> Add</a>
                        </li>
                        <li>
                            <a href="<?php echo site_url('pengumuman_berka/index'); ?>"><i
                                        class="fa fa-list-ul"></i> Listing</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#"> <i class="fa fa-desktop"></i> <span>Psikote</span> </a>
                    <ul class="treeview-menu">
                        <li class="active">
                            <a href="<?php echo site_url('psikote/add'); ?>"><i class="fa fa-plus"></i> Add</a>
                        </li>
                        <li>
                            <a href="<?php echo site_url('psikote/index'); ?>"><i class="fa fa-list-ul"></i> Listing</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#"> <i class="fa fa-desktop"></i> <span>User</span> </a>
                    <ul class="treeview-menu">
                        <li class="active">
                            <a href="<?php echo site_url('user/add'); ?>"><i class="fa fa-plus"></i> Add</a>
                        </li>
                        <li>
                            <a href="<?php echo site_url('user/index'); ?>"><i class="fa fa-list-ul"></i> Listing</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </section>
        <!-- /.sidebar -->
    </aside>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Main content -->
        <section class="content">
			<?php
			if(isset($_view) && $_view) $this->load->view($_view);
			?>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <footer class="main-footer">
        <strong>Powered By <a href="https://pratama.id/">PRATAMA STUDIO</a></strong>
    </footer>
    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Create the tabs -->
        <ul class="nav nav-tabs nav-justified control-sidebar-tabs"></ul>
        <!-- Tab panes -->
        <div class="tab-content">
            <!-- Home tab content -->
            <div class="tab-pane" id="control-sidebar-home-tab"></div>
            <!-- /.tab-pane -->
            <!-- Stats tab content -->
            <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div>
            <!-- /.tab-pane -->
        </div>
    </aside>
    <!-- /.control-sidebar -->
    <!-- Add the sidebar's background. This div must be placed
	immediately after the control sidebar -->
    <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
<!-- jQuery 2.2.3 -->
<script src="<?php echo site_url('resources/js/jquery-2.2.3.min.js'); ?>"></script>
<!-- Bootstrap 3.3.6 -->
<script src="<?php echo site_url('resources/js/bootstrap.min.js'); ?>"></script>
<!-- FastClick -->
<script src="<?php echo site_url('resources/js/fastclick.js'); ?>"></script>
<!-- AdminLTE App -->
<script src="<?php echo site_url('resources/js/app.min.js'); ?>"></script>
<!-- AdminLTE for demo purposes -->
<script src="<?php echo site_url('resources/js/demo.js'); ?>"></script>
<!-- DatePicker -->
<script src="<?php echo site_url('resources/js/moment.js'); ?>"></script>
<script src="<?php echo site_url('resources/js/bootstrap-datetimepicker.min.js'); ?>"></script>
<script src="<?php echo site_url('resources/js/global.js'); ?>"></script>
</body>
</html>
