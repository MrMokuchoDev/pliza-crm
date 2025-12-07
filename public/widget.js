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

    const baseUrl = currentScript.src.replace('/widget.js', '');

    const config = {
        id: widgetId,
        siteId: currentScript.getAttribute('data-site-id'),
        type: widgetType,
        phone: currentScript.getAttribute('data-phone') || '',
        position: currentScript.getAttribute('data-position') || 'bottom-right',
        color: customColor || typeColors[widgetType] || '#3B82F6',
        title: currentScript.getAttribute('data-title') || 'Contáctanos',
        buttonText: currentScript.getAttribute('data-button-text') || 'Enviar',
        apiUrl: baseUrl + '/api/v1/leads/capture',
        statusUrl: baseUrl + '/api/v1/sites/'
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
        whatsapp: 'Déjanos tus datos y te contactamos por WhatsApp',
        phone: 'Déjanos tus datos y te llamamos',
        contact_form: 'Completa el formulario y nos pondremos en contacto'
    };

    // Inicializar estilos globales (solo una vez)
    function initStyles() {
        if (document.getElementById('mcw-global-styles')) return;

        const styles = document.createElement('style');
        styles.id = 'mcw-global-styles';
        styles.textContent = `
            .mcw-widget-container * {
                box-sizing: border-box;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            }
            .mcw-buttons-container {
                position: fixed;
                display: flex;
                gap: 12px;
                z-index: 99998;
            }
            .mcw-buttons-container.bottom-right {
                right: 20px;
                bottom: 20px;
                flex-direction: row-reverse;
            }
            .mcw-buttons-container.bottom-left {
                left: 20px;
                bottom: 20px;
                flex-direction: row;
            }
            .mcw-buttons-container.top-right {
                right: 20px;
                top: 20px;
                flex-direction: row-reverse;
            }
            .mcw-buttons-container.top-left {
                left: 20px;
                top: 20px;
                flex-direction: row;
            }
            .mcw-fab {
                width: 56px;
                height: 56px;
                border-radius: 50%;
                border: none;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                justify-content: center;
                transition: transform 0.2s, box-shadow 0.2s;
                flex-shrink: 0;
            }
            .mcw-fab:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(0,0,0,0.2);
            }
            .mcw-fab svg {
                width: 26px;
                height: 26px;
                fill: white;
            }
            .mcw-modal-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 99999;
                padding: 20px;
            }
            .mcw-modal-overlay.mcw-active {
                display: flex;
            }
            .mcw-modal {
                background: white;
                border-radius: 16px;
                width: 100%;
                max-width: 400px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                overflow: hidden;
                animation: mcw-slideUp 0.3s ease;
            }
            @keyframes mcw-slideUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .mcw-modal-header {
                color: white;
                padding: 20px;
                text-align: center;
                position: relative;
            }
            .mcw-modal-header h3 {
                margin: 0;
                font-size: 20px;
                font-weight: 600;
            }
            .mcw-modal-header p {
                margin: 8px 0 0;
                font-size: 14px;
                opacity: 0.9;
            }
            .mcw-modal-body {
                padding: 24px;
            }
            .mcw-form-group {
                margin-bottom: 16px;
            }
            .mcw-form-group label {
                display: block;
                font-size: 14px;
                font-weight: 500;
                color: #374151;
                margin-bottom: 6px;
            }
            .mcw-form-group label span {
                color: #EF4444;
            }
            .mcw-form-group input,
            .mcw-form-group textarea {
                width: 100%;
                padding: 12px;
                border: 1px solid #D1D5DB;
                border-radius: 8px;
                font-size: 14px;
                transition: border-color 0.2s, box-shadow 0.2s;
            }
            .mcw-form-group input:focus,
            .mcw-form-group textarea:focus {
                outline: none;
                border-color: var(--mcw-color);
                box-shadow: 0 0 0 3px var(--mcw-color-light);
            }
            .mcw-form-group textarea {
                resize: vertical;
                min-height: 80px;
            }
            .mcw-form-group .mcw-error {
                color: #EF4444;
                font-size: 12px;
                margin-top: 4px;
                display: none;
            }
            .mcw-form-group.mcw-invalid input,
            .mcw-form-group.mcw-invalid textarea {
                border-color: #EF4444;
            }
            .mcw-form-group.mcw-invalid .mcw-error {
                display: block;
            }
            /* Estilos para intl-tel-input */
            .mcw-form-group .iti {
                width: 100%;
            }
            .mcw-form-group .iti__tel-input {
                width: 100%;
                padding: 12px;
                padding-left: 52px;
                border: 1px solid #D1D5DB;
                border-radius: 8px;
                font-size: 14px;
            }
            .mcw-form-group .iti__tel-input:focus {
                outline: none;
                border-color: var(--mcw-color);
                box-shadow: 0 0 0 3px var(--mcw-color-light);
            }
            .mcw-form-group.mcw-invalid .iti__tel-input {
                border-color: #EF4444;
            }
            .mcw-btn-submit {
                width: 100%;
                padding: 14px;
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: opacity 0.2s;
            }
            .mcw-btn-submit:hover {
                opacity: 0.9;
            }
            .mcw-btn-submit:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }
            .mcw-btn-close {
                position: absolute;
                top: 12px;
                right: 12px;
                background: rgba(255,255,255,0.2);
                border: none;
                width: 32px;
                height: 32px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.2s;
            }
            .mcw-btn-close:hover {
                background: rgba(255,255,255,0.3);
            }
            .mcw-btn-close svg {
                width: 18px;
                height: 18px;
                stroke: white;
            }
            .mcw-success {
                text-align: center;
                padding: 40px 20px;
            }
            .mcw-success svg {
                width: 64px;
                height: 64px;
                stroke: #10B981;
                margin-bottom: 16px;
            }
            .mcw-success h4 {
                font-size: 20px;
                color: #111827;
                margin: 0 0 8px;
            }
            .mcw-success p {
                color: #6B7280;
                margin: 0;
            }
            /* Toast notifications */
            .mcw-toast-container {
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 100000;
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .mcw-toast {
                background: #1F2937;
                border-radius: 12px;
                padding: 16px 20px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                display: flex;
                align-items: center;
                gap: 14px;
                min-width: 320px;
                max-width: 420px;
                animation: mcw-toastIn 0.3s ease;
            }
            .mcw-toast.mcw-toast-out {
                animation: mcw-toastOut 0.3s ease forwards;
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
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            .mcw-toast-icon svg {
                width: 22px;
                height: 22px;
            }
            .mcw-toast-icon.mcw-toast-error {
                background: #FEE2E2;
            }
            .mcw-toast-icon.mcw-toast-error svg {
                stroke: #DC2626;
            }
            .mcw-toast-icon.mcw-toast-success {
                background: #D1FAE5;
            }
            .mcw-toast-icon.mcw-toast-success svg {
                stroke: #059669;
            }
            .mcw-toast-icon.mcw-toast-warning {
                background: #FEF3C7;
            }
            .mcw-toast-icon.mcw-toast-warning svg {
                stroke: #D97706;
            }
            .mcw-toast-content {
                flex: 1;
            }
            .mcw-toast-title {
                font-weight: 600;
                color: #FFFFFF;
                margin: 0 0 2px;
                font-size: 15px;
            }
            .mcw-toast-message {
                color: #9CA3AF;
                margin: 0;
                font-size: 13px;
                line-height: 1.4;
            }
            .mcw-toast-close {
                background: none;
                border: none;
                padding: 4px;
                cursor: pointer;
                color: #6B7280;
                transition: color 0.2s;
                align-self: flex-start;
            }
            .mcw-toast-close:hover {
                color: #9CA3AF;
            }
            .mcw-toast-close svg {
                width: 18px;
                height: 18px;
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
        return `
            <form id="mcw-form">
                <div class="mcw-form-group">
                    <label>Nombre <span>*</span></label>
                    <input type="text" name="name" required placeholder="Tu nombre">
                    <div class="mcw-error">El nombre es requerido</div>
                </div>
                <div class="mcw-form-group">
                    <label>Teléfono <span>*</span></label>
                    <input type="tel" name="phone" id="mcw-phone-input" required>
                    <div class="mcw-error">Ingresa un número de teléfono válido</div>
                </div>
                <div class="mcw-form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="tu@email.com">
                    <div class="mcw-error">Ingresa un email válido</div>
                </div>
                <div class="mcw-form-group">
                    <label>Mensaje <span>*</span></label>
                    <textarea name="message" required placeholder="¿En qué podemos ayudarte?"></textarea>
                    <div class="mcw-error">El mensaje es requerido</div>
                </div>
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

            // Obtener teléfono completo con código de país desde intl-tel-input
            let phoneNumber = formData.get('phone').trim();
            let phoneValid = true;

            if (MCW.itiInstance) {
                // Obtener número en formato E.164 (+573001234567)
                phoneNumber = MCW.itiInstance.getNumber();

                // Validar usando la librería
                if (phoneNumber && !MCW.itiInstance.isValidNumber()) {
                    phoneValid = false;
                }
            }

            const data = {
                name: formData.get('name').trim(),
                phone: phoneNumber,
                email: formData.get('email').trim(),
                message: formData.get('message').trim(),
                site_id: cfg.siteId,
                source_type: cfg.type === 'contact_form' ? 'contact_form' : (cfg.type + '_button'),
                source_url: window.location.href,
                page_url: window.location.href,
                user_agent: navigator.userAgent
            };

            // Validaciones
            let hasErrors = false;

            if (!data.name) {
                form.querySelector('[name="name"]').closest('.mcw-form-group').classList.add('mcw-invalid');
                hasErrors = true;
            }
            if (!data.phone || !phoneValid) {
                const phoneGroup = form.querySelector('[name="phone"]').closest('.mcw-form-group');
                phoneGroup.classList.add('mcw-invalid');
                if (!phoneValid && data.phone) {
                    phoneGroup.querySelector('.mcw-error').textContent = 'El número de teléfono no es válido para el país seleccionado';
                }
                hasErrors = true;
            }
            if (!isValidEmail(data.email)) {
                form.querySelector('[name="email"]').closest('.mcw-form-group').classList.add('mcw-invalid');
                hasErrors = true;
            }
            if (!data.message) {
                form.querySelector('[name="message"]').closest('.mcw-form-group').classList.add('mcw-invalid');
                hasErrors = true;
            }

            if (hasErrors) return;

            // Deshabilitar botón
            const submitBtn = form.querySelector('.mcw-btn-submit');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';

            try {
                const response = await fetch(cfg.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok) {
                    // Mostrar errores de validación del servidor
                    if (result.errors) {
                        // Mapear errores a los campos del formulario
                        const fieldMap = {
                            'name': 'name',
                            'phone': 'phone',
                            'email': 'email',
                            'message': 'message'
                        };

                        Object.keys(result.errors).forEach(field => {
                            const inputName = fieldMap[field];
                            if (inputName) {
                                const formGroup = form.querySelector(`[name="${inputName}"]`)?.closest('.mcw-form-group');
                                if (formGroup) {
                                    formGroup.classList.add('mcw-invalid');
                                    const errorDiv = formGroup.querySelector('.mcw-error');
                                    if (errorDiv) {
                                        // Mostrar el primer mensaje de error
                                        const errorMsg = Array.isArray(result.errors[field])
                                            ? result.errors[field][0]
                                            : result.errors[field];
                                        errorDiv.textContent = errorMsg;
                                    }
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
                        const whatsappUrl = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(data.message)}`;
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
            const response = await fetch(config.statusUrl + config.siteId + '/status');
            const data = await response.json();
            return data.active === true;
        } catch (error) {
            console.error('MiniCRM Widget: Error verificando estado del sitio', error);
            return false;
        }
    }

    // Inicializar widget
    async function init() {
        // Verificar si el sitio está activo antes de mostrar el widget
        const isActive = await checkSiteStatus();
        if (!isActive) {
            console.log('MiniCRM Widget: Sitio inactivo, widget no se mostrará');
            return;
        }

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
