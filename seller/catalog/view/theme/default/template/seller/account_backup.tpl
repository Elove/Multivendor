<?php echo $header; ?>
<?php echo $column_left; ?>
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
   <!-- Content Header (Page header) -->
   <section class="content-header">
    <div class="row">
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
  <?php if ($success) { ?>
   <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?></div>
  <?php } ?>
 <div id="content" class="<?php echo $class; ?>"><?php echo $content_top; ?>
<div style="width:30%;float:left;"><h2><?php echo $text_extensions; ?></h2>
      <ul class="list-unstyled">
       <li><a href="<?php echo $manageextensions; ?>"><img src="image/nijjar/product.jpg" style="  width:64px;height:64px"><?php echo $text_manageextensions; ?></a></li>
      <li><a href="<?php echo $addextensions; ?>"><img src="image/nijjar/add.jpg" style="  width:64px;height:64px"><?php echo $text_addextensions; ?></a></li>
	   <li><a href="<?php echo $offer; ?>"><img src="image/nijjar/product.jpg" style="  width:64px;height:64px"><?php echo $text_offer; ?></a></li>	    
	   <li><a href="<?php echo $option; ?>"><img src="image/nijjar/options.jpg" style="  width:64px;height:64px"><?php echo $text_option; ?></a></li> 		 
	   <li><a href="<?php echo $attributes; ?>"><img src="image/nijjar/attributes.jpg" style="  width:64px;height:64px"><?php echo $text_attributes; ?></a></li>	     
	    <li><a href="<?php echo $export; ?>"><img src="image/nijjar/category1.jpg" style="  width:64px;height:64px"><?php echo $text_export; ?></a></li>
	   </ul>
	   </div>
	  <!--code added here -->
	  <div style="width:33%; float:left;"><h2><?php echo $text_my_account; ?></h2>    <ul class="list-unstyled">
				<li><a href="<?php echo $plan; ?>"><img src="image/nijjar/icon_upgrade.png" style="  width:64px;height:64px"><?php echo $text_plan; ?></a></li>
	   <li><a href="<?php echo $images; ?>"><img src="image/nijjar/category1.jpg" style="  width:64px;height:64px"><?php echo $text_images; ?></a></li>
	  <li><a href="<?php echo $category; ?>"><img src="image/nijjar/category1.jpg" style="  width:64px;height:64px"><?php echo $text_category; ?></a></li>
	  <li><a href="<?php echo $download; ?>"><img src="image/nijjar/download.png" style="  width:64px;height:64px"><?php echo $text_download; ?></a></li>
 <li><a href="<?php echo $edit; ?>"><img src="image/nijjar/edit1.png" style="  width:64px;height:64px"><?php echo $text_edit; ?></a></li>        
 <li><a href="<?php echo $password; ?>"><img src="image/nijjar/password.png" style="  width:64px;height:64px"><?php echo $text_password; ?></a></li>
      </ul>
	  </div>
	  <div style="width:33%;float:left;">
  <h2><?php echo $text_my_orders; ?></h2>
      <ul class="list-unstyled">
        <li><a href="<?php echo $address; ?>"><img src="image/nijjar/address.jpg" style="  width:64px;height:64px"><?php echo $text_address; ?></a></li>
        <li><a href="<?php echo $address2; ?>"><img src="image/nijjar/address.jpg" style="  width:64px;height:64px"><?php echo $text_getpaid; ?></a></li>
      <ul class="list-unstyled">
        <li><a href="<?php echo $order; ?>"><img src="image/nijjar/orders.png" style="  width:64px;height:64px"><?php echo $text_order; ?></a></li>
        <li><a href="<?php echo $transaction; ?>"><img src="image/nijjar/transaction.jpg" style="  width:64px;height:64px"><?php echo $text_transaction; ?></a></li>
      </ul>
	  </div></div>
</div>
    </section>
</div>
<?php echo $footer; ?>