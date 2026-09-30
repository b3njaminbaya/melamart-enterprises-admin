<?php require_once 'includes/header.php'; ?>
<?php require_admin(); ?>

<div class="row">
	<div class="col-md-12">

		<ol class="breadcrumb">
		  <li><a href="dashboard.php">Home</a></li>
		  <li class="active">Brands</li>
		</ol>

		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="page-heading"> <i class="glyphicon glyphicon-bookmark"></i> Manage Brands</div>
			</div> <!-- /panel-heading -->
			<div class="panel-body">

				<div class="div-action pull pull-right" style="padding-bottom:20px;">
					<button class="btn btn-primary" id="addBrandBtn"> <i class="glyphicon glyphicon-plus-sign"></i> Add Brand </button>
				</div> <!-- /div-action -->

				<table class="table" id="manageBrandTable" style="width:100%;">
					<thead>
						<tr>
							<th>Brand</th>
							<th class="text-right">Products</th>
							<th>Status</th>
							<th style="width:90px;"></th>
						</tr>
					</thead>
				</table>

			</div> <!-- /panel-body -->
		</div> <!-- /panel -->
	</div> <!-- /col-md-12 -->
</div> <!-- /row -->

<!-- add / edit brand (one modal) -->
<div class="modal fade" id="brandModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
    	<form class="form-horizontal" id="brandForm" method="POST" novalidate>
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title" id="brandModalTitle">Add Brand</h4>
	      </div>
	      <div class="modal-body">
	      	<div id="brand-messages"></div>
	      	<input type="hidden" id="brandId">

	        <div class="form-group">
	        	<label for="brandName" class="col-sm-3 control-label">Name *</label>
			    <div class="col-sm-9">
			      <input type="text" class="form-control" id="brandName" autocomplete="off">
			    </div>
	        </div>
	        <div class="form-group">
	        	<label for="brandStatus" class="col-sm-3 control-label">Status *</label>
			    <div class="col-sm-9">
			      <select class="form-control" id="brandStatus">
			      	<option value="1">Available</option>
			      	<option value="2">Not available</option>
			      </select>
			      <p class="help-block small">"Not available" hides it when adding or editing products.</p>
			    </div>
	        </div>
	      </div> <!-- /modal-body -->

	      <div class="modal-footer">
	        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
	        <button type="submit" class="btn btn-primary" id="saveBrandBtn" data-loading-text="Saving…"><i class="glyphicon glyphicon-ok-sign"></i> Save</button>
	      </div>
     	</form>
    </div>
  </div>
</div>

<!-- remove brand -->
<div class="modal fade" tabindex="-1" role="dialog" id="removeBrandModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="glyphicon glyphicon-trash"></i> Remove brand</h4>
      </div>
      <div class="modal-body">
      	<div class="removeBrandMessages"></div>
        <p>Remove <strong id="removeBrandName"></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-dismiss="modal">Keep</button>
        <button type="button" class="btn btn-danger" id="removeBrandBtn" data-loading-text="Removing…"><i class="glyphicon glyphicon-trash"></i> Remove</button>
      </div>
    </div>
  </div>
</div>

<script>
	var CRUD = {
		label: 'Brand',
		nav: '#navBrand',
		fetchUrl: 'php_action/fetchBrand.php',
		fetchOneUrl: 'php_action/fetchSelectedBrand.php',
		fetchOneParam: 'brandId',
		createUrl: 'php_action/createBrand.php',
		createFields: { name: 'brandName', status: 'brandStatus' },
		editUrl: 'php_action/editBrand.php',
		editFields: { name: 'editBrandName', status: 'editBrandStatus', id: 'brandId' },
		removeUrl: 'php_action/removeBrand.php',
		removeParam: 'brandId',
		prefix: 'brand'
	};
</script>
<script src="custom/js/simpleCrud.js"></script>

<?php require_once 'includes/footer.php'; ?>
