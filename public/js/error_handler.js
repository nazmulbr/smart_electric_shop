// Frontend Error Handler
// Include this in your HTML pages to catch and display JavaScript errors

window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e);
    
    // Create error display
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger';
    errorDiv.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;max-width:500px;padding:15px;box-shadow:0 4px 6px rgba(0,0,0,0.1);';
    errorDiv.innerHTML = `
        <strong>❌ JavaScript Error</strong><br>
        <strong>Message:</strong> ${e.message}<br>
        <strong>File:</strong> ${e.filename}<br>
        <strong>Line:</strong> ${e.lineno}<br>
        <strong>Column:</strong> ${e.colno}<br>
        <button onclick="this.parentElement.remove()" style="margin-top:10px;padding:5px 10px;background:#f44336;color:white;border:none;cursor:pointer;">Close</button>
    `;
    document.body.appendChild(errorDiv);
    
    // Auto-remove after 10 seconds
    setTimeout(() => {
        if (errorDiv.parentElement) {
            errorDiv.remove();
        }
    }, 10000);
    
    return false;
});

// Handle unhandled promise rejections
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled Promise Rejection:', e);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger';
    errorDiv.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;max-width:500px;padding:15px;box-shadow:0 4px 6px rgba(0,0,0,0.1);';
    errorDiv.innerHTML = `
        <strong>❌ Promise Rejection Error</strong><br>
        <strong>Reason:</strong> ${e.reason}<br>
        <button onclick="this.parentElement.remove()" style="margin-top:10px;padding:5px 10px;background:#f44336;color:white;border:none;cursor:pointer;">Close</button>
    `;
    document.body.appendChild(errorDiv);
    
    setTimeout(() => {
        if (errorDiv.parentElement) {
            errorDiv.remove();
        }
    }, 10000);
});

// Form validation error display
function showFormError(formElement, message) {
    // Remove existing error
    const existingError = formElement.querySelector('.form-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Create error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger form-error';
    errorDiv.style.cssText = 'margin-top:10px;';
    errorDiv.innerHTML = `<strong>❌ Error:</strong> ${message}`;
    formElement.insertBefore(errorDiv, formElement.firstChild);
    
    // Scroll to error
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// AJAX error handler
function handleAjaxError(xhr, status, error) {
    let errorMessage = '<strong>❌ AJAX Request Failed</strong><br>';
    errorMessage += `<strong>Status:</strong> ${status}<br>`;
    errorMessage += `<strong>Error:</strong> ${error}<br>`;
    
    if (xhr.responseText) {
        try {
            const response = JSON.parse(xhr.responseText);
            if (response.error) {
                errorMessage += `<strong>Details:</strong> ${response.error}<br>`;
            }
        } catch (e) {
            errorMessage += `<strong>Response:</strong> ${xhr.responseText.substring(0, 200)}<br>`;
        }
    }
    
    errorMessage += `<strong>Status Code:</strong> ${xhr.status}<br>`;
    errorMessage += `<strong>URL:</strong> ${xhr.responseURL || 'N/A'}`;
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger';
    errorDiv.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;max-width:500px;padding:15px;box-shadow:0 4px 6px rgba(0,0,0,0.1);';
    errorDiv.innerHTML = errorMessage + '<button onclick="this.parentElement.remove()" style="margin-top:10px;padding:5px 10px;background:#f44336;color:white;border:none;cursor:pointer;">Close</button>';
    document.body.appendChild(errorDiv);
    
    setTimeout(() => {
        if (errorDiv.parentElement) {
            errorDiv.remove();
        }
    }, 15000);
}

// Console error logger
const originalConsoleError = console.error;
console.error = function(...args) {
    originalConsoleError.apply(console, args);
    
    // Log to server if needed (optional)
    if (typeof logErrorToServer === 'function') {
        logErrorToServer(args.join(' '));
    }
};

