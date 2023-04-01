$(document).ready(function(){
    $('[data-bs-toggle="tooltip"]').tooltip();
    $('[data-bs-toggle="popover"]').popover({container: 'body',trigger: 'focus'});
    $('.toast').toast('show');
});