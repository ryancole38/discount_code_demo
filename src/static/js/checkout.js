
function onDiscountCodeApply() {
    let discountInputBox = $('#discount-code');
    let discountCode = discountInputBox.val();
    console.log(discountCode);
    $.ajax(
        {
            url: 'http://localhost:8000/checkout/applydiscount',
            type: 'GET',
            data: {'code': discountCode},
            success: function(data) {
                console.log(data);
            },
            error: function(data) {
                console.error(data);
            }
        }
    )
}