<link rel="stylesheet" href="assets/configedit/css/themes/proton/style.min.css" />
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<h1><?php echo _("Configuration File Editor")?></h1>
			<div class="panel panel-info">
				<div class="panel-heading">
					<div class="panel-title">
						<a href="#" data-toggle="collapse" data-target="#moreinfo"><i class="glyphicon glyphicon-info-sign"></i></a>&nbsp;&nbsp;&nbsp;<?php echo _("What is Configuration File Editor")?>
					</div>
				</div>
				<!--At some point we can probably kill this... Maybe make is a 1 time panel that may be dismissed-->
				<div class="panel-body collapse" id="moreinfo">
					<p><?php echo sprintf(_("Configuration file editor gives you the ability to edit custom %s files in the browser that you would normally have to edit through the CLI."),$brand)?></p>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4">
			<button class="btn" id="addfile"><i class="fa fa-plus"></i> <?php echo _("Add New File")?></button>
			<div id="jstree-proton-1" style="margin-top:20px;" class="proton-demo">
				<ul>
					<?php foreach($listings['custom'] as $path => $listing) {?>
						<li data-jstree='{"opened":true,"selected":true, "icon":"fa fa-folder-o"}'><?php echo $listing['name']?>
							<?php foreach($listing['files'] as $file) {?>
								<ul>
									<li data-jstree='{"icon":"fa <?php echo !empty($file['size']) ? 'fa-file-text-o' : 'fa-file-o'?>"}' data-path="<?php echo $path?>" data-type="<?php echo $file['type']?>" data-file="<?php echo $file['file']?>" data-mime="<?php echo $listing['mime']?>"><?php echo $file['file']?></li>
								</ul>
							<?php } ?>
						</li>
					<?php } ?>
					<?php foreach($listings['system'] as $path => $listing) {?>
						<li data-jstree='{"opened":true,"selected":true, "icon":"fa fa-folder-o"}'><?php echo $listing['name']?>
							<?php foreach($listing['files'] as $file) {?>
								<ul>
									<li data-jstree='{"icon":"fa <?php echo !empty($file['size']) ? 'fa-file-text-o' : 'fa-file-o'?>"}' data-path="<?php echo $path?>" data-type="<?php echo $file['type']?>" data-file="<?php echo $file['file']?>" data-mime="<?php echo $listing['mime']?>"><?php echo $file['file']?></li>
								</ul>
							<?php } ?>
						</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<div class="col-md-8">
			<h3 id="filemessage"><?php echo _('Click a file on the left to edit')?></h3>
			<div id="message" class="alert alert-danger hidden" role="alert"></div>
			<textarea id="editor" disabled></textarea>
			<br/>
			<button id="delete" class="pull-right btn btn-danger" disabled><?php echo _("Delete")?></button>
			<button id="save" class="pull-right btn" disabled><?php echo _("Save")?></button>
		</div>
	</div>
</div>
<script>var names = <?php echo json_encode($names);?></script>
