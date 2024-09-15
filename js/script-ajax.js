jQuery(function ($){
    function createDialog(){
        const dialog = document.createElement('dialog');
        dialog.setAttribute("id", "successDialog");
        dialog.innerHTML = '<div class="dialog-inner"></div> <form method="dialog"><button>Close</button></form>';
        document.body.appendChild(dialog);
    }

    $("#tfp_form").on("submit", function (e){
        e.preventDefault();
        const form = this;

        const formData = {
            action: 'custom_form_submission',
            fname: $('input[name="fname"]').val(),
            lname: $('input[name="lname"]').val(),
            email: $('input[name="email"]').val(),
            phone: $('input[name="phone"]').val(),
            service_address: $('input[name="service-address"]').val(),
        };

        $.ajax({
            url: ajax_object.ajaxurl,
            data: formData,
            type: 'POST',
            success: function(response) {
                createDialog();

                const dialog = $("#successDialog");
                const dialogInner = $("#successDialog .dialog-inner");
                response = JSON.parse(response)

                if(response.success) {
                    dialog.css('color', '#10bf0c');
                    dialogInner.html("").html(response.message);
                    form.reset()
                }else{
                    dialog.css('color', '#f00');
                    dialogInner.html("").html("ERROR: " + response.message)
                }

                dialog[0].showModal();
            },
        })
    });
});