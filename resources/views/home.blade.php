<!DOCTYPE html>
<html lang='zh-TW'>

<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta name='_token' content='{!! csrf_token() !!}'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Tagger</title>

    <link href='/assets/css/bootstrap.min.css' rel='stylesheet'>
    <link href='/assets/font-awesome/css/font-awesome.css' rel='stylesheet'>
    <link href='/assets/css/custom.css' rel='stylesheet'>
    <link href='/assets/css/animate.css' rel='stylesheet'>
    <link href='/assets/css/style.css' rel='stylesheet'>
    <link href='/assets/css/plugins/jQuery-Tags-Input/jquery.tagsinput.min.css' rel='stylesheet'> 
    <link href='/assets/css/plugins/toastr/toastr.min.css' rel='stylesheet'> 

    
    @yield('page-head')
</head>

<body>
    <div class='row'>
        <div class='col-xs-12'>
            @include('layouts.header')
        </div>
    </div>
    <div class='row'>
         
        <div class='col-xs-12'>
            <div class='row header-border-style'>
                <div class='col-xs-2'></div>
                <div class='col-xs-8'>
                    <div class='row selector-margin'>
                        <div class='input-group'>
                            <input type='text' class='form-control' placeholder='Keyworks' id='keywords'>
                            <span class='input-group-btn'> 
                                <button id='startSearch' type='button' class='btn btn-primary'>Search</button> 
                            </span>
                        </div>
                    </div>
                </div>
                <div class='col-xs-2'>
                </div>
            </div>
            <div class='row wrapper wrapper-content wrapper-padding-bottom animated fadeInRight' style='margin-left:25px;'>
                <div class='col-xs-3 no-padding' id='tag-section'>
                    <div class='row'>
                        <div class='form-group'>
                            <textarea id='tag-area' class='form-control' placeholder='Tags' style='height:400px;'></textarea>
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-xs-2 no-padding'><button type='button' class='btn btn-success' id='copyButton' >Copy</button></div>
                        <div class='col-xs-3 '><button type='button' class='btn btn-danger' id='cleanButton' >Clean</button> </div>
                    </div>
                </div>
                <div class='col-xs-9 image-display-section'>
                    <div class='grid pre-scrollable' id='scroll-area' style='margin:20px 0 20px 0;'>
                         <div id='loading' class="sk-spinner sk-spinner-three-bounce hidden loading-style">
                                <div class="sk-bounce1"></div>
                                <div class="sk-bounce2"></div>
                                <div class="sk-bounce3"></div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class='row'>
        <div class='col-xs-12'>
            @include('layouts.footer')
        </div>
    </div>
</body>
@include('components.googleAnalytics')
<script src='/assets/js/jquery-2.1.1.js'></script>
<script src='/assets/js/bootstrap.min.js'></script>
<script src='/assets/js/plugins/metisMenu/jquery.metisMenu.js'></script>
<script src='/assets/js/plugins/slimscroll/jquery.slimscroll.min.js'></script>
<script src='/assets/js/inspinia.js'></script>
<script src='/assets/js/plugins/masonary/masonry.pkgd.min.js'></script>
<script src='/assets/js/plugins/jQuery-Tags-Input/jquery.tagsinput.min.js'></script>
<script src='/assets/js/plugins/imagesLoaded/imagesloaded.pkgd.min.js'></script>
<script src='/assets/js/plugins/toastr/toastr.min.js'></script>

