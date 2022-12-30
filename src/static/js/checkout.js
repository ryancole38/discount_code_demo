
function onDiscountCodeApply() {
    $.ajax(
        {
            url: 'http://localhost:8000/checkout',
            type: 'GET',
            success: function(data) {
                console.log(data);
            },
            error: function(data) {
                console.error(data);
            }
        }
    )
}