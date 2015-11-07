$(document).ready(function() {

    // Single datepicker
    $('.datepicker').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        startDate: 'd'
    });


    // Datepicker for a modal    
    $('body').on('focus', '.datepicker', function() {

        $('.datepicker').datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            startDate: 'd'
        });

    });
    
    
    // Date range    
    $('#js-daterange input').each(function() {
        $(this).datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',
            todayHighlight: true
        });
    });
    
    $('input[type="range"]').val(2).change();
    
});
