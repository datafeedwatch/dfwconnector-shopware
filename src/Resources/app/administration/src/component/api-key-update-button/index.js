const { Component, Mixin } = Shopware;
import template from './api-key-update-button.html.twig';

Component.register('api-update-key-button', {
    template,

    props: ['label'],
    inject: ['apiUpdateKey', 'systemConfigApiService'],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            config: {},
            configKey: 'api2CartBridgeInstaller.config.StoreKey',
            storeKeyElem: {},
            isLoading: false,
            isSaveSuccessful: false,
        };
    },

    computed: {
        pluginConfig() {
            var child;

            if (this.$parent.$parent.$parent.$children.length > 1) {
                var rootElement = this.$parent.$parent.$parent.$children;

            } else {
                var rootElement = this.$parent.$parent.$parent.$children[0].$children;
            }

            rootElement.map(function(elem, key) {
                if (typeof(elem.$children[0].$attrs.name) !== 'undefined' && elem.$children[0].$attrs.name.includes("StoreKey")) {
                    child = elem.$children[0];
                }
            });

            if (typeof(child.$refs.component) !== 'undefined') {
                this.storeKeyElem = child.$refs.component;
            } else {
                this.storeKeyElem = child
            }

            this.configKey = this.storeKeyElem.$attrs.name;

            return this.systemConfigApiService.getValues(this.configKey.replaceAll('.StoreKey', '')).then(values => {
                this.config = values;
            });
        }
    },

    methods: {
        saveFinish() {
            this.isSaveSuccessful = false;
        },

        updatekey() {
            this.isLoading = true;
            this.apiUpdateKey.check(this.pluginConfig).then((res) => {
                if (res.success) {
                    this.isSaveSuccessful = true;
                    this.createNotificationSuccess({
                        title: this.$tc('api-update-key-button.title'),
                        message: this.$tc('api-update-key-button.success')
                    });

                    this.$set(this.config, this.configKey, res.storekey);
                    this.systemConfigApiService.saveValues(this.config, null).then(() => {
                        this.isLoading = false;
                        this.storeKeyElem.$emit('input', res.storekey);
                    });
                } else {
                    this.createNotificationError({
                        title: this.$tc('api-update-key-button.title'),
                        message: res.error
                    });
                }
            });
        }
    }
})