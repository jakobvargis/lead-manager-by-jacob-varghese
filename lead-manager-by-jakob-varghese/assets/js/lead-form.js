(function($){
  $(document).on('submit','#lm-lead-form', function(e){
    e.preventDefault();
    var form = $(this);
    var data = {
      action: 'lm_submit_lead',
      name: $('#lm_name').val(),
      email: $('#lm_email').val(),
      phone: $('#lm_phone').val(),
      security: lm_vars.nonce
    };
    form.find('button').attr('disabled', true).text('Submitting...');
    $.post(lm_vars.ajax_url, data, function(resp){
      if (resp.success) {
        $('#lm-result').html('<div style="color:green;">'+resp.data+'</div>');
        form[0].reset();
      } else {
        var msg = resp.data ? resp.data : 'Submission failed.';
        $('#lm-result').html('<div style="color:red;">'+msg+'</div>');
      }
      form.find('button').attr('disabled', false).text('Submit');
    }, 'json').fail(function(){
      $('#lm-result').html('<div style="color:red;">Request error. Try later.</div>');
      form.find('button').attr('disabled', false).text('Submit');
    });
  });
})(jQuery);
