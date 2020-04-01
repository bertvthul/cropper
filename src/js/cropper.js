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

         if (!$(this).parents('form')[0]) {
            // als dit veld niet in een formulier staat, sla het dan direct op
            var cropperObj = $(this).closest('.cropper');
            cropperObj.addClass('cropper--loading');
            var data = {'quicksave': true};
            var fieldname = $(cropperObj).data('name');
            data.model = $(cropperObj).data('model');
            data.name = fieldname;
            data.id = $(cropperObj).data('id');
            data.cropperx = {};
            data.croppery = {};
            data.cropperx[fieldname] = $(cropperObj).find('.cropperx').val();
            data.croppery[fieldname] = $(cropperObj).find('.croppery').val();
            data.cropperx[fieldname] = parseInt(data.cropperx[fieldname]) || 0;
            data.croppery[fieldname] = parseInt(data.croppery[fieldname]) || 0;
            $.ajax({
                type:'POST',
                url:'/cropperxhrRequest',
                data:data,
                success: function(data) {
                    cropperObj.removeClass('cropper--loading');
                },
                error: function(data) {
                    cropperObj.removeClass('cropper--loading');
                },
            });
         } else {
            // gewoon een formulier
            // zet nog wel even de cropper x en y op 0 wanneer die geen waarde hebben
            var cropperObj = $(this).closest('.cropper');
            if ($(cropperObj).find('.cropperx').val() == '') {
                $(cropperObj).find('.cropperx').val(0);
            }
            if ($(cropperObj).find('.croppery').val() == '') {
                $(cropperObj).find('.croppery').val(0);
            }
         }
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
    })
    .on('click', 'label.cropper__upload', function(e) {
        if ($(this).closest('.cropper').hasClass('cropper--has-modal')) {
            e.preventDefault();
            var name = $(this).closest('.cropper').data('name');
            var model = $(this).closest('.cropper').data('model');
            popModal(name, model);
        }
    })
    .on('submit', 'form#stock-image-search', function(e) {
        e.preventDefault();
        var searchTerm = $(this).find('[name="stock-search-term"]').val();
        var name = $(this).closest('#cropper-modal').data('name');
        var model = $(this).closest('#cropper-modal').data('model');
        var data = {'type': 'stockSearch', 'q': searchTerm, 'model': model, 'name': name};
        console.log(data);
        $.ajax({
            type:'POST',
            url:'/cropperxhrRequest',
            data:data,
            success: function(data) {
                $('#stock-list').html(data.html);
            },
            error: function(data) {
                console.log(data);
            },
        });
    })
    .on('click', '.cropper-select-image', function() {
        $('#cropper-modal').modal('hide');
        var name = $(this).closest('#cropper-modal').data('name');
        var cropperObj = $('.cropper[data-name="' + name + '"]');
        $(cropperObj).addClass('cropper--loading');

        var wasEmpty = false;
        if ($(cropperObj).hasClass('cropper--no-image')) {
            wasEmpty = true;
            $(cropperObj).removeClass('cropper--no-image');
        }

        var model = $(cropperObj).data('model');
        var id = $(cropperObj).data('id');
        var data = {
            'model': model, 
            'name': name, 
            'id': id
        };
        var imageUrl = $(this).data('orig-image');
        if (imageUrl != undefined) {
            data.type = 'imageUrlUpload';
            data.imageUrl = imageUrl;
        } else {
            var imagePath = $(this).data('image-local-path');
            data.type = 'imagePathUpload';
            data.imagePath = imagePath;
        }

        console.log(data);
        $.ajax({
            type:'POST',
            url:'/cropperxhrRequest',
            data:data,
            success: function(data) {
                $(cropperObj).removeClass('cropper--loading');
                $(cropperObj).find('.cropper__example').remove();
                $(cropperObj).find('.cropper__editor').html(data.html);
                $(cropperObj).find('.cropperx').val(0);
                $(cropperObj).find('.croppery').val(0);
            },
            error: function(data) {
                $(cropperObj).removeClass('cropper--loading');
                if (wasEmpty) {
                    $(cropperObj).addClass('cropper--no-image');
                }
                console.log(data);
            },
        });          
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
                console.log(data);
                $(cropperObj).removeClass('cropper--loading');
                $(cropperObj).find('.cropper__example').remove();
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

    function popModal(name, model) {
        if($('#cropper-modal').length) {
            // set modal variables (name)
            $('#cropper-modal').data('name', name);

            // show modal
            $('#cropper-modal').modal('show');

            // Laad de functies
            loadModalFunctions();

        } else {
            initModal(name, model);
        }
    }

    function initModal(name, model) {
        // Create modal when not present in the dom
        var data = {'type': 'initModal', 'name': name, 'model': model};
        $.ajax({
            type:'POST',
            url:'/cropperxhrRequest',
            data:data,
            success: function(data) {
                $('body').append(data.html);
                console.log(name);
                popModal(name, model);
            },
            error: function(data) {
                console.log(data);
                alert('error bij aanmaken modal, zie console');
            },
        });
    }

    function loadModalFunctions() {
        $(document).on('click', '#cropper-modal .nav-item', function() {
            $('#cropper-modal .nav-item.active').removeClass('active');
            $(this).addClass('active');

            var name = $('#cropper-modal').data('name');
            var model = $('#cropper-modal').data('model');
            var content = $(this).data('content');
            var data = {'type': 'modalNav', 'name': name, 'model': model, 'content': content};
            $.ajax({
                type:'POST',
                url:'/cropperxhrRequest',
                data:data,
                success: function(data) {
                    // of in #cropper-modal-tab?
                    $('#cropper-modal #cropper-modal-tab').html(data.html);
                },
                error: function(data) {
                    console.log(data);
                    alert('error bij navigeren modal, zie console');
                },
            });
        });
    }

});