<script>
    // define 
    var grid = $('.grid');
    var textarea = $('#tag-area');
    var loading = $('#loading');
    var scrollArea = $('#scroll-area');

    //initial plugins
    $('#scroll-area').css('min-height', ($(document).height() * 0.60) + 'px');
    $('#tag-area').tagsInput({'width':'90%','height':'500px','defaultText':''});
    $('#keywords').tagsInput({'width':'98%','height':'40px','defaultText':'keywords for your image'});

    grid.imagesLoaded(function(){ 
        grid.masonry({
            itemSelector: '.grid-item',
            columnWidth: 300,
            gutter: 25
        }); 
    });

    // search images after click
    $(document).on('click', '#startSearch', function() {
        removeAllElementsInGrid();
        textarea.importTags('');
        loading.toggleClass('hidden');
        scrollArea.toggleClass('shader');
        getImages();
    }); 

    // select image
    $(document).on( 'click', '.grid-item', function() {
        if ($(this).hasClass('selected-item') == true) {
                //$(this).removeClass('selected-item');
            } else {
                $(this).addClass('selected-item');
                getImageContents($(this).data("photo-id"));
            }
    });

    // copy tags
    $(document).on( 'click', '#copyButton', function() {
        if(textarea.val().trim().length > 0){
            copyToClipboard('#tag-area');
            alertToastr(500,3500,'success', '<hr>' + textarea.val() + '<hr>Above tags are copied to clipboard successfully!');
        }else{
            alertToastr(500,2000,'error','No tags found.');
        }
    });

    // clean tags
    $(document).on( 'click', '#cleanButton', function() {
        if(textarea.val().trim().length > 0){
            // remove tags from textare
            textarea.importTags('');
            // unselect all selected images
            $(".grid-item").each( function(){
                var image = $(this);
                if (image.hasClass('selected-item') == true) {
                    image.removeClass('selected-item');
                }
            });
            alertToastr(200,1500,'warning','Tags are cleaned up successfully!');
        }else{
            alertToastr(500,2000,'error','No tags found.');
        }
    });

    function cleanTags(){
        if(textarea.val().trim().length > 0){
            // remove tags from textare
            textarea.importTags('');
            // unselect all selected images
            $(".grid-item").each( function(){
                var image = $(this);
                if (image.hasClass('selected-item') == true) {
                    image.removeClass('selected-item');
                }
            });
            alertToastr(200,1500,'warning','Tags are cleaned up successfully!');
        }else{
            alertToastr(500,2000,'error','No tags found.');
        }
    }

    function alertToastr(timeout, optionTimeout, toastrType, message){
        setTimeout(function() {
            toastr.options = {
                positionClass: 'toast-top-right',
                closeButton: true,
                progressBar: true,
                showMethod: "fadeIn",
                hideMethod: "fadeOut",
                timeOut: optionTimeout,
                escapeHtml: true,
            };
            switch(toastrType){
                case 'success':
                    toastr.success(message, 'Tagger | <i class="fa fa-tags"></i>');
                    break;
                case 'warning':
                    toastr.warning(message, 'Tagger | <i class="fa fa-tags"></i>');
                    break;
                case 'error':
                    toastr.error(message, 'Tagger | <i class="fa fa-tags"></i>');
                    break;
                default:
                    toastr.info(message, 'Tagger | <i class="fa fa-tags"></i>');
                    break;
            }
        }, timeout);
    }
 
    function copyToClipboard(element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).val()).select();
        document.execCommand("copy");
        $temp.remove();
    }

    // clean grid
    function removeAllElementsInGrid(){
        grid.each(function(){
        var $item  = $(this).find('.grid-item');
            grid.masonry( 'remove', $item ).masonry('layout');
        });
    }

    // add X-CSRF-Token to add AJAX calls
    $.ajaxSetup({
       headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') }
    });

    // call controller by AJAX
    function getImages(){
        if($('#keywords').val().trim().length > 0){
            $.ajax({
                    url: '/search/' + $('#keywords').val(),
                    type: 'GET',
                    dataType: 'JSON'
                }).done(function(data) {
                    var items = "";
                    $.each(data, function() {
                        items += "<div class='grid-item' data-photo-id="+JSON.stringify(this[1])+"><div class='ibox'><div class='ibox-content'><img alt='image' class='img-responsive' src="+JSON.stringify(this[0])+"></div></div></div>";
                    });
                    allElements = $(items).filter(".grid-item");   
                    grid.append(allElements).imagesLoaded(function(){
                    grid.masonry( 'appended', allElements, true );
                    loading.toggleClass('hidden');
                    scrollArea.toggleClass('shader');
                    }); 
                }).fail(function(jqXHR, textStatus) {
                    alertToastr(500,3000,'error','Service down! Request failed: ' + textStatus);
                });
        } else{
            alertToastr(500,2000,'error','Please type some keywords to search.');
        }
    }   

    // get image contents after selection
    function getImageContents(photoId){
        $.ajax({
            url: '/getPhotoInfo/' + photoId,
            type: 'GET',
            dataType: 'JSON'
        }).done(function(data) {
            $.each(data, function() {
                if(!textarea.tagExist(this.toString())){
                    textarea.importTags(textarea.val() +","+ this.toString());
                }
            });
        }).fail(function(jqXHR, textStatus) {
            alert("Request failed: " + textStatus);
        });
    }
</script>
@yield('page-scripts')

</html>