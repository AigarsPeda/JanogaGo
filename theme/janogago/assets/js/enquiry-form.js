document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.jg-enquiry-form').forEach((form, formIndex) => {
    const copy = JSON.parse(form.dataset.feedback || '{}');
    const result = form.querySelector('#jg-enquiry-result');
    const submit = form.querySelector('[type="submit"]');
    if (!result || !submit) return;
    form.noValidate = true;
    let pending = false;

    const clearError = field => {
      field.setCustomValidity('');
      field.removeAttribute('aria-invalid');
      const id = `jg-error-${formIndex}-${field.name}`;
      const descriptions = (field.getAttribute('aria-describedby') || '').split(' ').filter(value => value && value !== id);
      if (descriptions.length) field.setAttribute('aria-describedby', descriptions.join(' '));
      else field.removeAttribute('aria-describedby');
      document.getElementById(id)?.remove();
    };
    const showErrors = errors => {
      Object.entries(errors).forEach(([name, message]) => {
        const field = form.elements.namedItem(name);
        if (!field || typeof field.setCustomValidity !== 'function') return;
        clearError(field);
        field.setCustomValidity(message);
        field.setAttribute('aria-invalid', 'true');
        const error = document.createElement('span');
        error.className = 'jg-field-error';
        error.id = `jg-error-${formIndex}-${name}`;
        error.textContent = message;
        field.setAttribute('aria-describedby', [field.getAttribute('aria-describedby'), error.id].filter(Boolean).join(' '));
        field.closest('label').append(error);
      });
    };
    const showResult = (message, success) => {
      result.classList.remove('jg-notice-closing', 'form-message-success', 'form-message-error');
      result.classList.add(success ? 'form-message-success' : 'form-message-error');
      result.setAttribute('role', success ? 'status' : 'alert');
      result.querySelector('p').textContent = message;
      result.querySelector('svg path').setAttribute('d', success ? 'm5 12 4 4L19 6' : 'M12 5v8m0 4v1');
      result.hidden = false;
    };
    const finishDismiss = () => {
      if (result.contains(document.activeElement)) submit.focus({preventScroll: true});
      result.hidden = true;
      result.classList.remove('jg-notice-closing');
    };
    const dismiss = () => {
      if (result.hidden || result.classList.contains('jg-notice-closing')) return;
      if (matchMedia('(prefers-reduced-motion: reduce)').matches) finishDismiss();
      else result.classList.add('jg-notice-closing');
    };
    result.querySelector('.jg-notice-close')?.addEventListener('click', dismiss);
    result.addEventListener('animationend', event => {
      if (event.target === result && result.classList.contains('jg-notice-closing')) finishDismiss();
    });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') dismiss(); });
    if (new URL(location.href).searchParams.has('enquiry')) {
      const url = new URL(location.href);
      url.searchParams.delete('enquiry');
      if (url.hash === '#pieteikties') url.hash = '';
      history.replaceState(history.state, '', url);
    }
    ['input', 'change'].forEach(type => form.addEventListener(type, event => {
      if (typeof event.target.setCustomValidity === 'function') clearError(event.target);
    }));

    form.addEventListener('submit', async event => {
      event.preventDefault();
      if (pending) return;
      const fields = [...form.querySelectorAll('input:not([type="hidden"]), textarea')];
      fields.forEach(clearError);
      const errors = {};
      fields.forEach(field => {
        const value = field.value.trim();
        if (field.required && (field.type === 'checkbox' ? !field.checked : !value)) {
          errors[field.name] = field.type === 'checkbox' ? copy.consent_required : copy.field_required;
        } else if (field.name === 'email' && value && (!field.validity.valid || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value))) {
          errors.email = copy.email_invalid;
        } else if (field.name === 'phone' && value) {
          const digits = value.replace(/\D/g, '');
          if (!/^\+?[0-9().\s-]+$/.test(value) || digits.length < 7 || digits.length > 15) errors.phone = copy.phone_invalid;
        } else if (field.name === 'people' && (field.validity.badInput || value && (!/^\d+$/.test(value) || Number(value) <= 0))) {
          errors.people = copy.people_invalid;
        }
        if (field.maxLength > 0 && value.length > field.maxLength) errors[field.name] = copy.too_long;
      });
      if (Object.keys(errors).length) {
        showErrors(errors);
        const name = Object.keys(errors)[0];
        showResult(`${copy[name] || ''}${copy[name] ? ': ' : ''}${errors[name]}`, false);
        form.elements.namedItem(name)?.focus({preventScroll: true});
        return;
      }
      pending = true;
      form.setAttribute('aria-busy', 'true');
      submit.disabled = true;
      const controller = new AbortController();
      const timeout = setTimeout(() => controller.abort(), 45000);
      try {
        const data = new FormData(form);
        data.set('jg_ajax', '1');
        const response = await fetch(form.getAttribute('action'), {method: 'POST', body: data, credentials: 'same-origin', signal: controller.signal});
        const feedback = await response.json();
        if (feedback.nonce) form.elements.namedItem('jg_enquiry_nonce').value = feedback.nonce;
        if (feedback.success) {
          form.reset();
          if (feedback.lead_created === true) document.dispatchEvent(new CustomEvent('jg:enquiry-sent'));
        }
        else showErrors(feedback.errors || {});
        showResult(feedback.message || copy.failed, feedback.success === true);
      } catch {
        showResult(copy.network, false);
      } finally {
        clearTimeout(timeout);
        pending = false;
        form.removeAttribute('aria-busy');
        submit.disabled = false;
      }
    });
  });
});
