(function() {
    'use strict';

    // Namespace global para el widget
    window.MiniCRMWidget = window.MiniCRMWidget || {
        initialized: false,
        widgets: [],
        containers: {},
        modal: null,
        currentConfig: null,
        itiInstance: null,
        itiLoaded: false
    };

    const MCW = window.MiniCRMWidget;

    // Cargar intl-tel-input CSS y JS desde CDN
    function loadIntlTelInput() {
        if (MCW.itiLoaded) return Promise.resolve();

        return new Promise((resolve) => {
            // CSS
            if (!document.getElementById('iti-css')) {
                const css = document.createElement('link');
                css.id = 'iti-css';
                css.rel = 'stylesheet';
                css.href = 'https://cdn.jsdelivr.net/npm/intl-tel-input@24.6.0/build/css/intlTelInput.css';
                document.head.appendChild(css);
            }

            // JS
            if (!document.getElementById('iti-js')) {
                const script = document.createElement('script');
                script.id = 'iti-js';
                script.src = 'https://cdn.jsdelivr.net/npm/intl-tel-input@24.6.0/build/js/intlTelInput.min.js';
                script.onload = () => {
                    MCW.itiLoaded = true;
                    resolve();
                };
                script.onerror = () => resolve(); // Continuar sin ITI si falla
                document.head.appendChild(script);
            } else {
                MCW.itiLoaded = true;
                resolve();
            }
        });
    }

    // Pre-cargar la librería
    loadIntlTelInput();

    // Obtener el script actual y sus atributos
    const currentScript = document.currentScript;
    if (!currentScript) {
        console.error('MiniCRM Widget: No se pudo detectar el script');
        return;
    }

    // Generar ID único para esta instancia
    const widgetId = 'mcw-' + Date.now() + '-' + Math.random().toString(36).substring(2, 11);

    // Colores por defecto según tipo
    const typeColors = {
        whatsapp: '#25D366',
        phone: '#3B82F6',
        contact_form: '#A855F7'
    };

    const widgetType = currentScript.getAttribute('data-type') || 'whatsapp';
    const customColor = currentScript.getAttribute('data-color');

    // Detectar baseUrl: soporta widget.js directo o via widget-serve.php
    let baseUrl = currentScript.src;
    if (baseUrl.includes('widget-serve.php')) {
        baseUrl = baseUrl.replace('/widget-serve.php', '');
    } else {
        baseUrl = baseUrl.replace('/widget.js', '');
    }

    const config = {
        id: widgetId,
        siteId: currentScript.getAttribute('data-site-id'),
        type: widgetType,
        phone: currentScript.getAttribute('data-phone') || '',
        position: currentScript.getAttribute('data-position') || 'bottom-right',
        color: customColor || typeColors[widgetType] || '#3B82F6',
        title: currentScript.getAttribute('data-title') || 'Contáctanos',
        buttonText: currentScript.getAttribute('data-button-text') || 'Enviar',
        privacyPolicyUrl: currentScript.getAttribute('data-privacy-url') || '',
        // URLs para captura de leads (directa primero, proxy como fallback)
        apiUrlDirect: baseUrl + '/api/v1/leads/capture',
        apiUrlProxy: baseUrl + '/api-proxy.php?endpoint=leads/capture'
    };

    if (!config.siteId) {
        console.error('MiniCRM Widget: data-site-id es requerido');
        return;
    }

    // Registrar widget
    MCW.widgets.push(config);

    // Iconos SVG
    const icons = {
        whatsapp: '<svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',
        phone: '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>',
        contact_form: '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>',
        close: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>',
        check: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>'
    };

    // Mostrar notificación toast
    function showToast(type, title, message, duration = 5000) {
        // Crear contenedor si no existe
        let container = document.getElementById('mcw-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'mcw-toast-container';
            container.className = 'mcw-toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = 'mcw-toast';
        toast.innerHTML = `
            <div class="mcw-toast-icon mcw-toast-${type}">
                ${icons[type] || icons.error}
            </div>
            <div class="mcw-toast-content">
                <p class="mcw-toast-title">${title}</p>
                <p class="mcw-toast-message">${message}</p>
            </div>
            <button class="mcw-toast-close">${icons.close}</button>
        `;

        container.appendChild(toast);

        // Cerrar al hacer click
        const closeBtn = toast.querySelector('.mcw-toast-close');
        const closeToast = () => {
            toast.classList.add('mcw-toast-out');
            setTimeout(() => toast.remove(), 300);
        };
        closeBtn.addEventListener('click', closeToast);

        // Auto-cerrar después de duration
        if (duration > 0) {
            setTimeout(closeToast, duration);
        }

        return toast;
    }

    // Descripciones por tipo
    const descriptions = {
        whatsapp: 'D\u00e9janos tus datos y te contactamos por WhatsApp',
        phone: 'D\u00e9janos tus datos y te llamamos',
        contact_form: 'Completa el formulario y nos pondremos en contacto'
    };

    // Inicializar estilos globales (solo una vez)
    function initStyles() {
        if (document.getElementById('mcw-global-styles')) return;

        const styles = document.createElement('style');
        styles.id = 'mcw-global-styles';
        styles.textContent = `
            /* Anular Elementor/WordPress en TODOS los elementos button del modal EXCEPTO btn-close */
            #mcw-modal-overlay button:not(.mcw-btn-submit):not(.mcw-btn-close),
            #mcw-modal-overlay button:not(.mcw-btn-submit):not(.mcw-btn-close):hover,
            #mcw-modal-overlay button:not(.mcw-btn-submit):not(.mcw-btn-close):focus,
            #mcw-modal-overlay button:not(.mcw-btn-submit):not(.mcw-btn-close):active,
            #mcw-modal-overlay [type="button"]:not(.mcw-btn-submit):not(.mcw-btn-close),
            #mcw-modal-overlay [type="button"]:not(.mcw-btn-submit):not(.mcw-btn-close):hover,
            #mcw-modal-overlay [type="button"]:not(.mcw-btn-submit):not(.mcw-btn-close):focus,
            #mcw-modal-overlay [type="button"]:not(.mcw-btn-submit):not(.mcw-btn-close):active {
                background: transparent !important;
                background-color: transparent !important;
            }
            .mcw-widget-container * {
                box-sizing: border-box !important;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif !important;
            }
            .mcw-buttons-container {
                position: fixed !important;
                display: flex !important;
                gap: 12px !important;
                z-index: 99998 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                background: transparent !important;
            }
            .mcw-buttons-container.bottom-right {
                right: 20px !important;
                bottom: 20px !important;
                flex-direction: row-reverse !important;
            }
            .mcw-buttons-container.bottom-left {
                left: 20px !important;
                bottom: 20px !important;
                flex-direction: row !important;
            }
            .mcw-buttons-container.top-right {
                right: 20px !important;
                top: 20px !important;
                flex-direction: row-reverse !important;
            }
            .mcw-buttons-container.top-left {
                left: 20px !important;
                top: 20px !important;
                flex-direction: row !important;
            }
            .mcw-fab {
                width: 56px !important;
                height: 56px !important;
                border-radius: 50% !important;
                border: none !important;
                cursor: pointer !important;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                transition: transform 0.2s, box-shadow 0.2s !important;
                flex-shrink: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .mcw-fab:hover {
                transform: scale(1.1) !important;
                box-shadow: 0 6px 20px rgba(0,0,0,0.2) !important;
            }
            .mcw-fab svg {
                width: 26px !important;
                height: 26px !important;
                fill: white !important;
                display: block !important;
            }
            .mcw-modal-overlay {
                position: fixed !important;
                inset: 0 !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                background: rgba(0,0,0,0.5) !important;
                display: none !important;
                z-index: 99999 !important;
                padding: 20px !important;
                margin: 0 !important;
                border: none !important;
            }
            .mcw-modal-overlay.mcw-active {
                display: block !important;
            }
            .mcw-modal {
                background: white !important;
                border-radius: 16px !important;
                width: 100% !important;
                max-width: 400px !important;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3) !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                position: fixed !important;
                overflow: hidden !important;
            }
            /* Posicionamiento según ubicación del widget */
            .mcw-modal-overlay.mcw-position-bottom-right .mcw-modal {
                bottom: 20px !important;
                right: 20px !important;
                animation: mcw-slideInFromBottomRight 0.3s ease !important;
            }
            .mcw-modal-overlay.mcw-position-bottom-left .mcw-modal {
                bottom: 20px !important;
                left: 20px !important;
                animation: mcw-slideInFromBottomLeft 0.3s ease !important;
            }
            .mcw-modal-overlay.mcw-position-top-right .mcw-modal {
                top: 20px !important;
                right: 20px !important;
                animation: mcw-slideInFromTopRight 0.3s ease !important;
            }
            .mcw-modal-overlay.mcw-position-top-left .mcw-modal {
                top: 20px !important;
                left: 20px !important;
                animation: mcw-slideInFromTopLeft 0.3s ease !important;
            }
            /* En móviles, mantener posicionamiento pero con altura automática */
            @media (max-width: 640px) {
                .mcw-modal-overlay.mcw-position-bottom-right .mcw-modal,
                .mcw-modal-overlay.mcw-position-bottom-left .mcw-modal {
                    top: auto !important;
                    bottom: 20px !important;
                }
                .mcw-modal-overlay.mcw-position-top-right .mcw-modal,
                .mcw-modal-overlay.mcw-position-top-left .mcw-modal {
                    bottom: auto !important;
                    top: 20px !important;
                }
                .mcw-modal-overlay.mcw-position-bottom-right .mcw-modal,
                .mcw-modal-overlay.mcw-position-top-right .mcw-modal {
                    left: auto !important;
                    right: 20px !important;
                    transform: none !important;
                }
                .mcw-modal-overlay.mcw-position-bottom-left .mcw-modal,
                .mcw-modal-overlay.mcw-position-top-left .mcw-modal {
                    right: auto !important;
                    left: 20px !important;
                    transform: none !important;
                }
                .mcw-modal {
                    max-width: calc(100% - 40px) !important;
                    width: calc(100% - 40px) !important;
                }
            }
            @keyframes mcw-slideUp {
                from {
                    opacity: 0;
                    transform: translateX(-50%) translateY(20px) !important;
                }
                to {
                    opacity: 1;
                    transform: translateX(-50%) translateY(0) !important;
                }
            }
            /* Animaciones por posición */
            @keyframes mcw-slideInFromBottomRight {
                from {
                    opacity: 0;
                    transform: translateY(20px) translateX(20px) scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) translateX(0) scale(1);
                }
            }
            @keyframes mcw-slideInFromBottomLeft {
                from {
                    opacity: 0;
                    transform: translateY(20px) translateX(-20px) scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) translateX(0) scale(1);
                }
            }
            @keyframes mcw-slideInFromTopRight {
                from {
                    opacity: 0;
                    transform: translateY(-20px) translateX(20px) scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) translateX(0) scale(1);
                }
            }
            @keyframes mcw-slideInFromTopLeft {
                from {
                    opacity: 0;
                    transform: translateY(-20px) translateX(-20px) scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) translateX(0) scale(1);
                }
            }
            .mcw-modal-header {
                color: white !important;
                padding: 20px !important;
                text-align: center !important;
                position: relative !important;
                margin: 0 !important;
                border: none !important;
                border-radius: 16px 16px 0 0 !important;
                overflow: hidden !important;
            }
            .mcw-modal-header h3 {
                margin: 0 !important;
                padding: 0 !important;
                font-size: 20px !important;
                font-weight: 600 !important;
                color: white !important;
                border: none !important;
                background: transparent !important;
                text-transform: none !important;
            }
            .mcw-modal-header p {
                margin: 8px 0 0 !important;
                padding: 0 !important;
                font-size: 14px !important;
                opacity: 0.9 !important;
                color: white !important;
                border: none !important;
                background: transparent !important;
                text-transform: none !important;
            }
            .mcw-modal-body {
                padding: 24px !important;
                margin: 0 !important;
                background: white !important;
                border: none !important;
            }
            .mcw-form-group {
                margin-bottom: 16px !important;
                margin-top: 0 !important;
                padding: 0 !important;
                border: none !important;
                background: transparent !important;
            }
            .mcw-form-group label {
                display: block !important;
                font-size: 14px !important;
                font-weight: 500 !important;
                color: #374151 !important;
                margin-bottom: 6px !important;
                margin-top: 0 !important;
                padding: 0 !important;
                border: none !important;
                background: transparent !important;
                text-transform: none !important;
            }
            .mcw-form-group label span {
                color: #EF4444 !important;
            }
            .mcw-form-group input,
            .mcw-form-group textarea,
            .mcw-form-group select {
                width: 100% !important;
                padding: 12px !important;
                border: 1px solid #D1D5DB !important;
                border-radius: 8px !important;
                font-size: 14px !important;
                transition: border-color 0.2s, box-shadow 0.2s !important;
                margin: 0 !important;
                background: white !important;
                color: #111827 !important;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif !important;
                text-transform: none !important;
                box-sizing: border-box !important;
            }
            .mcw-form-group input:focus,
            .mcw-form-group textarea:focus,
            .mcw-form-group select:focus {
                outline: none !important;
                border-color: var(--mcw-color) !important;
                box-shadow: 0 0 0 3px var(--mcw-color-light) !important;
            }
            .mcw-form-group textarea {
                resize: vertical !important;
                min-height: 80px !important;
            }
            .mcw-form-group .mcw-error {
                color: #EF4444 !important;
                font-size: 12px !important;
                margin-top: 4px !important;
                margin-bottom: 0 !important;
                display: none !important;
                padding: 0 !important;
                border: none !important;
                background: transparent !important;
            }
            .mcw-form-group.mcw-invalid input,
            .mcw-form-group.mcw-invalid textarea,
            .mcw-form-group.mcw-invalid select {
                border-color: #EF4444 !important;
            }
            .mcw-form-group.mcw-invalid .mcw-error {
                display: block !important;
            }
            /* Estilos para radio buttons */
            .mcw-form-group .mcw-radio-group {
                display: flex !important;
                flex-direction: column !important;
                gap: 8px !important;
            }
            .mcw-form-group .mcw-radio-group label {
                display: flex !important;
                align-items: center !important;
                cursor: pointer !important;
                font-weight: normal !important;
            }
            .mcw-form-group .mcw-radio-group input[type="radio"] {
                width: auto !important;
                margin-right: 8px !important;
                cursor: pointer !important;
            }
            /* Estilos para checkbox */
            .mcw-form-group input[type="checkbox"] {
                width: auto !important;
                margin-right: 8px !important;
                cursor: pointer !important;
            }
            .mcw-form-group label:has(input[type="checkbox"]) {
                display: flex !important;
                align-items: center !important;
                cursor: pointer !important;
                font-weight: normal !important;
            }
            /* Estilos para política de privacidad */
            .mcw-privacy-group {
                border-top: 1px solid #E5E7EB !important;
                padding-top: 16px !important;
                margin-top: 8px !important;
            }
            .mcw-privacy-label {
                font-size: 13px !important;
                line-height: 1.5 !important;
                display: flex !important;
                align-items: center !important;
            }
            .mcw-privacy-link {
                color: var(--mcw-color) !important;
                text-decoration: underline !important;
                font-weight: 500 !important;
            }
            .mcw-privacy-link:hover {
                text-decoration: none !important;
            }
            /* Estilos para multiselect */
            .mcw-form-group select[multiple] {
                min-height: 80px !important;
            }
            /* Estilos para intl-tel-input */
            .mcw-form-group .iti {
                width: 100% !important;
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .mcw-form-group .iti__tel-input {
                width: 100% !important;
                padding: 12px !important;
                padding-left: 80px !important;
                border: 1px solid #D1D5DB !important;
                border-radius: 8px !important;
                font-size: 14px !important;
                margin: 0 !important;
                background: white !important;
                color: #111827 !important;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif !important;
                box-sizing: border-box !important;
            }
            .mcw-form-group .iti__tel-input:focus {
                outline: none !important;
                border-color: var(--mcw-color) !important;
                box-shadow: 0 0 0 3px var(--mcw-color-light) !important;
            }
            .mcw-form-group.mcw-invalid .iti__tel-input {
                border-color: #EF4444 !important;
            }
            .mcw-form-group .iti__country-list {
                z-index: 100001 !important;
                background: white !important;
                border: 1px solid #D1D5DB !important;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
            }
            .mcw-form-group .iti__flag-container {
                position: absolute !important;
                top: 0 !important;
                bottom: 0 !important;
                left: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .mcw-form-group .iti__selected-dial-code {
                margin-left: 6px !important;
                color: #6B7280 !important;
                font-size: 14px !important;
                font-weight: normal !important;
                line-height: normal !important;
            }
            .mcw-btn-submit {
                width: 100% !important;
                padding: 14px !important;
                color: white !important;
                border: none !important;
                border-radius: 8px !important;
                font-size: 16px !important;
                font-weight: 600 !important;
                cursor: pointer !important;
                transition: opacity 0.2s !important;
                margin: 0 !important;
                background-color: var(--mcw-color) !important;
                text-transform: none !important;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif !important;
            }
            .mcw-btn-submit:hover {
                opacity: 0.9 !important;
            }
            .mcw-btn-submit:disabled {
                opacity: 0.6 !important;
                cursor: not-allowed !important;
            }
            .mcw-btn-close {
                position: absolute !important;
                top: 12px !important;
                right: 12px !important;
                background: rgba(255,255,255,0.2) !important;
                border: none !important;
                width: 32px !important;
                height: 32px !important;
                border-radius: 50% !important;
                cursor: pointer !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                transition: background 0.2s !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .mcw-btn-close:hover {
                background: rgba(255,255,255,0.3) !important;
            }
            .mcw-btn-close svg {
                width: 18px !important;
                height: 18px !important;
                stroke: white !important;
                display: block !important;
            }
            .mcw-success {
                text-align: center !important;
                padding: 40px 20px !important;
                margin: 0 !important;
                border: none !important;
                background: transparent !important;
            }
            .mcw-success svg {
                width: 64px !important;
                height: 64px !important;
                stroke: #10B981 !important;
                margin-bottom: 16px !important;
                display: inline-block !important;
            }
            .mcw-success h4 {
                font-size: 20px !important;
                color: #111827 !important;
                margin: 0 0 8px !important;
                padding: 0 !important;
                font-weight: 600 !important;
                border: none !important;
                background: transparent !important;
                text-transform: none !important;
            }
            .mcw-success p {
                color: #6B7280 !important;
                margin: 0 !important;
                padding: 0 !important;
                font-size: 14px !important;
                border: none !important;
                background: transparent !important;
                text-transform: none !important;
            }
            /* Toast notifications */
            .mcw-toast-container {
                position: fixed !important;
                top: 20px !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
                z-index: 100000 !important;
                display: flex !important;
                flex-direction: column !important;
                gap: 10px !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                background: transparent !important;
            }
            .mcw-toast {
                background: #1F2937 !important;
                border-radius: 12px !important;
                padding: 16px 20px !important;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3) !important;
                display: flex !important;
                align-items: center !important;
                gap: 14px !important;
                min-width: 320px !important;
                max-width: 420px !important;
                animation: mcw-toastIn 0.3s ease !important;
                margin: 0 !important;
                border: none !important;
            }
            .mcw-toast.mcw-toast-out {
                animation: mcw-toastOut 0.3s ease forwards !important;
            }
            @keyframes mcw-toastIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes mcw-toastOut {
                from { opacity: 1; transform: translateY(0); }
                to { opacity: 0; transform: translateY(-20px); }
            }
            .mcw-toast-icon {
                width: 40px !important;
                height: 40px !important;
                border-radius: 50% !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                flex-shrink: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
            }
            .mcw-toast-icon svg {
                width: 22px !important;
                height: 22px !important;
                display: block !important;
            }
            .mcw-toast-icon.mcw-toast-error {
                background: #FEE2E2 !important;
            }
            .mcw-toast-icon.mcw-toast-error svg {
                stroke: #DC2626 !important;
            }
            .mcw-toast-icon.mcw-toast-success {
                background: #D1FAE5 !important;
            }
            .mcw-toast-icon.mcw-toast-success svg {
                stroke: #059669 !important;
            }
            .mcw-toast-icon.mcw-toast-warning {
                background: #FEF3C7 !important;
            }
            .mcw-toast-icon.mcw-toast-warning svg {
                stroke: #D97706 !important;
            }
            .mcw-toast-content {
                flex: 1 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .mcw-toast-title {
                font-weight: 600 !important;
                color: #FFFFFF !important;
                margin: 0 0 2px !important;
                padding: 0 !important;
                font-size: 15px !important;
                border: none !important;
                background: transparent !important;
                text-transform: none !important;
            }
            .mcw-toast-message {
                color: #9CA3AF !important;
                margin: 0 !important;
                padding: 0 !important;
                font-size: 13px !important;
                line-height: 1.4 !important;
                border: none !important;
                background: transparent !important;
                text-transform: none !important;
            }
            .mcw-toast-close {
                background: none !important;
                border: none !important;
                padding: 4px !important;
                cursor: pointer !important;
                color: #6B7280 !important;
                transition: color 0.2s !important;
                align-self: flex-start !important;
                margin: 0 !important;
            }
            .mcw-toast-close:hover {
                color: #9CA3AF !important;
            }
            .mcw-toast-close svg {
                width: 18px !important;
                height: 18px !important;
                display: block !important;
            }
        `;
        document.head.appendChild(styles);
    }

    // Obtener o crear contenedor para una posición
    function getOrCreateContainer(position) {
        const containerId = 'mcw-container-' + position;

        if (MCW.containers[position]) {
            return MCW.containers[position];
        }

        const container = document.createElement('div');
        container.id = containerId;
        container.className = 'mcw-buttons-container ' + position;
        document.body.appendChild(container);

        MCW.containers[position] = container;
        return container;
    }

    // Crear o obtener el modal compartido
    function getOrCreateModal() {
        if (MCW.modal) {
            return MCW.modal;
        }

        const modalWrapper = document.createElement('div');
        modalWrapper.className = 'mcw-widget-container';
        modalWrapper.innerHTML = `
            <div class="mcw-modal-overlay" id="mcw-modal-overlay">
                <div class="mcw-modal">
                    <div class="mcw-modal-header" id="mcw-modal-header">
                        <button class="mcw-btn-close" id="mcw-btn-close">${icons.close}</button>
                        <h3 id="mcw-modal-title"></h3>
                        <p id="mcw-modal-description"></p>
                    </div>
                    <div class="mcw-modal-body" id="mcw-modal-body"></div>
                </div>
            </div>
        `;
        document.body.appendChild(modalWrapper);

        const overlay = document.getElementById('mcw-modal-overlay');
        const closeBtn = document.getElementById('mcw-btn-close');

        // Cerrar modal
        closeBtn.addEventListener('click', () => {
            overlay.classList.remove('mcw-active');
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('mcw-active');
            }
        });

        MCW.modal = {
            overlay: overlay,
            header: document.getElementById('mcw-modal-header'),
            title: document.getElementById('mcw-modal-title'),
            description: document.getElementById('mcw-modal-description'),
            body: document.getElementById('mcw-modal-body')
        };

        return MCW.modal;
    }

    // Generar formulario HTML
    function getFormHtml(cfg) {
        // Usar custom fields si están disponibles, sino campos por defecto
        const customFields = window.MCW_CUSTOM_FIELDS || [];

        console.log('[MiniCRM Widget] Custom fields loaded:', customFields.length > 0 ? customFields : 'Using default fields');

        let fieldsHtml = '';

        if (customFields.length > 0) {
            console.log('[MiniCRM Widget] Rendering dynamic form with', customFields.length, 'custom fields');
            // Generar campos dinámicamente desde custom fields
            customFields.forEach(field => {
                const requiredAttr = field.required ? 'required' : '';
                const requiredMark = field.required ? '<span>*</span>' : '';
                const errorMsg = field.required ? `${field.label} es requerido` : `Ingresa un ${field.label.toLowerCase()} válido`;

                // Determinar el tipo de input según el tipo de custom field
                let inputHtml = '';

                switch (field.type) {
                    case 'textarea':
                        const textareaDefault = field.default_value || '';
                        inputHtml = `<textarea name="${field.name}" ${requiredAttr} placeholder="${field.label}">${textareaDefault}</textarea>`;
                        break;
                    case 'select':
                        const options = field.options || [];
                        console.log('[MiniCRM Widget] Select field options:', field.name, options);
                        const defaultValue = field.default_value || '';
                        const optionsHtml = options.map(opt => {
                            const value = opt.value || opt;
                            const label = opt.label || opt;
                            const selected = value === defaultValue ? 'selected' : '';
                            return `<option value="${value}" ${selected}>${label}</option>`;
                        }).join('');
                        inputHtml = `<select name="${field.name}" ${requiredAttr}><option value="">Selecciona...</option>${optionsHtml}</select>`;
                        break;
                    case 'multiselect':
                        const multiOptions = field.options || [];
                        // Parsear default_value: puede ser string, array JSON, o null
                        let multiDefaults = [];
                        if (field.default_value) {
                            try {
                                // Intentar parsear como JSON
                                if (field.default_value.startsWith('[')) {
                                    multiDefaults = JSON.parse(field.default_value);
                                } else {
                                    // Si es un valor simple, crear array con ese valor
                                    multiDefaults = [field.default_value];
                                }
                            } catch (e) {
                                // Si falla el parse, usar como valor simple
                                multiDefaults = [field.default_value];
                            }
                        }
                        const multiOptionsHtml = multiOptions.map(opt => {
                            const value = opt.value || opt;
                            const label = opt.label || opt;
                            const selected = multiDefaults.includes(value) ? 'selected' : '';
                            return `<option value="${value}" ${selected}>${label}</option>`;
                        }).join('');
                        inputHtml = `<select name="${field.name}[]" ${requiredAttr} multiple size="3">${multiOptionsHtml}</select>`;
                        break;
                    case 'radio':
                        const radioOptions = field.options || [];
                        const radioDefault = field.default_value || '';
                        const radioHtml = radioOptions.map(opt => {
                            const value = opt.value || opt;
                            const label = opt.label || opt;
                            const checked = value === radioDefault ? 'checked' : '';
                            return `<label><input type="radio" name="${field.name}" value="${value}" ${requiredAttr} ${checked}> ${label}</label>`;
                        }).join('');
                        inputHtml = `<div class="mcw-radio-group">${radioHtml}</div>`;
                        break;
                    case 'checkbox':
                        const checkboxDefault = field.default_value === '1' || field.default_value === 'true';
                        const checked = checkboxDefault ? 'checked' : '';
                        inputHtml = `<label><input type="checkbox" name="${field.name}" value="1" ${checked}> ${field.placeholder || 'Sí'}</label>`;
                        break;
                    case 'tel':
                    case 'phone':
                        // Campo teléfono con intl-tel-input
                        inputHtml = `<input type="tel" name="${field.name}" id="mcw-phone-input" ${requiredAttr}>`;
                        break;
                    case 'email':
                        const emailDefault = field.default_value || '';
                        inputHtml = `<input type="email" name="${field.name}" ${requiredAttr} placeholder="${field.label}" value="${emailDefault}">`;
                        break;
                    case 'number':
                        const numberDefault = field.default_value || '';
                        inputHtml = `<input type="number" name="${field.name}" ${requiredAttr} placeholder="${field.label}" value="${numberDefault}">`;
                        break;
                    case 'date':
                        const dateDefault = field.default_value || '';
                        inputHtml = `<input type="date" name="${field.name}" ${requiredAttr} value="${dateDefault}">`;
                        break;
                    case 'url':
                        const urlDefault = field.default_value || '';
                        inputHtml = `<input type="url" name="${field.name}" ${requiredAttr} placeholder="${field.label}" value="${urlDefault}">`;
                        break;
                    default:
                        // text por defecto
                        const textDefault = field.default_value || '';
                        inputHtml = `<input type="text" name="${field.name}" ${requiredAttr} placeholder="${field.label}" value="${textDefault}">`;
                }

                fieldsHtml += `
                    <div class="mcw-form-group">
                        <label>${field.label} ${requiredMark}</label>
                        ${inputHtml}
                        <div class="mcw-error">${errorMsg}</div>
                    </div>
                `;
            });
        } else {
            console.log('[MiniCRM Widget] Using default hardcoded fields');
            // Campos por defecto (fallback si no hay custom fields)
            fieldsHtml = `
                <div class="mcw-form-group">
                    <label>Nombre <span>*</span></label>
                    <input type="text" name="cf_lead_1" required placeholder="Tu nombre">
                    <div class="mcw-error">El nombre es requerido</div>
                </div>
                <div class="mcw-form-group">
                    <label>Teléfono <span>*</span></label>
                    <input type="tel" name="cf_lead_3" id="mcw-phone-input" required>
                    <div class="mcw-error">Ingresa un número de teléfono válido</div>
                </div>
                <div class="mcw-form-group">
                    <label>Email</label>
                    <input type="email" name="cf_lead_2" placeholder="tu@email.com">
                    <div class="mcw-error">Ingresa un email válido</div>
                </div>
                <div class="mcw-form-group">
                    <label>Mensaje <span>*</span></label>
                    <textarea name="cf_lead_4" required placeholder="¿En qué podemos ayudarte?"></textarea>
                    <div class="mcw-error">El mensaje es requerido</div>
                </div>
            `;
        }

        // Checkbox de política de privacidad
        const privacyCheckbox = cfg.privacyPolicyUrl ? `
            <div class="mcw-form-group mcw-privacy-group">
                <label class="mcw-privacy-label">
                    <input type="checkbox" name="privacy_accepted" required>
                    <span style="color: #374151 !important;">He leído y acepto la <a href="${cfg.privacyPolicyUrl}" target="_blank" rel="noopener noreferrer" class="mcw-privacy-link">política de privacidad</a> <span style="color: #EF4444 !important;">*</span></span>
                </label>
                <div class="mcw-error">Debes aceptar la política de privacidad</div>
            </div>
        ` : '';

        return `
            <form id="mcw-form">
                ${fieldsHtml}
                ${privacyCheckbox}
                <button type="submit" class="mcw-btn-submit" style="background: ${cfg.color};">${cfg.buttonText}</button>
            </form>
        `;
    }

    // Inicializar intl-tel-input en el campo de teléfono
    function initPhoneInput() {
        const phoneInput = document.getElementById('mcw-phone-input');
        if (!phoneInput || !window.intlTelInput) return null;

        // Destruir instancia anterior si existe
        if (MCW.itiInstance) {
            MCW.itiInstance.destroy();
        }

        MCW.itiInstance = window.intlTelInput(phoneInput, {
            initialCountry: "auto",
            geoIpLookup: function(callback) {
                fetch("https://ipapi.co/json")
                    .then(res => res.json())
                    .then(data => callback(data.country_code))
                    .catch(() => callback("co")); // Default Colombia
            },
            preferredCountries: ["co", "mx", "ar", "cl", "pe", "ec", "ve", "us", "es"],
            separateDialCode: true,
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@24.6.0/build/js/utils.js"
        });

        return MCW.itiInstance;
    }

    // Validar email
    function isValidEmail(email) {
        if (!email) return true;
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Abrir modal con configuración específica
    async function openModal(cfg) {
        const modal = getOrCreateModal();
        MCW.currentConfig = cfg;

        // Actualizar estilos del modal
        modal.header.style.background = cfg.color;
        modal.title.textContent = cfg.title;
        modal.description.textContent = descriptions[cfg.type] || descriptions.contact_form;

        // Establecer variables CSS para el color
        modal.body.style.setProperty('--mcw-color', cfg.color);
        modal.body.style.setProperty('--mcw-color-light', cfg.color + '20');

        // Cargar formulario
        modal.body.innerHTML = getFormHtml(cfg);

        // Esperar a que intl-tel-input esté cargado e inicializar
        await loadIntlTelInput();
        setTimeout(() => initPhoneInput(), 50); // Pequeño delay para asegurar que el DOM esté listo

        // Attach form submit
        attachFormSubmit(cfg, modal);

        // Limpiar clases de posición previas
        modal.overlay.classList.remove('mcw-position-bottom-right', 'mcw-position-bottom-left', 'mcw-position-top-right', 'mcw-position-top-left');

        // Agregar clase según posición del widget
        modal.overlay.classList.add('mcw-position-' + cfg.position);

        // Mostrar modal
        modal.overlay.classList.add('mcw-active');
    }

    // Manejar envío del formulario
    function attachFormSubmit(cfg, modal) {
        const form = document.getElementById('mcw-form');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Limpiar errores previos
            form.querySelectorAll('.mcw-form-group').forEach(g => g.classList.remove('mcw-invalid'));

            const formData = new FormData(form);

            // Construir data object dinámicamente desde todos los campos del formulario
            const data = {
                site_id: cfg.siteId,
                source_type: cfg.type === 'contact_form' ? 'contact_form' : (cfg.type + '_button'),
                source_url: window.location.href,
                page_url: window.location.href,
                user_agent: navigator.userAgent
            };

            // Procesar cada campo del formulario
            let phoneNumber = '';
            let phoneValid = true;
            let phoneFieldName = null;

            for (let [name, value] of formData.entries()) {
                // Detectar campo de teléfono (buscar input type="tel" o id con "phone")
                const input = form.querySelector(`[name="${name}"]`);
                if (input && (input.type === 'tel' || input.id === 'mcw-phone-input')) {
                    phoneFieldName = name;
                    // Obtener teléfono completo con código de país desde intl-tel-input
                    if (MCW.itiInstance) {
                        phoneNumber = MCW.itiInstance.getNumber();
                        // Validar usando la librería
                        if (phoneNumber && !MCW.itiInstance.isValidNumber()) {
                            phoneValid = false;
                        }
                    } else {
                        phoneNumber = (value || '').trim();
                    }
                    data[name] = phoneNumber;
                } else {
                    // Manejar campos multiselect (name termina en [])
                    if (name.endsWith('[]')) {
                        // Remover [] del nombre para enviar al backend
                        const cleanName = name.slice(0, -2);
                        if (!data[cleanName]) {
                            data[cleanName] = [];
                        }
                        data[cleanName].push((value || '').trim());
                    } else {
                        // Otros campos
                        data[name] = (value || '').trim();
                    }
                }
            }

            // Validaciones dinámicas
            let hasErrors = false;

            // Validar todos los campos requeridos
            form.querySelectorAll('[required]').forEach(field => {
                let fieldName = field.name;
                // Si el nombre termina en [], quitarlo para buscar en data
                const cleanFieldName = fieldName.endsWith('[]') ? fieldName.slice(0, -2) : fieldName;
                const fieldValue = data[cleanFieldName];
                const formGroup = field.closest('.mcw-form-group');

                // Validar campo vacío (arrays deben tener al menos un elemento)
                if (!fieldValue || (Array.isArray(fieldValue) && fieldValue.length === 0) || (!Array.isArray(fieldValue) && fieldValue.trim() === '')) {
                    formGroup.classList.add('mcw-invalid');
                    hasErrors = true;
                }
                // Validar email si es tipo email
                else if (field.type === 'email' && !isValidEmail(fieldValue)) {
                    formGroup.classList.add('mcw-invalid');
                    hasErrors = true;
                }
                // Validar teléfono con intl-tel-input
                else if (field.type === 'tel' && phoneFieldName === fieldName && !phoneValid) {
                    formGroup.classList.add('mcw-invalid');
                    const errorDiv = formGroup.querySelector('.mcw-error');
                    if (errorDiv) {
                        errorDiv.textContent = 'El número de teléfono no es válido para el país seleccionado';
                    }
                    hasErrors = true;
                }
            });

            if (hasErrors) return;

            // Deshabilitar botón
            const submitBtn = form.querySelector('.mcw-btn-submit');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';

            try {
                // Intentar API directa primero, proxy como fallback para hosting con WAF/ModSecurity
                let response;
                const fetchOptions = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                };

                try {
                    response = await fetch(cfg.apiUrlDirect, fetchOptions);
                } catch (e) {
                    // Si falla la directa (CORS en producción), usar proxy
                    response = await fetch(cfg.apiUrlProxy, fetchOptions);
                }

                const result = await response.json();

                if (!response.ok) {
                    // Mostrar errores de validación del servidor
                    if (result.errors) {
                        // Mapear errores dinámicamente a los campos del formulario
                        Object.keys(result.errors).forEach(fieldName => {
                            const formGroup = form.querySelector(`[name="${fieldName}"]`)?.closest('.mcw-form-group');
                            if (formGroup) {
                                formGroup.classList.add('mcw-invalid');
                                const errorDiv = formGroup.querySelector('.mcw-error');
                                if (errorDiv) {
                                    // Mostrar el primer mensaje de error
                                    const errorMsg = Array.isArray(result.errors[fieldName])
                                        ? result.errors[fieldName][0]
                                        : result.errors[fieldName];
                                    errorDiv.textContent = errorMsg;
                                }
                            }
                        });

                        submitBtn.disabled = false;
                        submitBtn.textContent = cfg.buttonText;
                        return;
                    }

                    throw new Error(result.message || 'Error al enviar');
                }

                // Mostrar éxito
                modal.body.innerHTML = `
                    <div class="mcw-success">
                        ${icons.check}
                        <h4>¡Mensaje enviado!</h4>
                        <p>Nos pondremos en contacto contigo pronto.</p>
                    </div>
                `;

                // Acción post-envío según tipo
                setTimeout(() => {
                    if (cfg.type === 'whatsapp' && cfg.phone) {
                        const cleanPhone = cfg.phone.replace(/[^0-9]/g, '');
                        // Buscar campo textarea para el mensaje (probablemente mensaje)
                        const textareaField = form.querySelector('textarea');
                        const messageText = textareaField ? data[textareaField.name] : '';
                        const whatsappUrl = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(messageText || 'Hola')}`;
                        window.open(whatsappUrl, '_blank');
                    } else if (cfg.type === 'phone' && cfg.phone) {
                        window.location.href = `tel:${cfg.phone}`;
                    }

                    // Cerrar modal después de 2 segundos
                    setTimeout(() => {
                        modal.overlay.classList.remove('mcw-active');
                    }, 2000);
                }, 500);

            } catch (error) {
                console.error('MiniCRM Widget Error:', error);
                submitBtn.disabled = false;
                submitBtn.textContent = cfg.buttonText;

                // Mostrar error con toast
                const errorMessage = error.message && error.message !== 'Error al enviar'
                    ? error.message
                    : 'Por favor verifica los datos e intenta de nuevo.';
                showToast('error', 'Error al enviar', errorMessage);
            }
        });
    }

    // Crear botón FAB
    function createFab(cfg) {
        const fab = document.createElement('button');
        fab.className = 'mcw-fab';
        fab.id = cfg.id;
        fab.style.background = cfg.color;
        fab.innerHTML = icons[cfg.type] || icons.contact_form;
        fab.title = cfg.title;

        fab.addEventListener('click', () => {
            openModal(cfg);
        });

        return fab;
    }

    // Verificar si el sitio está activo
    async function checkSiteStatus() {
        try {
            // API directa primero, proxy como fallback para hosting con WAF/ModSecurity
            const directUrl = baseUrl + '/api/v1/sites/' + config.siteId + '/status';
            const proxyUrl = baseUrl + '/api-proxy.php?endpoint=sites/' + config.siteId + '/status';

            // Usar AbortController para timeout de 3 segundos
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 3000);

            let response;
            try {
                // Intentar API directa primero
                response = await fetch(directUrl, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    signal: controller.signal
                });
            } catch (e) {
                // Si falla la directa, intentar el proxy
                response = await fetch(proxyUrl, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    signal: controller.signal
                });
            }

            clearTimeout(timeoutId);
            const data = await response.json();
            return data.active === true;
        } catch (error) {
            // En caso de error/timeout, mostrar el widget de todos modos
            // para no bloquear funcionalidad por problemas de conectividad
            console.warn('MiniCRM Widget: No se pudo verificar estado, mostrando widget por defecto');
            return true;
        }
    }

    // Cargar custom fields desde API
    async function loadCustomFields() {
        // Si ya están cargados globalmente (por widget-serve.php), usar esos
        if (window.MCW_CUSTOM_FIELDS && window.MCW_CUSTOM_FIELDS.length > 0) {
            console.log('[MiniCRM Widget] Using custom fields from window.MCW_CUSTOM_FIELDS');
            return window.MCW_CUSTOM_FIELDS;
        }

        // Si no, cargar desde API
        try {
            const directUrl = baseUrl + '/api/v1/custom-fields/widget';
            const proxyUrl = baseUrl + '/api-proxy.php?endpoint=custom-fields/widget';

            let response;
            try {
                // Intentar API directa primero
                response = await fetch(directUrl, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                });
            } catch (e) {
                // Si falla la directa (CORS/WAF), usar proxy
                response = await fetch(proxyUrl, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                });
            }

            if (response.ok) {
                const fields = await response.json();
                window.MCW_CUSTOM_FIELDS = fields;
                console.log('[MiniCRM Widget] Custom fields loaded from API:', fields.length);
                return fields;
            }
        } catch (error) {
            console.warn('[MiniCRM Widget] Failed to load custom fields from API, using defaults');
        }

        // Fallback a array vacío (usará campos por defecto)
        window.MCW_CUSTOM_FIELDS = [];
        return [];
    }

    // Inicializar widget
    async function init() {
        // Verificar si el sitio está activo antes de mostrar el widget
        const isActive = await checkSiteStatus();
        if (!isActive) {
            console.log('MiniCRM Widget: Sitio inactivo, widget no se mostrará');
            return;
        }

        // Cargar custom fields
        await loadCustomFields();

        initStyles();

        const container = getOrCreateContainer(config.position);
        const fab = createFab(config);
        container.appendChild(fab);

        getOrCreateModal();
    }

    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
