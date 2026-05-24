/**
 * LoyalSystem — Admin JavaScript
 *
 * window.lsAdmin — { ajaxUrl, nonce, i18n }   (wp_localize_script)
 *
 * @package LoyalSystem
 */

/* global jQuery, lsAdmin */

(function ( $ ) {
    'use strict';

    /* =========================================================================
       HELPERS
       ========================================================================= */

    function lsAdminMsg( $el, type, text ) {
        if ( ! $el || ! $el.length ) { return; }
        $el.removeClass( 'ts-admin-msg--success ls-admin-msg--error ls-admin-msg--info' )
           .addClass( 'ts-admin-msg--' + type )
           .text( text )
           .show();
    }

    function lsAdminClearMsg( $el ) {
        if ( $el && $el.length ) { $el.hide().text( '' ); }
    }

    function extractError( response ) {
        if ( response && response.data && response.data.message ) {
            return response.data.message;
        }
        if ( response && response.responseText ) {
            try {
                var r = JSON.parse( response.responseText );
                if ( r.data && r.data.message ) { return r.data.message; }
            } catch ( e ) {}
        }
        return ( lsAdmin && lsAdmin.i18n && lsAdmin.i18n.error ) || 'An unexpected error occurred.';
    }

    /* =========================================================================
       INVOICES PAGE
       ========================================================================= */

    if ( $( '#ls-invoice-form' ).length ) {

        var currencyRates = {};

        // Populate rates from inline data if present.
        if ( typeof lsAdminCurrencies !== 'undefined' ) {
            currencyRates = lsAdminCurrencies;
        }

        function toBaseCurrency( amount, currencyCode ) {
            if ( ! currencyCode || currencyCode === 'GNF' ) {
                return amount;
            }
            var rate = currencyRates[ currencyCode ] ? parseFloat( currencyRates[ currencyCode ] ) : 1;
            if ( ! rate ) { return amount; }
            return Math.round( amount / rate * 100 ) / 100;
        }

        function updateGnfPreview() {
            var amount   = parseFloat( $( '#ls-invoice-amount' ).val() ) || 0;
            var currency = $( '#ls-invoice-currency' ).val();
            var $preview = $( '#ls-gnf-preview' );

            if ( ! $preview.length ) { return; }

            if ( currency && currency !== 'GNF' ) {
                var gnf = toBaseCurrency( amount, currency );
                $preview.text( '≈ ' + gnf.toLocaleString() + ' GNF' ).show();
            } else {
                $preview.hide().text( '' );
            }
        }

        $( document ).on( 'input change', '#ls-invoice-amount, #ls-invoice-currency', updateGnfPreview );

        // Customer search.
        var customerSearchTimer;
        $( document ).on( 'input', '#ls-customer-phone', function () {
            clearTimeout( customerSearchTimer );
            var phone = $.trim( $( this ).val() );
            var $result = $( '#ls-customer-search-result' );

            $result.hide().text( '' );

            if ( phone.length < 6 ) { return; }

            customerSearchTimer = setTimeout( function () {
                $.post( lsAdmin.ajaxUrl, {
                    action : 'ls_admin_search_customer',
                    nonce  : lsAdmin.nonce,
                    phone  : phone
                } )
                .done( function ( resp ) {
                    if ( resp.success && resp.data ) {
                        var c = resp.data;
                        $result.text( c.full_name ? c.full_name + ' — ' + c.phone : c.phone ).show();
                    } else {
                        $result.text( 'Customer not found' ).show();
                    }
                } );
            }, 400 );
        } );

        // Invoice form submit.
        $( document ).on( 'submit', '#ls-invoice-form', function ( e ) {
            e.preventDefault();
            var $btn  = $( '#ls-add-invoice-btn' );
            var $msg  = $( '#ls-invoice-msg' );

            lsAdminClearMsg( $msg );
            $btn.prop( 'disabled', true ).find( '.ls-spinner' ).show();

            $.post( lsAdmin.ajaxUrl, {
                action      : 'ls_admin_add_invoice',
                nonce       : lsAdmin.nonce,
                phone       : $.trim( $( '#ls-customer-phone' ).val() ),
                amount      : $( '#ls-invoice-amount' ).val(),
                currency    : $( '#ls-invoice-currency' ).val(),
                description : $.trim( $( '#ls-invoice-description' ).val() ),
                reference   : $.trim( $( '#ls-invoice-reference' ).val() )
            } )
            .done( function ( resp ) {
                if ( resp.success ) {
                    lsAdminMsg( $msg, 'success', resp.data.message || 'Invoice added.' );
                    $( '#ls-invoice-form' ).trigger( 'reset' );
                    $( '#ls-customer-phone' ).val( '+224' );
                    $( '#ls-customer-search-result' ).hide().text( '' );
                    $( '#ls-gnf-preview' ).hide().text( '' );
                    // Reload invoice list if function exists.
                    if ( typeof tsLoadInvoices === 'function' ) { tsLoadInvoices(); }
                } else {
                    lsAdminMsg( $msg, 'error', extractError( resp ) );
                }
            } )
            .fail( function ( jqXHR ) {
                lsAdminMsg( $msg, 'error', extractError( jqXHR ) );
            } )
            .always( function () {
                $btn.prop( 'disabled', false ).find( '.ls-spinner' ).hide();
            } );
        } );
    }

    /* =========================================================================
       CREDITS PAGE
       ========================================================================= */

    if ( $( '#ls-credits-wrap' ).length ) {

        $( document ).on( 'submit', '#ls-customer-lookup-form', function ( e ) {
            e.preventDefault();
            var phone = $.trim( $( '#ls-lookup-phone' ).val() );
            var $msg  = $( '#ls-credits-msg' );
            var $wrap = $( '#ls-ledger-table-wrap' );

            lsAdminClearMsg( $msg );
            $wrap.hide();

            if ( ! phone ) {
                lsAdminMsg( $msg, 'error', 'Please enter a phone number.' );
                return;
            }

            $( '#ls-lookup-btn' ).prop( 'disabled', true ).find( '.ls-spinner' ).show();

            $.post( lsAdmin.ajaxUrl, {
                action : 'ls_admin_get_balance',
                nonce  : lsAdmin.nonce,
                phone  : phone
            } )
            .done( function ( resp ) {
                if ( resp.success ) {
                    var d = resp.data;
                    $( '#ls-credits-balance' ).text( d.balance ? d.balance.toLocaleString() + ' GNF' : '0 GNF' );
                    $( '#ls-credits-customer-name' ).text( d.full_name || d.phone || phone );

                    // Render ledger rows.
                    var rows = d.ledger || [];
                    var $tbody = $( '#ls-ledger-tbody' );
                    $tbody.empty();
                    if ( rows.length ) {
                        $.each( rows, function ( i, row ) {
                            var amtClass = row.type === 'credit' ? 'ts-amount-credit' : 'ts-amount-debit';
                            var sign     = row.type === 'credit' ? '+' : '-';
                            $tbody.append(
                                '<tr>' +
                                '<td>' + escHtml( row.created_at || '' ) + '</td>' +
                                '<td><span class="' + amtClass + '">' + sign + parseFloat( row.amount ).toLocaleString() + '</span></td>' +
                                '<td>' + escHtml( row.type || '' ) + '</td>' +
                                '<td>' + escHtml( row.description || '' ) + '</td>' +
                                '</tr>'
                            );
                        } );
                    } else {
                        $tbody.append( '<tr><td colspan="4" style="text-align:center;color:#888;">No ledger entries.</td></tr>' );
                    }

                    $wrap.show();
                } else {
                    lsAdminMsg( $msg, 'error', extractError( resp ) );
                }
            } )
            .fail( function ( jqXHR ) {
                lsAdminMsg( $msg, 'error', extractError( jqXHR ) );
            } )
            .always( function () {
                $( '#ls-lookup-btn' ).prop( 'disabled', false ).find( '.ls-spinner' ).hide();
            } );
        } );
    }

    /* =========================================================================
       TICKETS PAGE
       ========================================================================= */

    if ( $( '#ls-tickets-wrap' ).length ) {

        // Toggle expand row.
        $( document ).on( 'click', '.ls-ticket-toggle', function () {
            var ticketId  = $( this ).data( 'id' );
            var $expandRow = $( '#ls-ticket-expand-' + ticketId );
            $expandRow.toggle();
        } );

        // Update ticket.
        $( document ).on( 'submit', '.ls-ticket-update-form', function ( e ) {
            e.preventDefault();
            var $form    = $( this );
            var ticketId = $form.data( 'id' );
            var $msg     = $( '#ls-ticket-msg-' + ticketId );
            var $btn     = $form.find( '.ls-update-btn' );

            $btn.prop( 'disabled', true );

            $.post( lsAdmin.ajaxUrl, {
                action      : 'ls_admin_update_ticket',
                nonce       : lsAdmin.nonce,
                ticket_id   : ticketId,
                status      : $form.find( '[name="status"]' ).val(),
                admin_notes : $form.find( '[name="admin_notes"]' ).val()
            } )
            .done( function ( resp ) {
                if ( resp.success ) {
                    lsAdminMsg( $msg, 'success', resp.data.message || 'Ticket updated.' );
                    // Update status badge in the main row.
                    var newStatus = $form.find( '[name="status"]' ).val();
                    $( '#ls-ticket-row-' + ticketId ).find( '.ls-badge' )
                        .text( newStatus.replace( '_', ' ' ) )
                        .removeClass()
                        .addClass( 'ts-badge ls-status-' + newStatus );
                } else {
                    lsAdminMsg( $msg, 'error', extractError( resp ) );
                }
            } )
            .fail( function ( jqXHR ) {
                lsAdminMsg( $msg, 'error', extractError( jqXHR ) );
            } )
            .always( function () {
                $btn.prop( 'disabled', false );
            } );
        } );

        // Delete ticket.
        $( document ).on( 'click', '.ls-ticket-delete-btn', function () {
            var ticketId = $( this ).data( 'id' );
            if ( ! confirm( 'Delete ticket #' + ticketId + '? This cannot be undone.' ) ) { return; }

            var $btn = $( this );
            $btn.prop( 'disabled', true );

            $.post( lsAdmin.ajaxUrl, {
                action    : 'ls_admin_delete_ticket',
                nonce     : lsAdmin.nonce,
                ticket_id : ticketId
            } )
            .done( function ( resp ) {
                if ( resp.success ) {
                    $( '#ls-ticket-row-' + ticketId ).remove();
                    $( '#ls-ticket-expand-' + ticketId ).remove();
                } else {
                    alert( extractError( resp ) );
                    $btn.prop( 'disabled', false );
                }
            } )
            .fail( function ( jqXHR ) {
                alert( extractError( jqXHR ) );
                $btn.prop( 'disabled', false );
            } );
        } );

        // View images.
        $( document ).on( 'click', '.ls-ticket-images-btn', function () {
            var ticketId = $( this ).data( 'id' );

            $.post( lsAdmin.ajaxUrl, {
                action    : 'ls_admin_get_ticket_images',
                nonce     : lsAdmin.nonce,
                ticket_id : ticketId
            } )
            .done( function ( resp ) {
                if ( resp.success && resp.data.images && resp.data.images.length ) {
                    var html = '<div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;">';
                    $.each( resp.data.images, function ( i, src ) {
                        html += '<a href="' + escHtml( src ) + '" target="_blank">' +
                                '<img src="' + escHtml( src ) + '" style="width:80px;height:80px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">' +
                                '</a>';
                    } );
                    html += '</div>';
                    $( '#ls-ticket-images-' + ticketId ).html( html ).show();
                } else {
                    $( '#ls-ticket-images-' + ticketId ).text( 'No images.' ).show();
                }
            } );
        } );
    }

    /* =========================================================================
       SETTINGS PAGE — SMS PROVIDER TOGGLE
       ========================================================================= */

    if ( $( '#ls-settings-form' ).length ) {

        function toggleSmsFields() {
            var provider = $( '#ls-sms-provider' ).val();
            $( '#ls-twilio-fields' ).toggle( provider === 'twilio' );
            $( '#ls-http-fields' ).toggle( provider === 'http' );
        }

        $( document ).on( 'change', '#ls-sms-provider', toggleSmsFields );
        toggleSmsFields();

        // Test SMS.
        $( document ).on( 'click', '#ls-sms-test-btn', function () {
            var phone = $.trim( $( '#ls-sms-test-phone' ).val() );
            var $btn  = $( this );
            var $msg  = $( '#ls-sms-test-msg' );
            var $codeWrap = $( '#ls-sms-test-code-wrap' );

            lsAdminClearMsg( $msg );
            $codeWrap.hide();

            if ( ! phone ) {
                lsAdminMsg( $msg, 'error', 'Enter a phone number to test.' );
                return;
            }

            $btn.prop( 'disabled', true ).find( '.ls-spinner' ).show();

            $.post( lsAdmin.ajaxUrl, {
                action : 'ls_admin_test_sms',
                nonce  : lsAdmin.nonce,
                phone  : phone
            } )
            .done( function ( resp ) {
                if ( resp.success ) {
                    lsAdminMsg( $msg, 'success', resp.data.message || 'SMS sent.' );
                    if ( resp.data.code ) {
                        $codeWrap.find( '.ls-code-badge' ).text( resp.data.code );
                        $codeWrap.show();
                    }
                } else {
                    lsAdminMsg( $msg, 'error', extractError( resp ) );
                }
            } )
            .fail( function ( jqXHR ) {
                lsAdminMsg( $msg, 'error', extractError( jqXHR ) );
            } )
            .always( function () {
                $btn.prop( 'disabled', false ).find( '.ls-spinner' ).hide();
            } );
        } );
    }

    /* =========================================================================
       UTILITY
       ========================================================================= */

    function escHtml( str ) {
        return String( str )
            .replace( /&/g,  '&amp;' )
            .replace( /</g,  '&lt;'  )
            .replace( />/g,  '&gt;'  )
            .replace( /"/g,  '&quot;' )
            .replace( /'/g,  '&#039;' );
    }

} )( jQuery );
