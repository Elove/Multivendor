<?php echo $header; ?>
<style>
#ver-zebra
{
	 font-size: 12px;
    text-align: justify;
    width: 100%;;
	border-collapse: collapse;
}
#ver-zebra th
{
	font-size: 14px;
	font-weight: normal;
	padding: 12px 15px;
	border-right: 1px solid #fff;
	border-left: 1px solid #fff;
	color: #039;
}
#ver-zebra td
{
	padding: 8px 15px;
	border-right: 1px solid #fff;
	border-left: 1px solid #fff;
		border-bottom: 1px solid #fff;
	color: #5D5D5D;
	background: #e8edff;
}
.vzebra-odd
{
	background: #eff2ff;
}
.vzebra-even
{
	background: #e8edff;
}
#ver-zebra #vzebra-adventure, #ver-zebra #vzebra-children
{
	background: #d0dafd;
	border-bottom: 1px solid #c8d4fd;
}
#ver-zebra #vzebra-comedy, #ver-zebra #vzebra-action
{
	background: #48B7E5;
	border-bottom: 1px solid #d6dfff;
	color:#fff;
	font-weight:bold;
}
#vzebra-comedy1
{
	background: #EEEEEE;
	border-bottom: 1px solid #d6dfff;
	color:#5D5D5D ! important;
	font-weight:bold;
	font-size:13px;
}
</style>
<div class="container">
  <ul class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
    <?php } ?>
  </ul>
  <div class="row"><?php echo $column_left; ?>
    <?php if ($column_left && $column_right) { ?>
    <?php $class = 'col-sm-6'; ?>
    <?php } elseif ($column_left || $column_right) { ?>
    <?php $class = 'col-sm-9'; ?>
    <?php } else { ?>
    <?php $class = 'col-sm-12'; ?>
    <?php } ?>
    <div id="content" class="<?php echo $class; ?>"><?php echo $content_top; ?>
<table summary="Most Favorite Movies" id="ver-zebra">
    <colgroup>
    	<col class="vzebra-odd">
    	<col class="vzebra-even">
    	<col class="vzebra-odd">
        <col class="vzebra-even">
    </colgroup>
	  <thead>
    	<tr>
            <th id="vzebra-comedy" scope="col"><?php echo $column_plan;?></th>
		   <th id="vzebra-comedy" scope="col"><?php echo $column_duration;?></th>	
            <th id="vzebra-comedy" scope="col"><?php echo $column_charges;?></th>	
		   <th id="vzebra-comedy" scope="col"><?php echo $column_about;?></th>	
        </tr>
    </thead>
    <tbody>
	     <?php if($getmemberships) { ?>		   
           <?php foreach ($getmemberships as $getmembership) { 
		   ?>	
    	<tr>
        	<td id="vzebra-comedy1" scope="col"><?php echo strtoupper($getmembership['commission_name']);?><?php  
			if($getmembership['amount1']>0){
			}
			else{
			echo "(Free)"; }?>	</td>
			<td id="vzebra-comedy1" scope="col">
			<?php if($getmembership['per']){
			 if($getmembership['duration_id']){
			echo $getmembership['per'].'/'.$durations[$getmembership['duration_id']];
			}}?></td>
			<td id="vzebra-comedy1" scope="col"><?php  
			if($getmembership['amount1']>0){
			echo  $getmembership['amount']; 
			}
			else{
			echo "Free"; }?>	</td>
         <td id="vzebra-comedy1" scope="col"><?php echo $getmembership['product_limit'];?>	</td>
         </tr>
		 <?php } }?>
    </tbody>
    </table>
      <?php echo $content_bottom; ?></div>
    <?php echo $column_right; ?></div>
</div>
<?php echo $footer; ?>