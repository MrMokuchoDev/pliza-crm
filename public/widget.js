(function() {
    'use strict';

    // Obtener el script actual y sus atributos
    const currentScript = document.currentScript;
    if (!currentScript) {
        console.error('MiniCRM Widget: No se pudo detectar el script');
        return;
    }

    // Colores por defecto según tipo
    const typeColors = {
        whatsapp: '#25D366',  // Verde WhatsApp
        phone: '#3B82F6',     // Azul
        contact_form: '#A855F7' // Púrpura
    };

    const widgetType = currentScript.getAttribute('data-type') || 'whatsapp';
    const customColor = currentScript.getAttribute('data-color');

    const config = {
        siteId: currentScript.getAttribute('data-site-id'),
        type: widgetType,
        phone: currentScript.getAttribute('data-phone') || '',
        position: currentScript.getAttribute('data-position') || 'bottom-right',
        color: customColor || typeColors[widgetType] || '#3B82F6',
        title: currentScript.getAttribute('data-title') || 'Contáctanos',
        buttonText: currentScript.getAttribute('data-button-text') || 'Enviar',
        apiUrl: currentScript.src.replace('/widget.js', '/api/v1/leads/capture')
    };

    if (!config.siteId) {
        console.error('MiniCRM Widget: data-site-id es requerido');
        return;
    }

    // Función para obtener estilos de posición
    const getPositionStyles = (pos) => {
        const positions = {
            'bottom-right': 'right: 20px; bottom: 20px;',
            'bottom-left': 'left: 20px; bottom: 20px;',
            'top-right': 'right: 20px; top: 20px;',
            'top-left': 'left: 20px; top: 20px;'
        };
        return positions[pos] || positions['bottom-right'];
    };

    // Estilos CSS
    const styles = `
        .mcw-widget-container * {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }
        .mcw-fab {
            position: fixed;
            ${getPositionStyles(config.position)}
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: ${config.color};
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s, box-shadow 0.2s;
            z-index: 99998;
        }
        .mcw-fab:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
        .mcw-fab svg {
            width: 28px;
            height: 28px;
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
            background: ${config.color};
            color: white;
            padding: 20px;
            text-align: center;
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
            border-color: ${config.color};
            box-shadow: 0 0 0 3px ${config.color}20;
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
        .mcw-btn-submit {
            width: 100%;
            padding: 14px;
            background: ${config.color};
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
    `;

    // Iconos SVG
    const icons = {
        whatsapp: '<svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',
        phone: '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>',
        contact_form: '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>',
        close: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>',
        check: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
    };

    // Descripciones por tipo
    const descriptions = {
        whatsapp: 'Déjanos tus datos y te contactamos por WhatsApp',
        phone: 'Déjanos tus datos y te llamamos',
        contact_form: 'Completa el formulario y nos pondremos en contacto'
    };

    // Crear contenedor
    const container = document.createElement('div');
    container.className = 'mcw-widget-container';
    container.innerHTML = `
        <style>${styles}</style>
        <button class="mcw-fab" id="mcw-fab">${icons[config.type] || icons.contact_form}</button>
        <div class="mcw-modal-overlay" id="mcw-overlay">
            <div class="mcw-modal">
                <div class="mcw-modal-header" style="position: relative;">
                    <button class="mcw-btn-close" id="mcw-close">${icons.close}</button>
                    <h3>${config.title}</h3>
                    <p>${descriptions[config.type] || descriptions.contact_form}</p>
                </div>
                <div class="mcw-modal-body" id="mcw-body">
                    <form id="mcw-form">
                        <div class="mcw-form-group">
                            <label>Nombre <span>*</span></label>
                            <input type="text" name="name" required placeholder="Tu nombre">
                            <div class="mcw-error">El nombre es requerido</div>
                        </div>
                        <div class="mcw-form-group">
                            <label>Teléfono <span>*</span></label>
                            <input type="tel" name="phone" required placeholder="+57 300 123 4567">
                            <div class="mcw-error">El teléfono es requerido</div>
                        </div>
                        <div class="mcw-form-group">
                            <label>Email</label>
                            <input type="email" name="email" placeholder="tu@email.com">
                            <div class="mcw-error">Email inválido</div>
                        </div>
                        <div class="mcw-form-group">
                            <label>Mensaje <span>*</span></label>
                            <textarea name="message" required placeholder="¿En qué podemos ayudarte?"></textarea>
                            <div class="mcw-error">El mensaje es requerido</div>
                        </div>
                        <button type="submit" class="mcw-btn-submit">${config.buttonText}</button>
                    </form>
                </div>
            </div>
        </div>
    `;

    // Insertar en el DOM
    document.body.appendChild(container);

    // Referencias a elementos
    const fab = document.getElementById('mcw-fab');
    const overlay = document.getElementById('mcw-overlay');
    const closeBtn = document.getElementById('mcw-close');
    const body = document.getElementById('mcw-body');

    // Guardar HTML original del formulario para restaurarlo después
    const originalFormHtml = body.innerHTML;

    // Abrir modal
    fab.addEventListener('click', () => {
        overlay.classList.add('mcw-active');
    });

    // Cerrar modal
    closeBtn.addEventListener('click', () => {
        overlay.classList.remove('mcw-active');
    });

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.remove('mcw-active');
        }
    });

    // Validar email
    function isValidEmail(email) {
        if (!email) return true; // Email es opcional
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Función para manejar el envío del formulario
    function attachFormSubmit() {
        const currentForm = document.getElementById('mcw-form');
        if (!currentForm) return;

        currentForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Limpiar errores previos
            currentForm.querySelectorAll('.mcw-form-group').forEach(g => g.classList.remove('mcw-invalid'));

            const formData = new FormData(currentForm);
            const data = {
                name: formData.get('name').trim(),
                phone: formData.get('phone').trim(),
                email: formData.get('email').trim(),
                message: formData.get('message').trim(),
                site_id: config.siteId,
                source_type: config.type === 'contact_form' ? 'contact_form' : (config.type + '_button'),
                source_url: window.location.href,
                page_url: window.location.href,
                user_agent: navigator.userAgent
            };

            // Validaciones
            let hasErrors = false;

            if (!data.name) {
                currentForm.querySelector('[name="name"]').closest('.mcw-form-group').classList.add('mcw-invalid');
                hasErrors = true;
            }
            if (!data.phone) {
                currentForm.querySelector('[name="phone"]').closest('.mcw-form-group').classList.add('mcw-invalid');
                hasErrors = true;
            }
            if (!isValidEmail(data.email)) {
                currentForm.querySelector('[name="email"]').closest('.mcw-form-group').classList.add('mcw-invalid');
                hasErrors = true;
            }
            if (!data.message) {
                currentForm.querySelector('[name="message"]').closest('.mcw-form-group').classList.add('mcw-invalid');
                hasErrors = true;
            }

            if (hasErrors) return;

            // Deshabilitar botón
            const submitBtn = currentForm.querySelector('.mcw-btn-submit');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';

            try {
                const response = await fetch(config.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    throw new Error('Error al enviar');
                }

                // Mostrar éxito
                body.innerHTML = `
                    <div class="mcw-success">
                        ${icons.check}
                        <h4>¡Mensaje enviado!</h4>
                        <p>Nos pondremos en contacto contigo pronto.</p>
                    </div>
                `;

                // Acción post-envío según tipo
                setTimeout(() => {
                    if (config.type === 'whatsapp' && config.phone) {
                        const cleanPhone = config.phone.replace(/[^0-9]/g, '');
                        const whatsappUrl = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(data.message)}`;
                        window.open(whatsappUrl, '_blank');
                    } else if (config.type === 'phone' && config.phone) {
                        window.location.href = `tel:${config.phone}`;
                    }

                    // Cerrar modal después de 2 segundos
                    setTimeout(() => {
                        overlay.classList.remove('mcw-active');
                        // Reset form después de que se cierre el modal
                        setTimeout(() => {
                            body.innerHTML = originalFormHtml;
                            // Re-attach event listener al nuevo formulario
                            attachFormSubmit();
                        }, 300);
                    }, 2000);
                }, 500);

            } catch (error) {
                console.error('MiniCRM Widget Error:', error);
                submitBtn.disabled = false;
                submitBtn.textContent = config.buttonText;
                alert('Error al enviar el mensaje. Por favor intenta de nuevo.');
            }
        });
    }

    // Attach inicial del formulario
    attachFormSubmit();
})();
