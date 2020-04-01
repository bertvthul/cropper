@if(!empty($images))
	<div class="card-columns">
		@foreach($images as $image)
			<div class="card stock-item">
				<img src="{{ $image->thumb->url }}" width="100%" alt="{{ $image->name }}" data-orig-image="{{ $image->original->url }}" class="cropper-select-image card-img-top">
			</div>
		@endforeach
	</div>
@else
	@if($searchTerm)
		<p>Geen resultaten. Probeer een andere zoekterm...</p>
	@else
		<p>Vul een zoekterm in.</p>
	@endif
@endif