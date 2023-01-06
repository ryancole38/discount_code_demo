
async function makeAsyncRequest(route, dataToSend) {
    return $.ajax(
        {
            url: 'http://localhost:8000' + route,
            type: 'GET',
            data: dataToSend,
            success: function(data) {
                //console.log(data);
            },
            error: function(data) {
                //console.error(data);
            }
        }
    );
}

function onSubmitDiscountCodeChanges() {
    let form = $('#discount-code-value-form');
    
    // Return if we couldn't find the form
    if (!form) {
        return;
    }

    let discountCode = {
        id: 0,
        artistId: 0,
        merchTypeId: 0,
        codeString: '',
        discountMessage: '',
        startDate: '',
        endDate: '',
        discountType: 0,
        discountAmount: 0,
        timesRedeemable: 0,
        isStackable: false,
        minimumOrderAmount: 0
    };

    function setObjectFromInputValue(id) {
        discountCode[id] = form.find('#' + id).val();
    }

    // Populate discount code from form elements
    Object.keys(discountCode).forEach(setObjectFromInputValue);
    // Fix isStackable since it's a checkbox
    discountCode['isStackable'] = form.find('#isStackable').is(':checked');

    let response = makeAsyncRequest(
        '/discount_codes/create/creatediscount',
        discountCode     
    );

}

async function onDeleteDiscountCodeClicked(id) {
    makeAsyncRequest(
        '/discount_codes/admin/delete',
        {
            'id': id
        }
    ).then((res) => {
        if (res === "true") {
            $('#discount-code-table tr#' + id).remove();
        }
    });
}

function onDiscountCodeApply() {
    let discountInputBox = $('#discount-code');
    let discountCode = discountInputBox.val();
    makeAsyncRequest(
        '/checkout/applydiscount',
        {
            'code': discountCode
        }
    ).then((response) => {
        // Reset input box to have no value
        response = JSON.parse(response);
        discountInputBox.val("");

        if (response['success']) {
            updateViewContent(response['view']);
        }

    });
}

function updateViewContent(content) {
    $('#view').replaceWith(content);
}

function addAppliedDiscount(discount) {
    let amount = discount['amount'];
    let id = discount['id'];
    let appliedDiscountsList = $('#applied-discounts-list');

}