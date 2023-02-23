const { Component, Mixin } = Shopware;
import template from './api-bridge-test-button.html.twig';

Component.register('api-bridge-test-button', {
    template,

    props: ['label'],
    inject: ['apiBridgeTest', 'systemConfigApiService'],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            config: {},
            configKey: 'dfwconnector.config.StoreKey',
            storeKeyElem: {},
            isLoading: false,
            isSaveSuccessful: false,
        };
    },

    methods: {
        saveFinish() {
            this.isSaveSuccessful = false;
        },

        check() {
            this.isLoading = true;
            var storeKey = '';

            if (document.getElementById('dfwconnector.config.StoreKey')) {
                storeKey = document.getElementById('dfwconnector.config.StoreKey').value;
            }

            if (storeKey !== '') {
                this.apiBridgeTest.check(storeKey).then((res) => {
                    if (res.success) {
                        this.isSaveSuccessful = true;
                        this.createNotificationSuccess({
                            title: this.$tc('api-bridge-test-button.title'),
                            message: this.$tc('api-bridge-test-button.success')
                        });
                    } else {
                        var errorMessage = res.message ?? res.error ?? 'Check Bridge failed!';
                        this.createNotificationError({
                            title: this.$tc('api-bridge-test-button.title'),
                            message: errorMessage
                        });
                    }

                    this.isLoading = false;
                });
            } else {
                this.createNotificationError({
                    title: this.$tc('api-bridge-test-button.title'),
                    message: 'StoreKey not defined!'
                });
                this.isSaveSuccessful = true;
                this.isLoading = false;
            }
        }
    }
})