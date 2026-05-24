/**
 * LoyalSystem — Public Frontend JavaScript
 *
 * window.lsPublic   — { ajaxUrl, nonce, i18n }     (wp_localize_script)
 * window.lsLoginData — { currentUrl, redirectUrl, ajaxUrl, nonce }  (inline in login.php)
 *
 * @package LoyalSystem
 */

/* global jQuery, lsPublic, lsLoginData */

(function ( $ ) {
    'use strict';

    /* =========================================================================
       CONFIGURATION
       ========================================================================= */
    var TS = {
        ajaxUrl : ( typeof lsPublic !== 'undefined' && lsPublic.ajaxUrl )
                    ? lsPublic.ajaxUrl
                    : ( typeof lsLoginData !== 'undefined' ? lsLoginData.ajaxUrl : '' ),
        nonce   : ( typeof lsPublic !== 'undefined' && lsPublic.nonce )
                    ? lsPublic.nonce
                    : ( typeof lsLoginData !== 'undefined' ? lsLoginData.nonce : '' ),
        i18n    : ( typeof lsPublic !== 'undefined' && lsPublic.i18n ) ? lsPublic.i18n : {}
    };

    /* =========================================================================
       HELPERS
       ========================================================================= */

    function tsShowMessage( $el, type, message ) {
        if ( ! $el || ! $el.length ) { return; }
        $el.removeClass( 'ts-message--success ls-message--error ls-message--info ls-message--warning' )
           .addClass( 'ts-message--' + type )
           .text( message )
           .show();
    }

    function tsHideMessage( $el ) {
        if ( $el && $el.length ) { $el.hide().text( '' ); }
    }

    function tsSetLoading( $btn, loading ) {
        if ( ! $btn || ! $btn.length ) { return; }
        $btn.prop( 'disabled', loading );
        $btn.find( '.ls-btn-spinner' ).toggle( loading );
    }

    function tsExtractError( response ) {
        if ( typeof response === 'string' ) {
            try { response = JSON.parse( response ); } catch (e) {}
        }
        if ( response && response.data && response.data.message ) {
            return response.data.message;
        }
        if ( response && response.responseText ) {
            try {
                var r = JSON.parse( response.responseText );
                if ( r.data && r.data.message ) { return r.data.message; }
            } catch (e) {}
        }
        return TS.i18n.error || 'An unexpected error occurred.';
    }

    /* =========================================================================
       LOGIN REDIRECT HELPER
       ========================================================================= */

    function tsLoginRedirect() {
        // 1. Check URL query string.
        var match = window.location.search.match( /[?&]redirect_to=([^&]+)/ );
        if ( match ) {
            window.location.href = decodeURIComponent( match[1] );
            return;
        }
        // 2. Fall back to PHP-passed redirectUrl.
        if ( typeof lsLoginData !== 'undefined' && lsLoginData.redirectUrl ) {
            window.location.href = lsLoginData.redirectUrl;
            return;
        }
        window.location.reload();
    }

    /* =========================================================================
       LOGIN FLOW (login.php)
       ========================================================================= */

    if ( $( '#ls-login-app' ).length ) {

        var loginState = {
            phone          : '',
            countdownTimer : null,
            countdownSecs  : 60
        };

        function showStep( stepName ) {
            $( '.ls-step' ).hide().attr( 'aria-hidden', 'true' );
            var $target = $( '#ls-step-' + stepName );
            $target.show().removeAttr( 'aria-hidden' );
            if ( $target.length ) {
                $( 'html, body' ).animate( { scrollTop: $target.offset().top - 80 }, 200 );
            }
        }

        // Switch between password login and OTP/phone views.
        $( document ).on( 'click', '#ls-switch-to-otp', function () {
            // Copy phone number from password form if already filled.
            var phone = $.trim( $( '#ls-pw-login-phone' ).val() );
            if ( phone && phone !== '+224' ) {
                $( '#ls-phone-input' ).val( phone );
            }
            showStep( 'phone' );
        } );
        $( document ).on( 'click', '#ls-switch-to-password', function () {
            showStep( 'password-login' );
        } );

        // ── OTP digit inputs ──────────────────────────────────────────────────

        $( document ).on( 'input', '.ls-otp-digit', function () {
            var $this  = $( this );
            var idx    = parseInt( $this.data( 'index' ), 10 );
            var val    = $this.val().replace( /\D/g, '' ).slice( 0, 1 );
            $this.val( val );
            $this.removeClass( 'ts-otp-error' );
            if ( val && idx < 5 ) {
                $( '.ls-otp-digit[data-index="' + ( idx + 1 ) + '"]' ).focus();
            }
        } );

        $( document ).on( 'keydown', '.ls-otp-digit', function ( e ) {
            if ( e.key === 'Backspace' && ! $( this ).val() ) {
                var idx = parseInt( $( this ).data( 'index' ), 10 );
                if ( idx > 0 ) {
                    $( '.ls-otp-digit[data-index="' + ( idx - 1 ) + '"]' ).val( '' ).focus();
                }
            }
        } );

        $( document ).on( 'paste', '.ls-otp-digit', function ( e ) {
            e.preventDefault();
            var pasted = ( e.originalEvent.clipboardData || window.clipboardData ).getData( 'text' ).replace( /\D/g, '' );
            $( '.ls-otp-digit' ).each( function ( i ) {
                $( this ).val( pasted[ i ] || '' );
            } );
            $( '.ls-otp-digit' ).last().focus();
        } );

        function getOtp() {
            return $( '.ls-otp-digit' ).map( function () { return $( this ).val(); } ).get().join( '' );
        }

        function clearOtpInputs() {
            $( '.ls-otp-digit' ).val( '' );
            $( '.ls-otp-digit' ).first().focus();
        }

        // ── Countdown ─────────────────────────────────────────────────────────

        function startCountdown( seconds ) {
            clearInterval( loginState.countdownTimer );
            loginState.countdownSecs = seconds || LS_Settings_cooldown || 60;
            $( '#ls-resend-otp-btn' ).hide();
            updateCountdown();
            loginState.countdownTimer = setInterval( function () {
                loginState.countdownSecs--;
                if ( loginState.countdownSecs <= 0 ) {
                    stopCountdown();
                } else {
                    updateCountdown();
                }
            }, 1000 );
        }

        function stopCountdown() {
            clearInterval( loginState.countdownTimer );
            $( '#ls-resend-countdown' ).text( '' );
            $( '#ls-resend-otp-btn' ).show();
        }

        function updateCountdown() {
            $( '#ls-resend-countdown' ).text(
                'Renvoyer dans ' + loginState.countdownSecs + 's'
            );
        }

        // ── Step 1: Request OTP ────────────────────────────────────────────────

        $( document ).on( 'submit', '#ls-phone-form', function ( e ) {
            e.preventDefault();
            var phone  = $.trim( $( '#ls-phone-input' ).val() );
            var $btn   = $( '#ls-send-otp-btn' );
            var $msg   = $( '#ls-auth-message' );

            tsHideMessage( $msg );

            if ( ! phone ) {
                tsShowMessage( $msg, 'error', 'Veuillez entrer votre numéro de téléphone.' );
                return;
            }

            loginState.phone = phone;
            tsSetLoading( $btn, true );

            $.post( TS.ajaxUrl, {
                action : 'ls_request_otp',
                nonce  : TS.nonce,
                phone  : phone
            } )
            .done( function ( resp ) {
                if ( resp.success ) {
                    $( '#ls-otp-phone-display' ).text( phone );
                    showStep( 'otp' );
                    clearOtpInputs();
                    startCountdown( resp.data.cooldown || 60 );
                } else {
                    tsShowMessage( $msg, 'error', tsExtractError( resp ) );
                }
            } )
            .fail( function ( jqXHR ) {
                tsShowMessage( $msg, 'error', tsExtractError( jqXHR ) );
            } )
            .always( function () {
                tsSetLoading( $btn, false );
            } );
        } );

        // ── Resend OTP ────────────────────────────────────────────────────────

        $( document ).on( 'click', '#ls-resend-otp-btn', function () {
            var $btn = $( this ).prop( 'disabled', true );
            $.post( TS.ajaxUrl, {
                action : 'ls_request_otp',
                nonce  : TS.nonce,
                phone  : loginState.phone
            } )
            .done( function ( resp ) {
                if ( resp.success ) {
                    clearOtpInputs();
                    startCountdown( resp.data.cooldown || 60 );
                }
            } )
            .always( function () { $btn.prop( 'disabled', false ); } );
        } );

        // ── Back button from OTP ──────────────────────────────────────────────

        $( document ).on( 'click', '#ls-otp-back', function () {
            showStep( 'phone' );
        } );

        // ── Step 2: Verify OTP ────────────────────────────────────────────────

        $( document ).on( 'submit', '#ls-otp-form', function ( e ) {
            e.preventDefault();
            var otp  = getOtp();
            var $btn = $( '#ls-verify-otp-btn' );
            var $msg = $( '#ls-auth-message' );

            tsHideMessage( $msg );

            if ( otp.length !== 6 ) {
                tsShowMessage( $msg, 'error', 'Veuillez entrer le code à 6 chiffres.' );
                return;
            }

            tsSetLoading( $btn, true );

            $.post( TS.ajaxUrl, {
                action : 'ls_verify_otp',
                nonce  : TS.nonce,
                phone  : loginState.phone,
                code   : otp
            } )
            .done( function ( resp ) {
                if ( resp.success ) {
                    stopCountdown();
                    // Always set/reset password after OTP verification.
                    showStep( 'set-password' );
                    $( '#ls-new-password' ).focus();
                } else {
                    tsShowMessage( $msg, 'error', tsExtractError( resp ) );
                    $( '.ls-otp-digit' ).addClass( 'ts-otp-error' );
                    clearOtpInputs();
                }
            } )
            .fail( function ( jqXHR ) {
                tsShowMessage( $msg, 'error', tsExtractError( jqXHR ) );
            } )
            .always( function () {
                tsSetLoading( $btn, false );
            } );
        } );

        // ── Step 1b: Password login ────────────────────────────────────────────

        $( document ).on( 'submit', '#ls-password-login-form', function ( e ) {
            e.preventDefault();
            var phone    = $.trim( $( '#ls-pw-login-phone' ).val() );
            var password = $( '#ls-pw-login-password' ).val();
            var $btn     = $( '#ls-pw-login-btn' );
            var $msg     = $( '#ls-auth-message' );

            tsHideMessage( $msg );
            tsSetLoading( $btn, true );

            $.post( TS.ajaxUrl, {
                action   : 'ls_customer_login',
                nonce    : TS.nonce,
                phone    : phone,
                password : password
            } )
            .done( function ( resp ) {
                if ( resp.success ) {
                    tsLoginRedirect();
                } else {
                    tsShowMessage( $msg, 'error', tsExtractError( resp ) );
                }
            } )
            .fail( function ( jqXHR ) {
                tsShowMessage( $msg, 'error', tsExtractError( jqXHR ) );
            } )
            .always( function () {
                tsSetLoading( $btn, false );
            } );
        } );

        // ── Step 3: Set Password ──────────────────────────────────────────────

        $( document ).on( 'submit', '#ls-set-password-form', function ( e ) {
            e.preventDefault();
            var password = $.trim( $( '#ls-new-password' ).val() );
            var confirm  = $.trim( $( '#ls-confirm-password' ).val() );
            var $btn     = $( '#ls-set-password-btn' );
            var $msg     = $( '#ls-auth-message' );

            tsHideMessage( $msg );

            if ( password.length < 6 ) {
                tsShowMessage( $msg, 'error', 'Le mot de passe doit contenir au moins 6 caractères.' );
                $( '#ls-new-password' ).focus();
                return;
            }

            if ( password !== confirm ) {
                tsShowMessage( $msg, 'error', 'Les mots de passe ne correspondent pas. Veuillez réessayer.' );
                $( '#ls-confirm-password' ).val( '' ).focus();
                return;
            }

            tsSetLoading( $btn, true );

            $.post( TS.ajaxUrl, {
                action           : 'ls_set_password',
                nonce            : TS.nonce,
                password         : password,
                password_confirm : confirm
            } )
            .done( function ( resp ) {
                if ( resp.success ) {
                    tsLoginRedirect();
                } else {
                    tsShowMessage( $msg, 'error', tsExtractError( resp ) );
                }
            } )
            .fail( function ( jqXHR ) {
                tsShowMessage( $msg, 'error', tsExtractError( jqXHR ) );
            } )
            .always( function () {
                tsSetLoading( $btn, false );
            } );
        } );

        // Password visibility toggle.
        $( document ).on( 'click', '.ls-pw-toggle', function () {
            var target = $( this ).data( 'target' );
            var $field = $( '#' + target );
            if ( $field.attr( 'type' ) === 'password' ) {
                $field.attr( 'type', 'text' );
            } else {
                $field.attr( 'type', 'password' );
            }
        } );

    } // end login-app

    /* =========================================================================
       SUBMIT TICKET FORM
       ========================================================================= */

    $( document ).on( 'submit', '#ls-submit-ticket-form', function ( e ) {
        e.preventDefault();
        var $btn = $( '#ls-submit-ticket-btn' );
        var $msg = $( '#ls-ticket-msg' );

        tsHideMessage( $msg );
        tsSetLoading( $btn, true );

        var formData = new FormData( this );
        formData.append( 'action', 'ls_submit_ticket' );
        formData.append( 'nonce', TS.nonce );

        $.ajax( {
            url         : TS.ajaxUrl,
            type        : 'POST',
            data        : formData,
            processData : false,
            contentType : false
        } )
        .done( function ( resp ) {
            if ( resp.success ) {
                $( '#ls-ticket-form-wrap' ).hide();
                $( '#ls-ticket-topbar' ).hide();
                $( '#ls-ticket-success' ).show();
                $( 'html, body' ).animate( { scrollTop: $( '#ls-ticket-success' ).offset().top - 60 }, 300 );
            } else {
                tsShowMessage( $msg, 'error', tsExtractError( resp ) );
            }
        } )
        .fail( function ( jqXHR ) {
            tsShowMessage( $msg, 'error', tsExtractError( jqXHR ) );
        } )
        .always( function () {
            tsSetLoading( $btn, false );
        } );
    } );

    /* =========================================================================
       INVOICE LOOKUP FORM (in public invoice-lookup.php has its own inline JS)
       ========================================================================= */

    /* =========================================================================
       DASHBOARD PROFILE FORM
       ========================================================================= */

    $( document ).on( 'submit', '#ls-profile-form', function ( e ) {
        e.preventDefault();
        var $btn = $( '#ls-profile-btn' );
        var $msg = $( '#ls-profile-msg' );
        tsHideMessage( $msg );
        tsSetLoading( $btn, true );

        $.post( TS.ajaxUrl, {
            action    : 'ls_update_profile',
            nonce     : TS.nonce,
            full_name : $( '[name="full_name"]', this ).val(),
            address   : $( '[name="address"]', this ).val()
        } )
        .done( function ( resp ) {
            if ( resp.success ) {
                tsShowMessage( $msg, 'success', resp.data.message );
            } else {
                tsShowMessage( $msg, 'error', tsExtractError( resp ) );
            }
        } )
        .fail( function ( jqXHR ) {
            tsShowMessage( $msg, 'error', tsExtractError( jqXHR ) );
        } )
        .always( function () { tsSetLoading( $btn, false ); } );
    } );

} )( jQuery );
