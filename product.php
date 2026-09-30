<?php require_once 'includes/header.php'; ?>
<?php require_admin(); ?>
<?php
$brandOptions = $connect->query("SELECT brand_id, brand_name FROM brands WHERE brand_status = 1 AND brand_active = 1 ORDER BY brand_name")->fetch_all(MYSQLI_ASSOC);
$categoryOptions = $connect->query("SELECT categories_id, categories_name FROM categories WHERE categories_status = 1 AND categories_active = 1 ORDER BY categories_name")->fetch_all(MYSQLI_ASSOC);

$renderSelect = function($rows, $idKey, $nameKey) {
	$html = '<option value="">— Select —</option>';
	foreach($rows as $row) {
		$html .= '<option value="' . (int)$row[$idKey] . '">' . h($row[$nameKey]) . '</option>';
	}
	return $html;
};

/* Same fields on the add and edit forms; $p = '' or 'edit' (id/name prefix). */
$productFields = function($p) use ($renderSelect, $brandOptions, $categoryOptions) {
	$id = function($name) use ($p) { return $p === '' ? $name : $p . ucfirst($name); };
	ob_start(); ?>
	<div class="form-group">
		<label for="<?php echo $id('productName'); ?>" class="col-sm-4 control-label">Product name *</label>
		<div class="col-sm-8">
			<input type="text" class="form-control" id="<?php echo $id('productName'); ?>" name="<?php echo $id('productName'); ?>" placeholder="e.g. Steel scaffold frame 1.7m" autocomplete="off">
		</div>
	</div>
	<div class="form-group">
		<label for="<?php echo $id('dailyRate'); ?>" class="col-sm-4 control-label">Daily hire rate (KSh) *</label>
		<div class="col-sm-8">
			<input type="number" min="0.01" step="0.01" class="form-control" id="<?php echo $id('dailyRate'); ?>" name="<?php echo $id('dailyRate'); ?>" autocomplete="off">
		</div>
	</div>
	<div class="form-group">
		<label for="<?php echo $id('quantity'); ?>" class="col-sm-4 control-label">Quantity in store *</label>
		<div class="col-sm-8">
			<input type="number" min="0" step="1" class="form-control" id="<?php echo $id('quantity'); ?>" name="<?php echo $id('quantity'); ?>" autocomplete="off">
			<?php if($p !== '') { ?><p class="help-block small">Units in the yard now — items out on hire are not counted here and come back automatically when returned.</p><?php } ?>
		</div>
	</div>
	<div class="form-group">
		<label for="<?php echo $id('rate'); ?>" class="col-sm-4 control-label">Purchase cost (KSh)</label>
		<div class="col-sm-8">
			<input type="number" min="0" step="0.01" class="form-control" id="<?php echo $id('rate'); ?>" name="<?php echo $id('rate'); ?>" placeholder="Optional — for your records" autocomplete="off">
		</div>
	</div>
	<div class="form-group">
		<label for="<?php echo $id('brandName'); ?>" class="col-sm-4 control-label">Brand *</label>
		<div class="col-sm-8">
			<select class="form-control" id="<?php echo $id('brandName'); ?>" name="<?php echo $id('brandName'); ?>"><?php echo $renderSelect($brandOptions, 'brand_id', 'brand_name'); ?></select>
		</div>
	</div>
	<div class="form-group">
		<label for="<?php echo $id('categoryName'); ?>" class="col-sm-4 control-label">Category *</label>
		<div class="col-sm-8">
			<select class="form-control" id="<?php echo $id('categoryName'); ?>" name="<?php echo $id('categoryName'); ?>"><?php echo $renderSelect($categoryOptions, 'categories_id', 'categories_name'); ?></select>
		</div>
	</div>
	<div class="form-group">
		<label for="<?php echo $id('productStatus'); ?>" class="col-sm-4 control-label">Status *</label>
		<div class="col-sm-8">
			<select class="form-control" id="<?php echo $id('productStatus'); ?>" name="<?php echo $id('productStatus'); ?>">
				<option value="1">Available for hire</option>
				<option value="2">Not available</option>
			</select>
		</div>
	</div>
	<?php return ob_get_clean();
};
?>

<div class="row">
	<div class="col-md-12">

		<ol class="breadcrumb">
		  <li><a href="dashboard.php">Home</a></li>
		  <li class="active">Equipment</li>
		</ol>

		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="page-heading"> <i class="glyphicon glyphicon-wrench"></i> Manage Equipment</div>
			</div> <!-- /panel-heading -->
			<div class="panel-body">

				<div class="remove-messages"></div>

				<div class="div-action pull pull-right" style="padding-bottom:20px;">
					<button class="btn btn-primary" id="addProductModalBtn"> <i class="glyphicon glyphicon-plus-sign"></i> Add Product </button>
				</div> <!-- /div-action -->

				<div class="table-responsive" style="clear:both;">
				<table class="table" id="manageProductTable" style="width:100%;">
					<thead>
						<tr>
							<th style="width:70px;">Photo</th>
							<th>Product</th>
							<th class="text-right">Daily rate (KSh)</th>
							<th class="text-right">In store</th>
							<th class="text-right">On hire</th>
							<th>Brand</th>
							<th>Category</th>
							<th>Status</th>
							<th style="width:90px;"></th>
						</tr>
					</thead>
				</table>
				</div>

			</div> <!-- /panel-body -->
		</div> <!-- /panel -->
	</div> <!-- /col-md-12 -->
