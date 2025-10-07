
class WishlistManager {
    constructor() {
        this.apiEndpoint = '/app/assets/utils/wishlist-handler.php';
        this.isLoggedIn = document.body.dataset.loggedIn === 'true';
        this.init();
    }
    
    init() {
        if (!this.isLoggedIn) return;
        
        // Use event delegation for dynamic content
        document.addEventListener('click', (e) => {
            const wishlistBtn = e.target.closest('.wishlist-btn');
            if (wishlistBtn) {
                e.preventDefault();
                e.stopPropagation();
                
                // Extract design ID from onclick attribute or data attribute
                const onclickAttr = wishlistBtn.getAttribute('onclick');
                const designId = onclickAttr ? 
                    parseInt(onclickAttr.match(/\d+/)[0]) : 
                    parseInt(wishlistBtn.dataset.designId);
                
                if (designId) {
                    this.toggleWishlist(designId, wishlistBtn);
                }
            }
        });
        
        // Initialize any existing wishlist items on page load
        this.initializeWishlistStates();
    }
    
    async toggleWishlist(designId, button) {
        // Disable button during request
        button.disabled = true;
        button.style.cursor = 'wait';
        
        // Golden ratio animation
        this.animateButton(button);
        
        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: `action=toggle_wishlist&design_id=${designId}`
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Update ALL wishlist buttons for this product
                this.updateAllButtons(designId, data.action);
                
                // Show success notification
                this.showNotification(data.message, 'success');
                
                // Update wishlist counter if exists
                this.updateWishlistCounter(data.action);
                
            } else {
                // Handle specific error cases
                if (data.message === 'Please login') {
                    this.handleLoginRequired();
                } else {
                    this.showNotification(data.message || 'Error updating wishlist', 'error');
                }
            }
            
        } catch (error) {
            console.error('Wishlist error:', error);
            this.showNotification('Failed to update wishlist. Please try again.', 'error');
            
        } finally {
            // Re-enable button
            button.disabled = false;
            button.style.cursor = 'pointer';
        }
    }
    
    updateAllButtons(designId, action) {
        // Find ALL buttons for this product ID (featured + grid sections)
        const buttons = document.querySelectorAll(
            `.wishlist-btn[onclick*="${designId}"], .wishlist-btn[data-design-id="${designId}"]`
        );
        
        buttons.forEach(button => {
            const heart = button.querySelector('i');
            if (!heart) return;
            
            if (action === 'added') {
                // Update to filled heart
                heart.classList.remove('far');
                heart.classList.add('fas');
                button.classList.add('active');
                
                // Add pulse animation
                this.pulseAnimation(heart);
                
            } else if (action === 'removed') {
                // Update to empty heart
                heart.classList.remove('fas');
                heart.classList.add('far');
                button.classList.remove('active');
                
                // Add fade animation
                this.fadeAnimation(heart);
            }
        });
    }
    
    animateButton(button) {
        // Golden ratio scale animation
        button.style.transition = 'transform 0.382s ease';
        button.style.transform = 'scale(1.618)';
        
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 382);
    }
    
    pulseAnimation(element) {
        element.style.animation = 'wishlist-pulse 0.618s ease';
        setTimeout(() => {
            element.style.animation = '';
        }, 618);
    }
    
    fadeAnimation(element) {
        element.style.animation = 'wishlist-fade 0.382s ease';
        setTimeout(() => {
            element.style.animation = '';
        }, 382);
    }
    
    showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingToast = document.querySelector('.wishlist-toast');
        if (existingToast) {
            existingToast.remove();
        }
        
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = `wishlist-toast wishlist-toast-${type}`;
        toast.innerHTML = `
            <div class="wishlist-toast-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Auto remove after golden ratio timing
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 382);
        }, 2618); // 1.618 * 1618ms
    }
    
    updateWishlistCounter(action) {
        const counter = document.querySelector('.wishlist-counter');
        if (!counter) return;
        
        let count = parseInt(counter.textContent) || 0;
        
        if (action === 'added') {
            count++;
        } else if (action === 'removed' && count > 0) {
            count--;
        }
        
        counter.textContent = count;
        
        // Animate counter
        counter.style.animation = 'wishlist-bounce 0.382s ease';
        setTimeout(() => {
            counter.style.animation = '';
        }, 382);
    }
    
    handleLoginRequired() {
        // You can customize this based on your login modal/page
        if (confirm('Please login to add items to wishlist. Would you like to login now?')) {
            window.location.href = '/login.php?redirect=' + encodeURIComponent(window.location.href);
        }
    }
    
    initializeWishlistStates() {
        // Check all wishlist buttons and ensure correct state
        document.querySelectorAll('.wishlist-btn').forEach(button => {
            const heart = button.querySelector('i');
            if (heart) {
                if (button.classList.contains('active')) {
                    heart.classList.remove('far');
                    heart.classList.add('fas');
                } else {
                    heart.classList.remove('fas');
                    heart.classList.add('far');
                }
            }
        });
    }
    
    // Public method to manually refresh wishlist from server
    async refreshWishlist() {
        try {
            const response = await fetch(this.apiEndpoint + '?action=get_wishlist', {
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.wishlist) {
                    data.wishlist.forEach(designId => {
                        this.updateAllButtons(designId, 'added');
                    });
                }
            }
        } catch (error) {
            console.error('Failed to refresh wishlist:', error);
        }
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.wishlistManager = new WishlistManager();
    });
} else {
    // DOM already loaded
    window.wishlistManager = new WishlistManager();
}

// Global function for backward compatibility with onclick attributes
function toggleWishlist(designId) {
    if (window.wishlistManager) {
        const button = event.currentTarget || event.target;
        window.wishlistManager.toggleWishlist(designId, button);
    }
    return false;
}

