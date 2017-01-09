function alertToastr(timeout, optionTimeout, toastrType, message) {
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
        switch (toastrType) {
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