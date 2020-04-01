@if(!empty($images))
	<div class="card-columns">
		@foreach($images as $image)
			<div class="card library-item">
				<img src="{{ $image->image }}" width="100%" alt="Afbeelding" data-image-local-path="{{ $image->path }}" class="cropper-select-image card-img-top">
			</div>
		@endforeach
	</div>
@else
	<p>Nog geen eerder geuploade beelden gevonden.</p>
@endif
