/*
 * WHMCS Stripe ACH Javascript
 *
 * @copyright Copyright (c) WHMCS Limited 2005-2019
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */
function initStripeACH()
{
    var paymentForm = jQuery('#frmPayment');

    if (paymentForm.length) {
        jQuery('#inputBankAcctType').closest('div.form-group').slideUp().remove();
        jQuery('#inputBankAcctHolderName').closest('div.form-group').slideUp().remove();
        jQuery('#inputBankRoutingNum').closest('div.form-group').slideUp().remove();
        jQuery('#inputBankName').closest('div.form-group').slideUp().remove();
        jQuery('#inputBankAcctNum').closest('div.form-group').slideUp().remove();
        jQuery('#billingAddressChoice').closest('div.form-group').slideUp().remove();
        var btnContainer = jQuery('#btnSubmitContainer'),
            currentSelection = jQuery('input[name="paymethod"]:checked').val();

        paymentForm.off('submit');
        btnContainer.find('button').addClass('disabled').prop('disabled', true);

        btnContainer.before(
            '<div class="form-group bank-details">\n' +
            '    <label for="inputBankDetails" class="col-sm-4 control-label">\n' +
            '        \n' +
            '    </label>\n' +
            '    <div class="col-sm-6">\n' +
            '        <button type="button" class="btn btn-default form-control" id="inputBankDetails">' +
            '            <i class="fal fa-plus-circle"></i> Add Bank Information' +
            '        </button>' +
            '    </div>\n' +
            '</div>'
        );
        var bankDetailsId = '#inputBankDetails',
            inputBankDetails = jQuery(bankDetailsId);

        if (currentSelection === 'new') {
            if (inputBankDetails.not(':visible')) {
                inputBankDetails.show('fast');
            }
            jQuery(document).on('click', bankDetailsId, handler_open);
        } else {
            if (inputBankDetails.is(':visible')) {
                inputBankDetails.hide('fast');
            }
            jQuery(document).off('click', bankDetailsId);
            btnContainer.find('button').removeClass('disabled').prop('disabled', false);
        }

        jQuery('input[name="paymethod"]').on('ifChecked', function() {
            inputBankDetails = jQuery('#inputBankDetails');
            if (jQuery(this).val() === 'new') {
                if (inputBankDetails.not(':visible')) {
                    inputBankDetails.show('fast');
                }
                jQuery(document).on('click', bankDetailsId, handler_open);
            } else {
                if (inputBankDetails.is(':visible')) {
                    inputBankDetails.hide('fast');
                }
                jQuery(document).on('click', bankDetailsId, handler_open);
                btnContainer.find('button').removeClass('disabled').prop('disabled', false);
            }
        });
    }
}

function stripe_ach_reset_input_to_new()
{
    jQuery('input[name="paymethod"][value="new"]').iCheck('check');

    setTimeout(function() {
        jQuery('.gateway-errors').hide().addClass('hidden');
    }, 4000);
}

function handler_open() {
    var frm = jQuery('#frmPayment'),
        displayError = jQuery('.gateway-errors').first(),
        linkHandler = Plaid.create(
        {
            env: plaidEnvironment,
            clientName: companyName,
            key: plaidPublicKey,
            product: ['auth'],
            selectAccount: true,
            onSuccess: function(public_token, metadata) {
                WHMCS.http.jqClient.jsonPost({
                    url: WHMCS.utils.getRouteUrl('/stripe_ach/token/exchange'),
                    data: {
                        token: csrfToken,
                        public_token: public_token,
                        account_id: metadata.account_id
                    },
                    success: function(response) {
                        token = response.token;
                        frm.append(
                            jQuery('<input type="hidden" name="remoteStorageToken">').val(token)
                        );
                        var btnContainer = jQuery('#btnSubmitContainer'),
                            inputBankDetails = jQuery('#inputBankDetails');

                        inputBankDetails.prop('disabled', true).addClass('disabled');
                        btnContainer.find('button').removeClass('disabled').prop('disabled', false);
                        btnContainer.find('button').click();
                        btnContainer.find('button').addClass('disabled').prop('disabled', true);

                    },
                    warning: function(error) {
                        displayError.html(error);
                        if (displayError.hasClass('hidden')) {
                            displayError.removeClass('hidden').show();
                        }
                        scrollToGatewayInputError();
                    },
                    fail: function(error) {
                        displayError.html(error);
                        if (displayError.hasClass('hidden')) {
                            displayError.removeClass('hidden').show();
                        }
                        scrollToGatewayInputError();
                    }
                });
            },
            onExit: function(err, metadata) {
                if (err === null) {
                    if (metadata && metadata.status === 'requires_credentials' && !metadata.link_session_id) {
                        err = 'Incorrect Plaid credentials provided. Please contact support.';
                    }
                }

                if (err != null) {
                    displayError.html(err);
                    if (displayError.hasClass('hidden')) {
                        displayError.removeClass('hidden').show();
                    }
                    scrollToGatewayInputError();
                }
            },
        }
    );
    linkHandler.open();
}
