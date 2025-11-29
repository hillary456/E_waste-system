 
const API_URL = "http://localhost/electronic-waste/backend/src/api";
 
let currentUser = null;
let isLoginMode = true;
let selectedUserType = 'donor';
 
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
    checkSession();
});

function initializeApp() {
    setupEventListeners();
    setupSmoothScrolling();
    setupMobileMenu();
    setupFormValidation();
    animateOnScroll();
     
    switchTab('login');
}

function checkSession() {
    const storedUser = sessionStorage.getItem('currentUser');
    if (storedUser) {
        currentUser = JSON.parse(storedUser);
    }
}

function setupEventListeners() {
    document.querySelectorAll('.nav-link').forEach(link => link.addEventListener('click', handleNavClick));
    
    const donationForm = document.getElementById('donationForm');
    if (donationForm) donationForm.addEventListener('submit', handleDonationSubmit);
    
    const contactForm = document.getElementById('contactForm');
    if (contactForm) contactForm.addEventListener('submit', handleContactSubmit);
    
    const authForm = document.getElementById('authForm');
    if (authForm) authForm.addEventListener('submit', handleAuthSubmit);
    
    document.querySelectorAll('.user-type-btn').forEach(btn => btn.addEventListener('click', handleUserTypeSelect));
    window.addEventListener('click', handleModalClick);
    document.querySelectorAll('.nav-item').forEach(item => item.addEventListener('click', handleDashboardNavClick));

    const logoutBtn = document.getElementById('logoutBtn');
    if(logoutBtn) logoutBtn.addEventListener('click', logout);
}
 

function handleNavClick(e) {
    e.preventDefault();
    const targetId = e.target.getAttribute('href').substring(1);
    scrollToSection(targetId);
}

function scrollToSection(sectionId) {
    const element = document.getElementById(sectionId);
    if (element) {
        const headerHeight = document.querySelector('.header').offsetHeight;
        const elementPosition = element.offsetTop - headerHeight - 20;
        window.scrollTo({ top: elementPosition, behavior: 'smooth' });
    }
}

function setupSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            scrollToSection(targetId);
        });
    });
}

function setupMobileMenu() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                hamburger.classList.remove('active');
            });
        });
    }
}

 
function setupFormValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => { 
        form.addEventListener('input', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') {
               clearFieldError(e);
            }
        });
        form.addEventListener('focusout', function(e) {
             if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') {
               if(e.target.hasAttribute('required')) validateField({target: e.target});
            }
        });
    });
}

function validateField(e) {
    const field = e.target;
    const value = field.value.trim();
    field.classList.remove('error'); 
    if (field.offsetParent === null) return true;  

    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    return true;
}

