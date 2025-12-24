/**
 * Messages Module - Shared JavaScript Functions
 * 
 * This file contains shared JavaScript functions used across message-related pages
 * to avoid code duplication.
 */

/**
 * Update search button state based on search mode
 * 
 * @param {boolean} isSearchMode - Whether search mode is active
 * @param {HTMLElement} searchToggleBtn - Search toggle button element
 * @param {HTMLElement} searchIcon - Search icon element
 * @param {HTMLFormElement} searchForm - Search form element
 * @param {HTMLInputElement} searchInput - Search input element
 * @param {string} baseUrl - Base URL for assets
 */
function updateButtonState(isSearchMode, searchToggleBtn, searchIcon, searchForm, searchInput, baseUrl) {
    if (isSearchMode) {
        searchToggleBtn.title = 'Search';
        if (searchIcon) {
            searchIcon.innerHTML = '<img src="' + baseUrl + '/assets/icons/magnifying-glass.svg" alt="search" width="16" height="16" class="icon-inline me-0 mb-1">';
        }
        searchToggleBtn.onclick = function() {
            searchForm.submit();
        };
    } else {
        searchToggleBtn.title = 'Reset';
        if (searchIcon) {
            searchIcon.innerHTML = '<img src="' + baseUrl + '/assets/icons/cancel.svg" alt="reset" width="16" height="16" class="icon-inline me-0 mb-1">';
        }
        searchToggleBtn.onclick = function() {
            searchInput.value = '';
            searchForm.submit();
        };
    }
}

