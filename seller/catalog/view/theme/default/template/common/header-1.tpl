<!DOCTYPE html>
<!--[if IE]><![endif]-->
<!--[if IE 8 ]><html dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>" class="ie8"><![endif]-->
<!--[if IE 9 ]><html dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>" class="ie9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!-->
<html dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>">
<!--<![endif]-->
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?php echo $title; ?></title>
<base href="<?php echo $base; ?>" />
<?php if ($description) { ?>
<meta name="description" content="<?php echo $description; ?>" />
<?php } ?>
<?php if ($keywords) { ?>
<meta name="keywords" content= "<?php echo $keywords; ?>" />
<?php } ?>
<script src="catalog/view/javascript/jquery/jquery-2.1.1.min.js" type="text/javascript"></script>
<link href="catalog/view/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen" />
<script src="catalog/view/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<link href="catalog/view/javascript/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="//fonts.googleapis.com/css?family=Open+Sans:400,400i,300,700" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="catalog/view/javascript/AdminLTE.min.css">
<link rel="stylesheet" type="text/css" href="catalog/view/javascript/skins/_all-skins.min.css">
<link href="catalog/view/theme/default/stylesheet/stylesheet.css" rel="stylesheet">
<?php foreach ($styles as $style) { ?>
<link href="<?php echo $style['href']; ?>" type="text/css" rel="<?php echo $style['rel']; ?>" media="<?php echo $style['media']; ?>" />
<?php } ?>
<script src="catalog/view/javascript/common.js" type="text/javascript"></script>
<?php foreach ($links as $link) { ?>
<link href="<?php echo $link['href']; ?>" rel="<?php echo $link['rel']; ?>" />
<?php } ?>
<?php foreach ($scripts as $script) { ?>
<script src="<?php echo $script; ?>" type="text/javascript"></script>
<?php } ?>
<?php foreach ($analytics as $analytic) { ?>
<?php echo $analytic; ?>
<?php } ?>
<script>
$(document).ready(function() {
// Override summernotes image manager
	$('button[data-event=\'showImageDialog\']').attr('data-toggle', 'image').removeAttr('data-event');
	$(document).delegate('button[data-toggle=\'image\']', 'click', function() {
		$('#modal-image').remove();
		$(this).parents('.note-editor').find('.note-editable').focus();
		$.ajax({
			url: 'index.php?route=common/filemanager',
			dataType: 'html',
			beforeSend: function() {
				$('#button-image i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
				$('#button-image').prop('disabled', true);
			},
			complete: function() {
				$('#button-image i').replaceWith('<i class="fa fa-upload"></i>');
				$('#button-image').prop('disabled', false);
			},
			success: function(html) {
				$('body').append('<div id="modal-image" class="modal">' + html + '</div>');
				$('#modal-image').modal('show');
			}
		});	
	});
	// Image Manager
	$(document).delegate('a[data-toggle=\'image\']', 'click', function(e) {
		e.preventDefault();
		$('.popover').popover('hide', function() {
			$('.popover').remove();
		});
		var element = this;
		$(element).popover({
			html: true,
			placement: 'right',
			trigger: 'manual',
			content: function() {
				return '<button type="button" id="button-image" class="btn btn-primary"><i class="fa fa-pencil"></i></button> <button type="button" id="button-clear" style="display:none" class="btn btn-danger"><i class="fa fa-trash-o"></i></button>';
			}
		});
		$(element).popover('show');
		$('#button-image').on('click', function() {
			$('#modal-image').remove();
			$.ajax({
				url: 'index.php?route=common/filemanager&token=&target=' + $(element).parent().find('input').attr('id') + '&thumb=' + $(element).attr('id'),
				dataType: 'html',
				beforeSend: function() {
					$('#button-image i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
					$('#button-image').prop('disabled', true);
				},
				complete: function() {
					$('#button-image i').replaceWith('<i class="fa fa-pencil"></i>');
					$('#button-image').prop('disabled', false);
				},
				success: function(html) {
					$('body').append('<div id="modal-image" class="modal">' + html + '</div>');
					$('#modal-image').modal('show');
				}
			});
			$(element).popover('hide', function() {
				$('.popover').remove();
			});
		});		
		$('#button-clear').on('click', function() {
			$(element).find('img').attr('src', $(element).find('img').attr('data-placeholder'));
			$(element).parent().find('input').attr('value', '');
			$(element).popover('hide', function() {
				$('.popover').remove();
			});
		});
	});
	// tooltips on hover
	$('[data-toggle=\'tooltip\']').tooltip({container: 'body', html: true});
	// Makes tooltips work on ajax generated content
	$(document).ajaxStop(function() {
		$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});
	});
	// https://github.com/opencart/opencart/issues/2595
	$.event.special.remove = {
		remove: function(o) {
			if (o.handler) { 
				o.handler.apply(this, arguments);
			}
		}
	}
	$('[data-toggle=\'tooltip\']').on('remove', function() {
		$(this).tooltip('destroy');
	});	
});
</script>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<nav id="top" class="navbar navbar-static-top" role="navigation">
  <div class="container">
    <div id="top-links" class="nav pull-right">
    </div>
  </div>
