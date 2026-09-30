<?php require_once 'includes/header.php'; ?>
<?php require_admin(); ?>

<div class="row">
	<div class="col-md-12">

		<ol class="breadcrumb">
		  <li><a href="dashboard.php">Home</a></li>
		  <li class="active">Categories</li>
		</ol>

		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="page-heading"> <i class="glyphicon glyphicon-th-list"></i> Manage Categories</div>
			</div> <!-- /panel-heading -->
			<div class="panel-body">

				<div class="div-action pull pull-right" style="padding-bottom:20px;">
					<button class="btn btn-primary" id="addCategoryBtn"> <i class="glyphicon glyphicon-plus-sign"></i> Add Category </button>
				</div> <!-- /div-action -->

				<table class="table" id="manageCategoryTable" style="width:100%;">
					<thead>
						<tr>
							<th>Category</th>
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

<!-- add / edit category (one modal) -->
<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
    	<form class="form-horizontal" id="categoryForm" method="POST" novalidate>
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title" id="categoryModalTitle">Add Category</h4>
	      </div>
	      <div class="modal-body">
	      	<div id="category-messages"></div>
	      	<input type="hidden" id="categoryId">

	        <div class="form-group">
	        	<label for="categoryName" class="col-sm-3 control-label">Name *</label>
			    <div class="col-sm-9">
			      <input type="text" class="form-control" id="categoryName" autocomplete="off">
			    </div>
	        </div>
	        <div class="form-group">
	        	<label for="categoryStatus" class="col-sm-3 control-label">Status *</label>
			    <div class="col-sm-9">
			      <select class="form-control" id="categoryStatus">
			      	<option value="1">Available</option>
			      	<option value="2">Not available</option>
			      </select>
			      <p class="help-block small">"Not available" hides it when adding or editing products.</p>
			    </div>
	        </div>
	      </div> <!-- /modal-body -->

	      <div class="modal-footer">
	        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
	        <button type="submit" class="btn btn-primary" id="saveCategoryBtn" data-loading-text="Saving…"><i class="glyphicon glyphicon-ok-sign"></i> Save</button>
	      </div>
     	</form>
    </div>
  </div>
</div>

<!-- remove category -->
<div class="modal fade" tabindex="-1" role="dialog" id="removeCategoryModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="glyphicon glyphicon-trash"></i> Remove category</h4>
      </div>
      <div class="modal-body">
      	<div class="removeCategoryMessages"></div>
        <p>Remove <strong id="removeCategoryName"></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-dismiss="modal">Keep</button>
        <button type="button" class="btn btn-danger" id="removeCategoryBtn" data-loading-text="Removing…"><i class="glyphicon glyphicon-trash"></i> Remove</button>
      </div>
    </div>
  </div>
</div>

<script>
	var CRUD = {
		label: 'Category',
		nav: '#navCategories',
		fetchUrl: 'php_action/fetchCategories.php',
		fetchOneUrl: 'php_action/fetchSelectedCategories.php',
		fetchOneParam: 'categoriesId',
		createUrl: 'php_action/createCategories.php',
		createFields: { name: 'categoriesName', status: 'categoriesStatus' },
		editUrl: 'php_action/editCategories.php',
		editFields: { name: 'editCategoriesName', status: 'editCategoriesStatus', id: 'editCategoriesId' },
		removeUrl: 'php_action/removeCategories.php',
		removeParam: 'categoriesId',
		prefix: 'category'
	};
</script>
<script src="custom/js/simpleCrud.js"></script>

<?php require_once 'includes/footer.php'; ?>
