$(function() {

    var isAdvancedUpload = function() {
        var div = document.createElement('div');
        return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
    }();

    // $(document).on('scroll', '.crop-example', function() {
    //     console.log($(this).scrollTop());
    // });

    

    $(document).on('click', '.recrop', function() {
        console.log('remove thumb');
        $(this).closest('.cropper-slice-html-example').find('.cropped-thumb').remove();
        $(this).closest('.cropper-example-con').addClass('can-crop');
    })
    .on('click', '.scale-options .btn-arrow-down', function() {
        console.log('scale down');
        if($(this).closest('.cropper-example-con').hasClass('higher')) {
            var extraHeight = $(this).closest('.cropper-example-con').find('.crop-image').outerHeight() - $(this).closest('.cropper-example-con').find('.crop-example').outerHeight();
            var currentScrolltop = $(this).closest('.cropper-example-con').find('.crop-example').scrollTop();
            // $(this).closest('.cropper-example-con').find('.crop-example').scrollTop(currentScrolltop + (extraHeight / 10));
            $(this).closest('.cropper-example-con').find('.crop-example').animate({scrollTop: currentScrolltop + (extraHeight / 4)});

        } else {
            var extraWidth = $(this).closest('.cropper-example-con').find('.crop-image').outerWidth() - $(this).closest('.cropper-example-con').find('.crop-example').outerWidth();
            var currentScrollleft = $(this).closest('.cropper-example-con').find('.crop-example').scrollLeft();
            console.log($(this).closest('.cropper-example-con').find('.crop-image').outerWidth());
            // $(this).closest('.cropper-example-con').find('.crop-example').scrollLeft(currentScrollleft + (extraWidth / 10));
            $(this).closest('.cropper-example-con').find('.crop-example').animate({scrollLeft: currentScrollleft + (extraWidth / 4)});

        }
        // 
    })
    .on('click', '.scale-options .btn-arrow-up', function() {
        console.log('scale up');
        if($(this).closest('.cropper-example-con').hasClass('higher')) {
            var extraHeight = $(this).closest('.cropper-example-con').find('.crop-image').outerHeight() - $(this).closest('.cropper-example-con').find('.crop-example').outerHeight();
            var currentScrolltop = $(this).closest('.cropper-example-con').find('.crop-example').scrollTop();
            // $(this).closest('.cropper-example-con').find('.crop-example').scrollTop(currentScrolltop - (extraHeight / 10));
            $(this).closest('.cropper-example-con').find('.crop-example').animate({scrollTop: currentScrolltop - (extraHeight / 4)});
        } else {
            var extraWidth = $(this).closest('.cropper-example-con').find('.crop-image').outerWidth() - $(this).closest('.cropper-example-con').find('.crop-example').outerWidth();
            var currentScrollleft = $(this).closest('.cropper-example-con').find('.crop-example').scrollLeft();
            // $(this).closest('.cropper-example-con').find('.crop-example').scrollLeft(currentScrollleft - (extraWidth / 10));
            $(this).closest('.cropper-example-con').find('.crop-example').animate({scrollLeft: currentScrollleft - (extraWidth / 4)});
        }
        // 
    });

    document.addEventListener('scroll', function (event) {
        if (event.target.classList != undefined && event.target.classList.contains('crop-example')) {
            var yPercentage = 0;
            var xPercentage = 0;

            if (event.target.classList.contains('higher')) {
                var extraHeight = $(event.target).find('.crop-image').outerHeight() - event.target.offsetHeight;
                var yPercentage = (100 / extraHeight) * event.target.scrollTop;
            } else if (event.target.classList.contains('wider')) {
                var extraWidth = $(event.target).find('.crop-image').outerWidth() - event.target.offsetWidth;
                var xPercentage = (100 / extraWidth) * event.target.scrollLeft;
            }

            xPercentage = Math.round(xPercentage);
            yPercentage = Math.round(yPercentage);

            xPercentage = (xPercentage > 100) ? 100 : xPercentage;
            yPercentage = (yPercentage > 100) ? 100 : yPercentage;

            $(event.target).parents('.cropper-con').find('.cropperx').val(xPercentage);
            $(event.target).parents('.cropper-con').find('.croppery').val(yPercentage);
        }
    }, true);

    // Zet scroll goed op basis van default waarde cropper x en cropper y
    setCropperScrollPosition();

	$.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    if (isAdvancedUpload) {
        console.log('advanced upload available');
        $('.cropper-con').addClass('dropZone');

        $('.cropper-con').each(function() {
            console.log('dropzone');
            var $form = $(this);

            $form.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
            })
            .on('dragover dragenter', function(e) {
                $(this).addClass('has-dragover');
            })
            .on('dragleave dragend drop', function(e) {
                $(this).removeClass('has-dragover');
            })
            .on('drop', function(ev) {
                droppedFile = ev.originalEvent.dataTransfer.files[0];
                xhrUploadFile(droppedFile, $(this));
                
            });
        });

    } else {
        console.log('no advanced upload available');
    }

    $(document).on('change', 'input[type="file"].img-cropper', function() {
    	var files = $(this)[0].files[0];
    	xhrUploadFile(files, $(this).closest('.cropper-con'));
    });

    function xhrUploadFile(file, cropperObj) {
        $(cropperObj).addClass('uploading');
        var wasEmpty = false;
        if ($(cropperObj).hasClass('no-image-yet')) {
            wasEmpty = true;
            $(cropperObj).removeClass('no-image-yet');
        }

        var fd = new FormData();
        fd.append('file',file);
        fd.append('model', $(cropperObj).data('model'));
        fd.append('name', $(cropperObj).data('name'));
        fd.append('id', $(cropperObj).data('id'));

  //    var xhr = new XMLHttpRequest();
        // xhr.open('POST', '/cropperxhrRequest', true);
        // xhr.send(file);


        $.ajax({
            type:'POST',
            url:'/cropperxhrRequest',
            data:fd,
            contentType: false,
            processData: false,
            success: function(data) {
                $(cropperObj).removeClass('uploading');
                $('.cropper-example-' + data.name).find('.cropped-thumb').remove();
                console.log(data);
                $('.cropper-example-' + data.name).parents('.cropper-slice-html-example').html(data.html);
                $('.cropper-example-' + data.name).parents('.cropper-con').find('.cropperx').val(0);
                $('.cropper-example-' + data.name).parents('.cropper-con').find('.croppery').val(0);
            },
            error: function(data) {
                $(cropperObj).removeClass('uploading');
                if (wasEmpty) {
                    $(cropperObj).addClass('no-image-yet');
                }
                console.log('Er ging iets mis: ');
                console.log(data.responseJSON.message);
                console.log(data.responseJSON.errors.file);
            }

        });

    }

    function setCropperScrollPosition() {
        $('.cropper-con').each(function() {
            var cropperx = $(this).find('.cropperx').val();
            var croppery = $(this).find('.croppery').val();
            if (!isNaN(cropperx) && !isNaN(croppery)) {
                if (cropperx > 0) {
                    var extraWidth = $(this).find('.crop-image').outerWidth() - $(this).find('.crop-example')[0].offsetWidth;

                    $(this).find('.crop-example').scrollLeft(extraWidth * (cropperx / 100));
                } else if (croppery > 0) {
                    var extraHeight = $(this).find('.crop-image').outerHeight() - $(this).find('.crop-example')[0].offsetHeight;

                    $(this).find('.crop-example').scrollTop(extraHeight * (croppery / 100));
                }
            }
        });
    }

});
