
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
        minimumOrderAmount: 0
    };

    function setObjectFromInputValue(id) {
        discountCode[id] = form.find('#' + id).val();
    }

    // Populate discount code from form elements
    Object.keys(discountCode).forEach(setObjectFromInputValue);

    if (checkForAndSetDiscountCreationErrors(discountCode)){
        let response = makeAsyncRequest(
            '/discount_codes/create/creatediscount',
            discountCode     
        ).then((res) => {
            window.location.href = 'http://localhost:8000/discount_codes/admin';
        });
    }

}

function checkForAndSetDiscountCreationErrors(discountCode) {
    let validationFunctions = {
        codeString: validateCodeString,
        startDate: validateDateString,
        endDate: validateDateString,
        discountAmount: validateUnsigned,
        timesRedeemable: validateUnsignedInt,
        minimumOrderAmount: validateUnsigned
    }

    let hadError = false;
    Object.keys(validationFunctions).forEach((fieldId) => {
        let validationFn = validationFunctions[fieldId];
        if (!validationFn(discountCode[fieldId])) {
            hadError = true;
            setError(fieldId);
        }
        else {
            // Clear error just in case it was previously set
            clearError(fieldId);
        }
    });
    return !hadError;
}

function setError(fieldId) {
    let element = $('#' + fieldId);
    // Todo: check if element exists
    element.addClass('error');
}

function clearError(fieldId) {
    let element = $('#' + fieldId);
    element.removeClass('error');
}

function validateUnsigned(numericString) {
    // Match arbitrary # of digits, but if there is a decimal
    // then there must be at least one digit following
    // Valid: 0, 0.1, 1.5
    // Not valid: 0. , . , .1
    const unsignedExpr = /[0-9]{1,}(\.[0-9]{1,}){0,1}/
    return (numericString.match(unsignedExpr) !== null);
}

function validateUnsignedInt(numericString) {
    const uintExpr = /[0-9]{1}/;
    return (numericString.match(uintExpr) !== null);
}

function validateCodeString(codeString) {
    const codeExpr = /[a-zA-Z0-9]{1,}/;
    return (codeString.match(codeExpr) !== null);
}

function validateDateString(dateString) {
    // Match MM/DD/YYYY
    const dateExpr = /([0-9]{2})\/([0-9]{2})\/([0-9]{4})/
    let matches = dateString.match(dateExpr);
    if (matches === null) {
        return false;
    }
    let month = matches[1];
    let date = matches[2];
    let year = matches[3];

    let dateObj = new Date(`${year}-${month}-${date}`);
    return (dateObj !== "Invalid Date" && !isNaN(dateObj));
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

function makeApplyDiscountRequest(data) {
    makeAsyncRequest(
        '/checkout/applydiscount',
        data
    ).then((response) => {
        console.log(response);
        response = JSON.parse(response);
        setError('discount-code');
        if (response['success']) {
            updateViewContent(response['view']);
        }
    });
}

function onSubmitOrder() {
    let appliedDiscount = $('#applied-discount-code').text();
    let artistName = $('#applied-artist-name').text();
    let data = {};
    if (appliedDiscount) {
        data['code'] = appliedDiscount;
    }
    if (artistName) {
        data['artist'] = artistName;
    }

    makeSubmitOrderRequest(data);
}

function makeSubmitOrderRequest(data) {
    makeAsyncRequest(
        '/checkout/submit', 
        data
    ).then((res) => {
        console.log(res);
        let response = JSON.parse(res);
        if (response['success']){
            // redirect to "thank you" page
            window.location.href = 'http://localhost:8000/order_confirmation'
        } else {
            // TODO: specify WHY. It's probably because the code
            // was used too many times, which could be specified in the
            // response.
            alert('Failed to submit order');
        }
    });
}

function onDiscountCodeApply() {
    let discountInputBox = $('#discount-code');
    if (discountInputBox.length) {
        let discountCode = discountInputBox.val();
        makeApplyDiscountRequest({'code': discountCode}); 
        return;
    }
    let appliedDiscount = $('#applied-discount-code').text();
    let selectedArtist = $('#discountArtistName').val();
    makeApplyDiscountRequest({
        'code': appliedDiscount,
        'artist': selectedArtist
    });
}

function onDiscountCodeRemove() {
    // Make an empty request to re-load the page
    makeApplyDiscountRequest({});
}

function updateViewContent(content) {
    $('#content').html(content);
}

function addAppliedDiscount(discount) {
    let amount = discount['amount'];
    let id = discount['id'];
    let appliedDiscountsList = $('#applied-discounts-list');

}