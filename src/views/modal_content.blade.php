<div class="row">
    <div class="col-md-4">
        <nav class="navbar navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item{{ $activeMenu == 'upload' ? ' active' : '' }}" data-content="upload">
                    <a href="#" class="nav-link">Uploaden</a>
                </li>
                <li class="nav-item{{ $activeMenu == 'library' ? ' active' : '' }}" data-content="library">
                    <a href="#" class="nav-link">Eerder geupload</a>
                </li>
                <li class="nav-item{{ $activeMenu == 'stock' ? ' active' : '' }}" data-content="stock">
                    <a href="#" class="nav-link">Zoeken</a>
                </li>
            </ul>
        </nav>
    </div>
    <div id="cropper-modal-tab" class="col-md-8">
        {!! $defaultContent !!}
    </div>
</div>