</nav>
<header class="main-header">
  <div class="container">
    <div class="row">
      <div class="col-sm-6">
        <div id="logo">
          <?php if ($logo) { ?>
          <a href="<?php echo $home; ?>"><img src="<?php echo $logo; ?>" title="<?php echo $name; ?>" alt="<?php echo $name; ?>" class="img-responsive" /></a>
          <?php } else { ?>
          <h1><a href="<?php echo $home; ?>"><?php echo $name; ?></a></h1>
          <?php } ?>
        </div>
      </div>
     <div class="col-sm-6">
	 <?php if ($logged) { ?>
	<div class="col-sm-12" style="margin-top:40px;text-align:right;">
		<span style="display:block; font-weight:bold;font-size:15px;color:red;ont-style:italic;">Welcome you are logged in as <a href="index.php?route=saccount/account"><?php echo $username; ?></a> <b>(</b> <a href="index.php?route=saccount/logout">Logout</a> <b>)</b></span>
	</div>
	<?php } ?>
      </div>
     <!-- <div class="col-sm-3"><?php echo $cart; ?></div>-->
    </div>
  </div>
</header>
<?php if ($logged) { ?>
<div class="container">
  <nav id="menu" class="navbar">
    <div class="navbar-header"><span id="category" class="visible-xs"><?php echo $text_category; ?></span>
      <button type="button" class="btn btn-navbar navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse"><i class="fa fa-bars"></i></button>
    </div>
    <div class="collapse navbar-collapse navbar-ex1-collapse">
               <ul class="nav navbar-nav">
	  <li><a href="<?php echo $account; ?>">Home</a></li>
            <li class="dropdown"><a href="<?php echo $account; ?>" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">My Store<span class="caret"></span></a>
				<ul class="dropdown-menu">
					<li><a href="index.php?route=saccount/extension">Manage Products</a></li>
					<li><a href="index.php?route=seller/product/add">Add new product</a></li>
					<li><a href="index.php?route=saccount/offer">Add already listed product</a></li>
					<li><a href="index.php?route=saccount/attribute">Manage Attributes</a></li>
					<li><a href="index.php?route=saccount/option">Manage options</a></li>
					<li><a href="index.php?route=saccount/download">Manage Downloads</a></li>
					<li><a href="index.php?route=saccount/uploadimages">Upload Images</a></li>
					<li><a href="index.php?route=saccount/smartexportimport">Upload Products in Bulk</a></li>
     				</ul>
		</li>
		<li class="dropdown"><a href="<?php echo $account; ?>" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">My Account<span class="caret"></span></a>
			<ul class="dropdown-menu">
				<li><a href="index.php?route=saccount/edit">Edit your account information</a></li>
				<li><a href="index.php?route=saccount/password">Change your password</a></li>
				<li><a href="index.php?route=saccount/address">Modify your address book entries</a></li>
				<li><a href="index.php?route=saccount/address2">How you wish to be paid ?</a></li>
			</ul>
		</li>
		<li class="dropdown"><a href="<?php echo $account; ?>" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">My Orders<span class="caret"></span></a>
			<ul class="dropdown-menu">
				<li><a href="index.php?route=saccount/order">View your order history</a></li>
				<li><a href="index.php?route=saccount/transaction">Your Transactions</a></li>
			</ul>
		</li>
          </ul>
    </div>
  </nav>
</div>
<?php } ?>
    <body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
      <header class="main-header">
        <!-- Logo -->
        <a class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini"><b><?php echo $name; ?></b></span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><b><?php echo $name; ?></b></span>
          </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav pull-left">
              <!-- User Account: style can be found in dropdown.less -->
              <?php if ($logged) { ?>
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <!-- <img src="<?php echo $thumb;?>" class="user-image" alt="User Image"> -->
                  <span class="hidden-xs"><?php echo $username?></span>
                </a>
                <ul class="dropdown-menu">
                  <li><a href="index.php?route=saccount/logout">Logout</a></li>
                </ul>
              </li>
              <?php } ?>
            </ul>
          </div>
        </nav>
      </header>
