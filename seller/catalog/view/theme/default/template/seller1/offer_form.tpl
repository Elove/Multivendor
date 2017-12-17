<?php echo $header; ?><?php echo $column_left; ?>
<script type="text/javascript" src="catalog/view/javascript/jquery/ajaxupload.js"></script>
  <style>
  .page-header {
    border-bottom: 0px solid #eee;
    margin: 40px 0 20px;
    padding-bottom: 9px;
}
</style>
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
   <!-- Content Header (Page header) -->
   <section class="content-header">
    <div class="row">
    <h1><?php echo $heading_title1; ?></h1>
     <div class="pull-left">
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
     </div>
     </div>
   </section>
  <!-- Main content -->
   <section class="content"> 
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
	 <?php if ($success) { ?>
  <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?></div>
  <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $heading_title1; ?></h3>
      </div>
      <div class="panel-body">
		<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
		<div class="customer-offer-create-preview">
		<h3><?php echo $entry_reg;?></h3>
		</div>
		<div>
		<div class="left-sidebar" style="margin-left:5px; margin-right:0px; float: left; height:110px;width:150px;">
		<div class="main_info">
		<div class="back">
		<div class="main_photo">
		<img src="<?php echo $thumb; ?>" alt="" id="thumb" width="100" height="100"/>
		</div>
		</div><!-- end back -->
		</div><!-- end main_info -->
		</div> <!-- end left -->
		<div style="width:700px;float:left">
		<div class="product-list">
		<div id="messages_product_view"></div>
		<h1 itemprop="name"><?php echo $product_description;?></h1>
		<div class="info">
		<div id="details-preview">
		<span class="label"><?php echo $entry_model;?></span>
		<?php echo $model;?>
		</div>
		</div><!-- end info -->
		</div>
		</div>
		</div>
		<div class="clearfix"></div>
		<div id="product_description">
					<h3><?php echo $entry_reg1;?></h3>
		<?php echo $description;?>
		</div>
		<div class="buttons clearfix">
			<div class="pull-right">
			<span><a href='<?php echo $insert; ?>' class="btn btn-primary"><?php echo $b_no;?></a></span>
		    <span> <a href='<?php echo $action; ?>' class="btn btn-primary"><?php echo $b_yes;?></a></span>	 
			</div>
		</div>
		</form>
      </div>
    </div>
  </div>
</div>
  </div>
</div>
<?php echo $footer; ?>