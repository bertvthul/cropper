$(function() {

    var isAdvancedUpload = function() {
        var div = document.createElement('div');
        return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
    }();


    $(document).on('click', '.cropper__recrop', function() {
        $(this).closest('.cropper__editor').find('.cropper__example').css('background-image', '');
        $(this).closest('.cropper__editor').find('.cropper__crop').addClass('cropper__crop--cropping');

    })
    .on('click', '.cropper__savecrop', function() {
         $(this).closest('.cropper__editor').find('.cropper__crop').removeClass('cropper__crop--cropping');
    })
    .on('click', '.cropper__croptools .btn-arrow-down', function() {
        console.log('down');
        if($(this).closest('.cropper__crop').hasClass('cropper__crop--higher')) {
            var extraHeight = $(this).closest('.cropper__crop').find('.cropper__image').outerHeight() - $(this).closest('.cropper__crop').find('.cropper__image-con').outerHeight();
            var currentScrolltop = $(this).closest('.cropper__crop').find('.cropper__image-con').scrollTop();
            // $(this).closest('.cropper__crop').find('.cropper__image-con').scrollTop(currentScrolltop + (extraHeight / 10));
            $(this).closest('.cropper__crop').find('.cropper__image-con').animate({scrollTop: currentScrolltop + (extraHeight / 4)}, 300);

        } else {
            var extraWidth = $(this).closest('.cropper__crop').find('.cropper__image').outerWidth() - $(this).closest('.cropper__crop').find('.cropper__image-con').outerWidth();
            var currentScrollleft = $(this).closest('.cropper__crop').find('.cropper__image-con').scrollLeft();
            console.log($(this).closest('.cropper__crop').find('.cropper__image').outerWidth());
            // $(this).closest('.cropper__crop').find('.cropper__image-con').scrollLeft(currentScrollleft + (extraWidth / 10));
            $(this).closest('.cropper__crop').find('.cropper__image-con').animate({scrollLeft: currentScrollleft + (extraWidth / 4)}, 300);

        }
        // 
    })
    .on('click', '.cropper__croptools .btn-arrow-up', function() {
        console.log('up');
        if($(this).closest('.cropper__crop').hasClass('cropper__crop--higher')) {
            var extraHeight = $(this).closest('.cropper__crop').find('.cropper__image').outerHeight() - $(this).closest('.cropper__crop').find('.cropper__image-con').outerHeight();
            var currentScrolltop = $(this).closest('.cropper__crop').find('.cropper__image-con').scrollTop();
            // $(this).closest('.cropper__crop').find('.cropper__image-con').scrollTop(currentScrolltop - (extraHeight / 10));
            $(this).closest('.cropper__crop').find('.cropper__image-con').animate({scrollTop: currentScrolltop - (extraHeight / 4)}, 300);
        } else {
            var extraWidth = $(this).closest('.cropper__crop').find('.cropper__image').outerWidth() - $(this).closest('.cropper__crop').find('.cropper__image-con').outerWidth();
            var currentScrollleft = $(this).closest('.cropper__crop').find('.cropper__image-con').scrollLeft();
            // $(this).closest('.cropper__crop').find('.cropper__image-con').scrollLeft(currentScrollleft - (extraWidth / 10));
            $(this).closest('.cropper__crop').find('.cropper__image-con').animate({scrollLeft: currentScrollleft - (extraWidth / 4)}, 300);
        }
        // 
    });

    document.addEventListener('scroll', function (event) {
        if (event.target.classList != undefined && event.target.classList.contains('cropper__image-con')) {
            var yPercentage = 0;
            var xPercentage = 0;

            var verticalScroll = $(event.target).closest('.cropper__crop').hasClass('cropper__crop--higher');
            var horizontalScroll = $(event.target).closest('.cropper__crop').hasClass('cropper__crop--wider');

            if (verticalScroll) {
                var extraHeight = $(event.target).find('.cropper__image').outerHeight() - event.target.offsetHeight;
                var yPercentage = (100 / extraHeight) * event.target.scrollTop;
            } else if (horizontalScroll) {
                var extraWidth = $(event.target).find('.cropper__image').outerWidth() - event.target.offsetWidth;
                var xPercentage = (100 / extraWidth) * event.target.scrollLeft;
            }

            xPercentage = Math.round(xPercentage);
            yPercentage = Math.round(yPercentage);

            xPercentage = (xPercentage > 100) ? 100 : xPercentage;
            yPercentage = (yPercentage > 100) ? 100 : yPercentage;

            $(event.target).parents('.cropper').find('.cropperx').val(xPercentage);
            $(event.target).parents('.cropper').find('.croppery').val(yPercentage);
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
        $('.cropper').addClass('cropper--dropzone');

        $('.cropper').each(function() {
            var $form = $(this);

            $form.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
            })
            .on('dragover dragenter', function(e) {
                $(this).addClass('cropper--dragover');
            })
            .on('dragleave dragend drop', function(e) {
                $(this).removeClass('cropper--dragover');
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
    	xhrUploadFile(files, $(this).closest('.cropper'));
    });

    function xhrUploadFile(file, cropperObj) {
        $(cropperObj).addClass('cropper--loading');
        var wasEmpty = false;
        if ($(cropperObj).hasClass('cropper--no-image')) {
            wasEmpty = true;
            $(cropperObj).removeClass('cropper--no-image');
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
                $(cropperObj).removeClass('cropper--loading');
                $(cropperObj).find('.cropper__example').remove();
                console.log(data);
                $(cropperObj).find('.cropper__editor').html(data.html);
                $(cropperObj).find('.cropperx').val(0);
                $(cropperObj).find('.croppery').val(0);
            },
            error: function(data) {
                $(cropperObj).removeClass('cropper--loading');
                if (wasEmpty) {
                    $(cropperObj).addClass('cropper--no-image');
                }
                console.log('Er ging iets mis: ');
                console.log(data.responseJSON.message);
                console.log(data.responseJSON.errors.file);
            }

        });

    }

    function setCropperScrollPosition() {
        $('.cropper').each(function() {
            var cropperx = $(this).find('.cropperx').val();
            var croppery = $(this).find('.croppery').val();
            if (!isNaN(cropperx) && !isNaN(croppery)) {
                if (cropperx > 0) {
                    var extraWidth = $(this).find('.cropper__image').outerWidth() - $(this).find('.cropper__image-con')[0].offsetWidth;

                    $(this).find('.cropper__image-con').scrollLeft(extraWidth * (cropperx / 100));
                } else if (croppery > 0) {
                    var extraHeight = $(this).find('.cropper__image').outerHeight() - $(this).find('.cropper__image-con')[0].offsetHeight;

                    $(this).find('.cropper__image-con').scrollTop(extraHeight * (croppery / 100));
                }
            }
        });
    }

});
