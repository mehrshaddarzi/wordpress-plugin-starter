jQuery(document).ready(function ($) {
  // Ajax Url
  const ajax_url = wp_plugin.ajax;
  
  // Example Ajax
  /*$(document).on('click', '#button', function (e) {
        e.preventDefault();
        
        $.ajax({
            url: ajax_url,
            type: 'GET',
            dataType: "json",
            contentType: "application/json; charset=utf-8",
            cache: false,
            data: {
             'action': '' 
            },
            success: function (data, textStatus, xhr) {
                $("#tag-id").html(data.html);
            },
            error: function (xhr, status, error) {
                $("#tag-id").html(xhr.responseJSON.html);
            }
        });
    });*/

});
