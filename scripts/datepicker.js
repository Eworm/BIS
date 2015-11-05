$(document).ready(function() {

    $('.datepicker').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        startDate: 'd'
    });
    
    $('body').on('focus', '.datepicker', function() {

        $('.datepicker').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        startDate: 'd'
    });

    });
    
});
