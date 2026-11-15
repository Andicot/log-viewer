'use strict';
document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-log-copy]');
    if (!button || button.disabled) return;
    const text = document.getElementById(button.dataset.logCopy);
    const status = button.parentElement.querySelector('[data-copy-status]');
    const label = button.parentElement.querySelector('[data-copy-label]');
    button.disabled = true;
    status.textContent = '';
    try {
        await navigator.clipboard.writeText(text.value);
        text.hidden = true;
        label.hidden = true;
        status.textContent = button.dataset.success;
    } catch (error) {
        status.textContent = button.dataset.error;
        text.hidden = false;
        label.hidden = false;
        text.focus();
        text.select();
    } finally {
        button.disabled = false;
    }
});