function showFieldError(field, message) {
    field.classList.add('error');
    const existingError = field.parentNode.querySelector('.error-message');
    if (existingError) existingError.remove();
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.color = '#ef4444';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(e) {
    const field = e.target;
    field.classList.remove('error');
    const errorMessage = field.parentNode.querySelector('.error-message');
    if (errorMessage) errorMessage.remove();
} 
async function handleDonationSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const donationData = Object.fromEntries(formData.entries());
    
    if (!validateForm(e.target)) return;
    if (currentUser && currentUser.user_id) donationData.user_id = currentUser.user_id;
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch(`${API_URL}/donations.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(donationData)
        });
        const result = await response.json();
        if (response.ok) {
            e.target.reset();
            showNotification('Thank you for your generous donation!', 'success');
        } else {
            showNotification(result.message || 'Submission failed', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Connection error.', 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

async function handleContactSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const contactData = Object.fromEntries(formData.entries());
    
    if (!validateForm(e.target)) return;
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch(`${API_URL}/contact.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(contactData)
        });
        const result = await response.json();
        if (response.ok) {
            e.target.reset();
            showNotification('Message sent!', 'success');
        } else {
            showNotification(result.message || 'Failed to send', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Connection error.', 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

function validateForm(form) {
    const requiredFields = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    requiredFields.forEach(field => { 
        if (field.offsetParent !== null) { 
            if (!validateField({ target: field })) isValid = false;
        }
    });
    return isValid;
}

function showNotification(message, type = 'info') {
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()"><i class="fas fa-times"></i></button>
        </div>`;
    
    notification.style.cssText = `position: fixed; top: 100px; right: 20px; background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'}; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 3000; max-width: 400px; animation: slideInRight 0.3s ease;`;
    document.body.appendChild(notification);
    setTimeout(() => { if (notification.parentNode) notification.remove(); }, 5000);
} 
function openAuthModal() {
    const modal = document.getElementById('authModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; 
    if(isLoginMode) switchTab('login'); 
    else switchTab('signup');
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    resetAuthForm();
}

function switchTab(mode) {
    isLoginMode = mode === 'login';
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active')); 
    const buttons = document.querySelectorAll('.tab-btn');
    if(isLoginMode) buttons[0].classList.add('active');
    else buttons[1].classList.add('active');
    
    document.getElementById('modalTitle').textContent = isLoginMode ? 'Welcome Back' : 'Join CFS Kenya';
    document.getElementById('authSubmitBtn').textContent = isLoginMode ? 'Sign In' : 'Create Account';
     
    const nameField = document.getElementById('nameField');
    const confirmPassField = document.getElementById('confirmPasswordField');
    const orgField = document.getElementById('organizationField');
    const locField = document.getElementById('locationField');
    const userTypeField = document.getElementById('userTypeSelection');
    const forgotPassField = document.getElementById('forgotPassword');
    
    nameField.style.display = isLoginMode ? 'none' : 'block';
    confirmPassField.style.display = isLoginMode ? 'none' : 'block';
    orgField.style.display = isLoginMode ? 'none' : 'block';
    locField.style.display = isLoginMode ? 'none' : 'block';
    userTypeField.style.display = isLoginMode ? 'none' : 'block';
    forgotPassField.style.display = isLoginMode ? 'block' : 'none';
     
    const nameInput = document.getElementById('authName');
    const confirmPassInput = document.getElementById('confirmPassword');
    const locationInput = document.getElementById('location');
    
    if (isLoginMode) { 
        if(nameInput) nameInput.removeAttribute('required');
        if(confirmPassInput) confirmPassInput.removeAttribute('required');
        if(locationInput) locationInput.removeAttribute('required');
    } else { 
        if(nameInput) nameInput.setAttribute('required', 'true');
        if(confirmPassInput) confirmPassInput.setAttribute('required', 'true'); 
        if(locationInput) locationInput.setAttribute('required', 'true');
    }
    
    resetAuthForm();
}

function handleUserTypeSelect(e) {
    document.querySelectorAll('.user-type-btn').forEach(btn => btn.classList.remove('active'));
    e.currentTarget.classList.add('active');
    selectedUserType = e.currentTarget.dataset.type;
    
    const orgField = document.getElementById('organizationField');
    const orgLabel = orgField.querySelector('label');
    const orgInput = document.getElementById('authOrganization'); 
    
    if (selectedUserType === 'school') {
        orgLabel.textContent = 'School Name *';
        orgInput.required = true;
        orgInput.placeholder = 'Enter school name';
    } else if (selectedUserType === 'donor') {
        orgLabel.textContent = 'Organization (Optional)';
        orgInput.required = false;
        orgInput.placeholder = 'Company or organization';
    } else {
        orgLabel.textContent = 'Organization *';
        orgInput.required = true;
        orgInput.placeholder = 'Organization name';
    }
}

async function handleAuthSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const authData = Object.fromEntries(formData.entries());
    authData.user_type = selectedUserType;
 
    if (!validateForm(e.target)) return;
    
    if (!isLoginMode) {
        if (authData.password !== authData.confirmPassword) {
            showNotification('Passwords do not match!', 'error');
            return;
        }
    }
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitBtn.disabled = true;
    
    const action = isLoginMode ? 'login' : 'register';
     
    console.log("Sending Auth Data:", JSON.stringify(authData));
    
    try {
        const response = await fetch(`${API_URL}/auth.php?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(authData)
        });

        let result;
        try {
            result = await response.json();
        } catch (jsonError) {
             throw new Error("Server returned invalid JSON. Check API URL.");
        }

        if (response.ok) {
            if (isLoginMode) {
                currentUser = {
                    user_id: result.user_id,
                    name: result.name,
                    email: authData.email,
                    type: result.user_type,
                    organization: result.organization || '',
                    location: result.location || ''
                };
                sessionStorage.setItem('currentUser', JSON.stringify(currentUser));
                closeAuthModal();
                openDashboard();
                showNotification(`Welcome back, ${currentUser.name}!`, 'success');
            } else {
                showNotification('Account created successfully! Please login.', 'success');
                switchTab('login');
            }
        } else {
            showNotification(result.message || 'Authentication failed', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Connection error.', 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

function resetAuthForm() {
    const form = document.getElementById('authForm');
    form.reset();
    document.querySelectorAll('.error-message').forEach(error => error.remove());
    document.querySelectorAll('.error').forEach(field => field.classList.remove('error'));
}
 
function openDashboard() {
    const modal = document.getElementById('dashboardModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    if (currentUser) {
        document.getElementById('dashboardUserName').textContent = currentUser.name;
        document.getElementById('dashboardUserType').textContent = currentUser.type;
        updateSettingsForm();
    }
    showDashboardSection('overview');
}
function closeDashboard() {
    const modal = document.getElementById('dashboardModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}
function showDashboardSection(sectionName) {
    document.querySelectorAll('.dashboard-section').forEach(section => section.classList.remove('active'));
    const targetSection = document.getElementById(sectionName + 'Section');
    if(targetSection) targetSection.classList.add('active');
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
        if(item.textContent.toLowerCase().trim() === sectionName) item.classList.add('active');
    });
}
function handleDashboardNavClick(e) {
    const sectionName = e.target.textContent.toLowerCase().trim();
    showDashboardSection(sectionName);
}
function updateSettingsForm() {
    if (currentUser) {
        if(document.getElementById('settingsName')) document.getElementById('settingsName').value = currentUser.name;
        if(document.getElementById('settingsEmail')) document.getElementById('settingsEmail').value = currentUser.email;
        if(document.getElementById('settingsOrganization')) document.getElementById('settingsOrganization').value = currentUser.organization;
        if(document.getElementById('settingsLocation')) document.getElementById('settingsLocation').value = currentUser.location;
    }
}
function logout() {
    currentUser = null;
    sessionStorage.removeItem('currentUser');
    closeDashboard();
    showNotification('Logged out.', 'success');
}
function handleModalClick(e) {
    if (e.target === document.getElementById('authModal')) closeAuthModal();
    if (e.target === document.getElementById('dashboardModal')) closeDashboard();
}
function animateOnScroll() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('fade-in-up'); });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
    document.querySelectorAll('.value-card, .service-card, .stat-card, .story-card, .region-card').forEach(el => observer.observe(el));
}
window.addEventListener('scroll', () => {
    const header = document.querySelector('.header');
    if (window.scrollY > 100) {
        header.style.background = 'rgba(255, 255, 255, 0.98)';
        header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.15)';
    } else {
        header.style.background = 'rgba(255, 255, 255, 0.95)';
        header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
    }
});
const style = document.createElement('style');
style.textContent = `@keyframes slideInRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } } .notification-content { display: flex; align-items: center; gap: 12px; } .notification-close { background: none; border: none; color: inherit; cursor: pointer; padding: 4px; border-radius: 4px; transition: background-color 0.2s; } .notification-close:hover { background-color: rgba(255, 255, 255, 0.2); } .error { border-color: #ef4444 !important; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important; } .hamburger.active span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); } .hamburger.active span:nth-child(2) { opacity: 0; } .hamburger.active span:nth-child(3) { transform: rotate(-45deg) translate(7px, -6px); }`;
document.head.appendChild(style);