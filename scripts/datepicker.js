$(document).ready(function() {

    // Single datepicker
    $('.datepicker').datepicker({
        weekStart: 1,
        language: 'nl',
        autoclose: true,
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        startDate: 'd'
    });


    // Datepicker for a modal    
    $('body').on('focus', '.datepicker', function() {

        $('.datepicker').datepicker({
            weekStart: 1,
            language: 'nl',
            autoclose: true,
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            startDate: 'd'
        });

    });
    
    
    // Date range    
    $('#js-daterange input').each(function() {
        $(this).datepicker({
            weekStart: 1,
            language: 'nl',
            autoclose: true,
            format: 'dd-mm-yyyy',
            todayHighlight: true
        });
    });
    
    $('input[type="range"]').val(8).change();
    
});
