/*
 * WHMCS Stripe SEPA Javascript
 *
 * @copyright Copyright (c) WHMCS Limited 2005-2019
 * @license https://www.whmcs.com/license/ WHMCS Eula
 */
function initStripeSEPA()
{
    var paymentForm = jQuery('#frmPayment');

    if (paymentForm.length) {
        jQuery('#inputBankAcctType').closest('div.form-group').slideUp().remove();
        jQuery('#inputBankRoutingNum').closest('div.form-group').slideUp().remove();
        jQuery('#inputBankName').closest('div.form-group').slideUp().remove();
        jQuery('#inputBankAcctNum').closest('div.form-group').slideUp().remove();
        jQuery('#billingAddressChoice').closest('div.form-group').slideUp().remove();
        jQuery('#inputDescriptionContainer').before(
            '<div id="ibanElementContainer" class="form-group bank-details">\n' +
            '    <label for="ibanElement" class="col-sm-4 control-label">\n' +
            lang.iban +
            '    </label>\n' +
            '<div class="col-sm-7">' +
            '    <div class="input-inline form-control" id="ibanElement">\n' +
            '</div>' +
            '    </div>\n' +
            '</div>' +
            '<div id="mandateAcceptanceContainer" class="form-group bank-details">\n' +
            '    <div class="alert alert-info" id="mandateAcceptance">\n' +
            lang.mandate_acceptance +
            '    </div>\n' +
            '</div>'

        );

        var currentSelection = jQuery('input[name="paymethod"]:checked').val(),
            ibanElementContainer = jQuery('#ibanElementContainer'),
            mandateAcceptanceContainer = jQuery('#mandateAcceptanceContainer'),
            bankAccountHolderContainer = jQuery('#inputBankAcctHolderName').closest('div.form-group');

        iban.mount('#ibanElement');

        paymentForm.off('submit');
        iban.off('change');

        if (currentSelection === 'new') {
            if (ibanElementContainer.not(':visible')) {
                ibanElementContainer.show('fast');
                iban.on('change', stripe_sepa_iban_change_event);
                paymentForm.on('submit.stripe_sepa', stripe_sepa_form_submit);
            }
            if (mandateAcceptanceContainer.not(':visible')) {
                mandateAcceptanceContainer.show('fast');
            }
            if (bankAccountHolderContainer.hasClass('hidden')) {
                bankAccountHolderContainer.hide().removeClass('hidden');
            }
            if (bankAccountHolderContainer.not(':visible')) {
                bankAccountHolderContainer.show('fast');
            }
        } else {
            if (ibanElementContainer.is(':visible')) {
                ibanElementContainer.hide('fast');
            }
            if (mandateAcceptanceContainer.is(':visible')) {
                mandateAcceptanceContainer.hide('fast');
            }
            if (bankAccountHolderContainer.is(':visible')) {
                bankAccountHolderContainer.hide('fast');
            }
        }

        jQuery('input[name="paymethod"]').on('ifChecked', function() {
            ibanElementContainer = jQuery('#ibanElementContainer');
            mandateAcceptanceContainer = jQuery('#mandateAcceptanceContainer');
            bankAccountHolderContainer = jQuery('#inputBankAcctHolderName').closest('div.form-group');
            if (jQuery(this).val() === 'new') {
                if (ibanElementContainer.not(':visible')) {
                    ibanElementContainer.show('fast');
                    iban.on('change', stripe_sepa_iban_change_event);
                    paymentForm.on('submit.stripe_sepa', stripe_sepa_form_submit);
                }
                if (mandateAcceptanceContainer.not(':visible')) {
                    mandateAcceptanceContainer.show('fast');
                }
                if (bankAccountHolderContainer.hasClass('hidden')) {
                    bankAccountHolderContainer.hide().removeClass('hidden');
                }
                if (bankAccountHolderContainer.not(':visible')) {
                    bankAccountHolderContainer.show('fast');
                }
            } else {
                if (ibanElementContainer.is(':visible')) {
                    ibanElementContainer.hide('fast');
                    iban.off('change');
                    paymentForm.off('submit.stripe_sepa');
                }
                if (mandateAcceptanceContainer.is(':visible')) {
                    mandateAcceptanceContainer.hide('fast');
                }
                if (bankAccountHolderContainer.is(':visible')) {
                    bankAccountHolderContainer.hide('fast');
                }
            }
        });
    }
}

function stripe_sepa_iban_change_event(event)
{
    var displayError = jQuery('.gateway-errors').first(),
        frm = displayError.closest('form');
    // Handle real-time validation errors from the iban Element.
    if (event.error && event.error.message.length) {
        displayError.html(event.error.message);
        if (displayError.hasClass('hidden')) {
            displayError.removeClass('hidden').show();
        }
        frm.find('button[type="submit"]').prop('disabled', false)
            .removeClass('disabled')
            .find('span')
            .toggleClass('hidden');
        scrollToGatewayInputError();
    } else {
        if (!displayError.hasClass('hidden')) {
            displayError.hide().addClass('hidden');
        }
    }
}

function stripe_sepa_form_submit(event)
{
    var submitButton = jQuery('#btnSubmit').addClass('disabled').prop('disabled', true);

    event.preventDefault();
    var sourceData = {
        type: 'sepa_debit',
        currency: 'eur',
        owner: {
            name: jQuery('#inputBankAcctHolderName').val(),
            email: clientEmail,
        },
        mandate: {
            // Automatically send a mandate notification email to your customer
            // once the source is charged.
            notification_method: 'email',
        }
    };

    stripe.createSource(iban, sourceData).then(function(result) {
        var displayError = jQuery('.gateway-errors').first();
        if (result.error && result.error.message.length) {
            // Inform the customer that there was an error.
            displayError.html(result.error.message);
            if (displayError.hasClass('hidden')) {
                displayError.removeClass('hidden').show();
                submitButton.removeClass('disabled').prop('disabled', false);
            }
        } else {
            // Send the Source to your server to create a charge.
            displayError.addClass('hidden');
            stripeSourceHandler(result.source);
        }
    });
}

function stripeSourceHandler(source) {
    // Insert the Source ID into the form so it gets submitted to the server.
    var paymentForm = document.getElementById('frmPayment'),
        hiddenInput = document.createElement('input');
    hiddenInput.setAttribute('type', 'hidden');
    hiddenInput.setAttribute('name', 'remoteStorageToken');
    hiddenInput.setAttribute('value', source.id);
    paymentForm.appendChild(hiddenInput);
    // Submit the form.
    paymentForm.submit();
}