</div> <!-- /row -->


<!-- add product -->
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">

    	<form class="form-horizontal" id="submitProductForm" action="php_action/createProduct.php" method="POST" enctype="multipart/form-data" novalidate>
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title"><i class="fa fa-plus"></i> Add Product</h4>
	      </div>

	      <div class="modal-body" style="max-height:70vh; overflow:auto;">
	      	<div id="add-product-messages"></div>

	      	<div class="form-group">
	        	<label for="productImage" class="col-sm-4 control-label">Photo</label>
				    <div class="col-sm-8">
						<div id="kv-avatar-errors-1" class="center-block" style="display:none;"></div>
					    <div class="kv-avatar center-block">
					        <input type="file" id="productImage" name="productImage" accept="image/png,image/jpeg,image/gif">
					    </div>
					    <p class="help-block small">Optional. JPG, PNG or GIF under 2.5 MB.</p>
				    </div>
	        </div>

	        <?php echo $productFields(''); ?>
	      </div> <!-- /modal-body -->

	      <div class="modal-footer">
	        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
	        <button type="submit" class="btn btn-primary" id="createProductBtn" data-loading-text="Saving…" autocomplete="off"> <i class="glyphicon glyphicon-ok-sign"></i> Add product</button>
	      </div> <!-- /modal-footer -->
     	</form> <!-- /.form -->
    </div> <!-- /modal-content -->
  </div> <!-- /modal-dailog -->
</div>
<!-- /add product -->


<!-- edit product -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">

	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title"><i class="fa fa-edit"></i> Edit Product</h4>
	      </div>
	      <div class="modal-body" style="max-height:70vh; overflow:auto;">

	      	<div class="div-loading">
	      		<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
				<span class="sr-only">Loading...</span>
	      	</div>

	      	<div class="div-result div-hide">

			  <ul class="nav nav-tabs" role="tablist">
			    <li role="presentation" class="active"><a href="#productInfo" aria-controls="productInfo" role="tab" data-toggle="tab">Details</a></li>
			    <li role="presentation"><a href="#photo" aria-controls="photo" role="tab" data-toggle="tab">Photo</a></li>
			  </ul>

			  <div class="tab-content">

			    <div role="tabpanel" class="tab-pane active" id="productInfo">
			    	<form class="form-horizontal" id="editProductForm" action="php_action/editProduct.php" method="POST" novalidate>
			    	<br />
			    	<div id="edit-product-messages"></div>
			    	<input type="hidden" name="productId" id="editProductId">

			    	<?php echo $productFields('edit'); ?>

			        <div class="modal-footer">
				        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
				        <button type="submit" class="btn btn-primary" id="editProductBtn" data-loading-text="Saving…"> <i class="glyphicon glyphicon-ok-sign"></i> Save changes</button>
				    </div>
			        </form>
			    </div>

			    <div role="tabpanel" class="tab-pane" id="photo">
			    	<form action="php_action/editProductImage.php" method="POST" id="updateProductImageForm" class="form-horizontal" enctype="multipart/form-data" novalidate>
			    	<br />
			    	<div id="edit-productPhoto-messages"></div>
			    	<input type="hidden" name="productId" id="editPhotoProductId">

			    	<div class="form-group">
		        	<label class="col-sm-4 control-label">Current photo</label>
					    <div class="col-sm-8">
					      <img src="" id="getProductImage" class="thumbnail" alt="" style="width:220px; height:220px; object-fit:cover;" />
					    </div>
			        </div>

			      	<div class="form-group">
			        	<label for="editProductImage" class="col-sm-4 control-label">New photo</label>
					    <div class="col-sm-8">
							<div id="kv-avatar-errors-2" class="center-block" style="display:none;"></div>
						    <div class="kv-avatar center-block">
						        <input type="file" id="editProductImage" name="editProductImage" accept="image/png,image/jpeg,image/gif">
						    </div>
					    </div>
			        </div>

			        <div class="modal-footer">
				        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
				        <button type="submit" class="btn btn-primary" id="editProductImageBtn" data-loading-text="Uploading…"> <i class="glyphicon glyphicon-upload"></i> Upload photo</button>
				    </div>
			        </form>
			    </div>

			  </div>
			</div>

	      </div> <!-- /modal-body -->
    </div>
  </div>
</div>
<!-- /edit product -->

<!-- remove product -->
<div class="modal fade" tabindex="-1" role="dialog" id="removeProductModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="glyphicon glyphicon-trash"></i> Remove product</h4>
      </div>
      <div class="modal-body">
      	<div class="removeProductMessages"></div>
        <p>Remove <strong id="removeProductName">this product</strong>? It will no longer be offered on new orders. Past orders and invoices are not affected.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-dismiss="modal">Keep product</button>
        <button type="button" class="btn btn-danger" id="removeProductBtn" data-loading-text="Removing…"> <i class="glyphicon glyphicon-trash"></i> Remove</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- /remove product -->


<script src="custom/js/product.js"></script>

<?php require_once 'includes/footer.php'; ?>
