<div class="mb-3">
	<form id="stock-image-search" class="form-inline">
		<input type="text" name="stock-search-term" value="{{ $searchTerm }}" class="form-control" placeholder="Zoek een beeld">
		<button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
	</form>
</div>

<div id="stock-list">
	@include('cropper::modal_stock_list')
</div>