/**
 * Form Submit Handler with Spinner
 * Prevents double-click on form submissions and shows loading spinner
 */

(function () {
  "use strict";

  /**
   * Show spinner on button and disable it
   * @param {HTMLElement} button - The button element to show spinner on
   * @param {string} loadingText - Text to show while loading (optional)
   * @returns {Object} Object containing originalHTML and originalDisabled state for restoring
   */
  window.showButtonSpinner = function (button, loadingText) {
    if (!button || button.disabled) {
      return null;
    }

    const originalHTML = button.innerHTML;
    const originalDisabled = button.disabled;

    // Determine loading text based on button content or provided text
    let text = loadingText;
    if (!text) {
      // Try to extract text from button (remove icons)
      const btnText = button.textContent.trim() || button.innerText.trim();
      if (btnText.includes("Simpan")) text = "Menyimpan...";
      else if (btnText.includes("Update") || btnText.includes("Memperbarui"))
        text = "Memperbarui...";
      else if (btnText.includes("Kirim")) text = "Mengirim...";
      else if (btnText.includes("Password")) text = "Mengubah Password...";
      else if (btnText.includes("Hapus") || btnText.includes("Delete"))
        text = "Menghapus...";
      else text = "Memproses...";
    }

    button.disabled = true;
    button.innerHTML =
      '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
      text;

    return {
      originalHTML: originalHTML,
      originalDisabled: originalDisabled,
    };
  };

  /**
   * Restore button to original state
   * @param {HTMLElement} button - The button element to restore
   * @param {Object} state - State object returned from showButtonSpinner
   */
  window.hideButtonSpinner = function (button, state) {
    if (!button || !state) return;

    button.disabled = state.originalDisabled;
    button.innerHTML = state.originalHTML;
  };

  /**
   * Initialize form submit handler with spinner
   * @param {HTMLElement|string} formSelector - Form element or CSS selector
   * @param {Object} options - Configuration options
   * @param {string} options.loadingText - Custom loading text
   * @param {string} options.submitButtonSelector - Custom submit button selector (default: 'button[type="submit"]')
   * @param {boolean} options.checkValidity - Whether to check form validity first (default: true)
   */
  window.initFormSubmitHandler = function (formSelector, options) {
    options = options || {};
    const loadingText = options.loadingText || null;
    const submitButtonSelector =
      options.submitButtonSelector || 'button[type="submit"]';
    const checkValidity = options.checkValidity !== false; // default true

    const form =
      typeof formSelector === "string"
        ? document.querySelector(formSelector)
        : formSelector;

    if (!form) {
      console.warn("Form not found:", formSelector);
      return;
    }

    form.addEventListener("submit", function (e) {
      // Check if form is valid (skip if checkValidity is false)
      if (checkValidity && !form.checkValidity()) {
        return; // Browser will show validation errors
      }

      const submitBtn = form.querySelector(submitButtonSelector);
      if (submitBtn) {
        showButtonSpinner(submitBtn, loadingText);
      }
    });
  };

  /**
   * Initialize multiple forms at once
   * @param {Array} forms - Array of form configurations
   * Each config can be:
   * - string (form selector)
   * - object with { selector: string, options: object }
   */
  window.initFormSubmitHandlers = function (forms) {
    if (!Array.isArray(forms)) return;

    forms.forEach(function (config) {
      if (typeof config === "string") {
        initFormSubmitHandler(config);
      } else if (config.selector) {
        initFormSubmitHandler(config.selector, config.options || {});
      }
    });
  };

  /**
   * Auto-initialize forms with data attribute
   * Forms with data-spinner="true" will automatically get spinner handler
   */
  document.addEventListener("DOMContentLoaded", function () {
    const autoInitForms = document.querySelectorAll(
      'form[data-spinner="true"]'
    );
    autoInitForms.forEach(function (form) {
      const loadingText = form.getAttribute("data-spinner-text");
      const checkValidity =
        form.getAttribute("data-spinner-validate") !== "false";

      initFormSubmitHandler(form, {
        loadingText: loadingText,
        checkValidity: checkValidity,
      });
    });
  });
})();
