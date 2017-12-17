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
    <h1><?php echo $heading_title; ?></h1>
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
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $heading_title; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-attribute" class="form-horizontal">
			<div class="fieldset">
			<ul class="form-list">
			<li class="wide">
			<?php echo $text_product;?>
			<a href="<?php echo $insert;?>"><?php echo $text_insert;?></a>
			</p>
			</li>
			<li class="wide product-name-main">
			<label  for="product_name"><?php echo $text_serach;?></label>
			<div class="input-box">
			<input type="text" style="width:500px;padding:5px" name="product_name" id="product_name" value="">
			</div>
			</li>
			<input type="hidden" value="" name="select_product" id="select_product">
			</ul>
			</div>
			<div class="buttons clearfix">
			<div class="pull-right">
			<input type="submit" value="<?php echo $button_next; ?>" class="btn btn-primary" />
			</div>
			</div>
        </form>
      </div>
    </div>
  </div>
</div>
  </div>
</div>
<script type="text/javascript"><!--
$('input[name=\'product_name\']').autocomplete({
 	'source': function(request, response) {
	   $('input[name=\'select_product\']').val('');	
		$.ajax({
			url: 'index.php?route=seller/offer/autocomplete&filter_name1=' +  encodeURIComponent(request),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item['name'],
						value: item['product_id']
					}
				}));
			}
		});
	},
	'select': function(item) {
		$('input[name=\'product_name\']').val(item['label']);
		$('input[name=\'select_product\']').val(item['value']);
	}
});
//--></script> 
<?php echo $footer; ?>