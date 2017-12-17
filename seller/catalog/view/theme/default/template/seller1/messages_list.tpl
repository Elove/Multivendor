<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <?php if ($messages) { ?>
  <?php foreach ($messages as $message) { ?>
  <div class="order-list">
    <div class="order-content">
      <div><b><?php echo $text_date_added; ?></b> <?php echo $message['date_added']; ?><br />
	  <b><?php echo $text_customer; ?></b> <?php echo $message['name']; ?><br />
       <?php if($message['messageby'] == 'customer'){?> <b>Enquiry:</b> <?php }else{ ?><b>Reply:</b> <?php } ?>
		<?php echo $message['contactus_enquiry']; ?></div>
		<a href="<?php echo $message['reply']; ?>" class="button"><?php echo $button_reply; ?></a>
    </div>
  </div>
  <?php } ?>
  <div class="pagination"><?php echo $pagination; ?></div>
  <?php } else { ?>
  <div class="content"><?php echo $text_empty; ?></div>
  <?php } ?>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?>