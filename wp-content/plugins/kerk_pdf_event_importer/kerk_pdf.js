jQuery(document).ready(function($){
    $('#kerk_pdf_upload').on('click', function(){
        var file = $('#kerk_pdf_file')[0].files[0];
        if(!file){ alert('Select a PDF file.'); return; }
        var formData = new FormData();
        formData.append('action', 'kerk_pdf_extract');
        formData.append('nonce', kerkPdfAjax.nonce);
        formData.append('file', file);
        $('#kerk_pdf_result').text('Extracting...');
        $.ajax({
            url: kerkPdfAjax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res){
                if(res.success){
                    $('#kerk_pdf_textbox').val(res.data);
                    $('#kerk_pdf_result').text('Extraction complete.');
                }else{
                    $('#kerk_pdf_result').text('Error: '+res.data);
                }
            },
            error: function(){
                $('#kerk_pdf_result').text('AJAX error.');
            }
        });
    });
    $('#kerk_pdf_process').on('click', function(){
        var data = $('#kerk_pdf_textbox').val();
        $('#kerk_pdf_result').text('Processing...');
        $.post(kerkPdfAjax.ajax_url, {
            action: 'kerk_process_events',
            nonce: kerkPdfAjax.nonce,
            data: data
        }, function(res){
            if(res.success){
                $('#kerk_pdf_result').text(res.data);
            }else{
                $('#kerk_pdf_result').text('Error: '+res.data);
            }
        });
    });
});
