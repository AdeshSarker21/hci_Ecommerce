document.addEventListener('alpine:init', () => {
    Alpine.store('cart', {
        open: false,
        items: [],
        count: 0,
        subtotal: 0,
        shipping: 0,
        grandTotal: 0,
        couponCode: '',
        couponDiscount: 0,
        couponApplied: false,
        loading: false,
        toastMessage: '',
        toastType: 'success',
        toastVisible: false,

        init() {
            this.fetchSummary();
            this.startRevalidation();
        },

        async fetchSummary() {
            try {
                const res = await fetch('/cart/summary');
                const data = await res.json();
                if (data.success) {
                    this.items = data.cart_items;
                    this.count = data.cart_count;
                    this.subtotal = data.cart_total;
                    this.shipping = this.subtotal >= 50 ? 0 : 5.99;
                    this.grandTotal = this.subtotal + this.shipping - this.couponDiscount;
                }
            } catch (e) {
                console.error('Cart summary fetch failed:', e);
            }
        },

        async addItem(productId, quantity = 1, selectedVariants = null) {
            this.loading = true;
            try {
                const body = {
                    product_id: productId,
                    quantity: quantity,
                };
                if (selectedVariants) {
                    body.selected_variants = selectedVariants;
                }

                const res = await fetch('/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(body),
                });

                const data = await res.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.count = data.cart_count;
                    this.subtotal = data.cart_total;
                    this.shipping = this.subtotal >= 50 ? 0 : 5.99;
                    this.grandTotal = this.subtotal + this.shipping - this.couponDiscount;
                    await this.fetchSummary();
                    this.open = true;
                } else {
                    this.showToast(data.message, 'error');
                }
            } catch (e) {
                this.showToast('Something went wrong. Please try again.', 'error');
            } finally {
                this.loading = false;
            }
        },

        async updateQuantity(itemId, quantity) {
            if (quantity <= 0) {
                return this.removeItem(itemId);
            }

            this.loading = true;
            try {
                const res = await fetch(`/cart/${itemId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ quantity }),
                });

                const data = await res.json();

                if (data.success) {
                    this.count = data.cart_count;
                    this.subtotal = data.cart_total;
                    this.shipping = this.subtotal >= 50 ? 0 : 5.99;
                    this.grandTotal = this.subtotal + this.shipping - this.couponDiscount;
                    await this.fetchSummary();
                } else {
                    this.showToast(data.message, 'error');
                }
            } catch (e) {
                this.showToast('Something went wrong.', 'error');
            } finally {
                this.loading = false;
            }
        },

        async removeItem(itemId) {
            this.loading = true;
            try {
                const res = await fetch(`/cart/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                });

                const data = await res.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.count = data.cart_count;
                    this.subtotal = data.cart_total;
                    this.shipping = this.subtotal >= 50 ? 0 : 5.99;
                    this.grandTotal = this.subtotal + this.shipping - this.couponDiscount;
                    await this.fetchSummary();
                } else {
                    this.showToast(data.message, 'error');
                }
            } catch (e) {
                this.showToast('Something went wrong.', 'error');
            } finally {
                this.loading = false;
            }
        },

        async clearCart() {
            this.loading = true;
            try {
                const res = await fetch('/cart', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                });

                const data = await res.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.items = [];
                    this.count = 0;
                    this.subtotal = 0;
                    this.shipping = 0;
                    this.grandTotal = 0;
                    this.couponDiscount = 0;
                    this.couponApplied = false;
                    this.couponCode = '';
                }
            } catch (e) {
                this.showToast('Something went wrong.', 'error');
            } finally {
                this.loading = false;
            }
        },

        async applyCoupon() {
            if (!this.couponCode.trim()) return;

            this.loading = true;
            try {
                const res = await fetch('/cart/coupon', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ code: this.couponCode }),
                });

                const data = await res.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.couponDiscount = data.discount;
                    this.couponApplied = true;
                    this.grandTotal = this.subtotal + this.shipping - this.couponDiscount;
                } else {
                    this.showToast(data.message, 'error');
                }
            } catch (e) {
                this.showToast('Something went wrong.', 'error');
            } finally {
                this.loading = false;
            }
        },

        async removeCoupon() {
            this.loading = true;
            try {
                const res = await fetch('/cart/coupon', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                });

                const data = await res.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.couponDiscount = 0;
                    this.couponApplied = false;
                    this.couponCode = '';
                    this.grandTotal = this.subtotal + this.shipping;
                }
            } catch (e) {
                this.showToast('Something went wrong.', 'error');
            } finally {
                this.loading = false;
            }
        },

        getItemQuantity(itemId) {
            const item = this.items.find(i => i.id === itemId);
            return item ? item.quantity : 0;
        },

        showToast(message, type = 'success') {
            this.toastMessage = message;
            this.toastType = type;
            this.toastVisible = true;
            setTimeout(() => {
                this.toastVisible = false;
            }, 3000);
        },

        startRevalidation() {
            setInterval(async () => {
                if (!this.loading && this.count > 0) {
                    await this.fetchSummary();
                }
            }, 60000);
        },

        get formattedSubtotal() {
            return '$' + this.subtotal.toFixed(2);
        },

        get formattedShipping() {
            return this.shipping === 0 ? 'Free' : '$' + this.shipping.toFixed(2);
        },

        get formattedGrandTotal() {
            return '$' + this.grandTotal.toFixed(2);
        },

        get formattedCouponDiscount() {
            return this.couponDiscount > 0 ? '-$' + this.couponDiscount.toFixed(2) : '$0.00';
        },
    });
});
