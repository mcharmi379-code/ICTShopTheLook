import template from './annotation-banner-detail.html.twig';
const {Component, Mixin} = Shopware;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();

Component.register('annotation-banner-detail', {
    template,

    inject: [
        'repositoryFactory',
    ],
    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            processSuccess: false,
            isLoading: false,
            uploadTag: 'sw-profile-upload-tag',
            BannerMediaItem: null,
            annotationBanner: null,
            addProductCard: false,
        };
    },

    created() {
        this.getBannerData();
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('product_annotation_banner');
        },

        defaultFolderName(){
            return this.repository;
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },
        ...mapPropertyErrors(
            'annotationBanner', [
                'name',
                'productId',
                'mediaId'
            ]
        ),

        annotationBannerNameTranslatedError() {
            const error = this.annotationBannerNameError;
            const isTranslated = error && error.detail && typeof error.detail === 'string';
            const translations = {
                'This value should not be blank.': 'Dieser Wert sollte nicht leer sein.',             
            };

            return isTranslated ? translations[error.detail] || error.detail : error;
        }
    },

    watch: {
        'annotationBanner.mediaId'(id) {
            if (id) {
                this.setMediaItem({targetId: id});
            }
        }
    },

    methods: {
        getBannerData() {
            this.repository
                .get(this.$route.params.id, Shopware.Context.api)
                .then((entity) => {
                    this.annotationBanner = entity;
                    if (entity) {
                        this.addProductCard = true;
                    }
                });
        },

        onClickSave() {
            this.isLoading = true;
            this.repository
                .save(this.annotationBanner, Shopware.Context.api)
                .then(() => {
                    this.clearCache();
                    this.isLoading = false;
                    this.processSuccess = true;
                }).catch((exception) => {
                this.createNotificationError({
                    title: this.$tc('annotation_banner.error.title'),
                    message: this.$tc('annotation_banner.error.message'),
                });
                this.isLoading = false;
            });
        },

        clearCache() {
            const cacheApiService = Shopware.Service('cacheApiService');
            cacheApiService.clear()
                .then(() => {
                    this.createNotificationSuccess({
                        title: this.$tc('annotation_banner.success.title'),
                        message: this.$tc('annotation_banner.success.message'),
                    });
                    this.processSuccess = false;
                }).catch(() => {
                    this.createNotificationError({
                        title: this.$tc('annotation_banner.error.title_cache'),
                        message: this.$tc('annotation_banner.error.message_cache'),
                    });
                    this.processSuccess = false;
                });
        },

        setMediaItem({targetId}) {
            this.mediaRepository.get(targetId).then((response) => {
                this.BannerMediaItem = response;
            });
            this.annotationBanner.mediaId = targetId;
        },

        onDropMedia(mediaEntity) {
            this.setMediaItem({targetId: mediaEntity.id});
        },

        onUnlinkMedia() {
            this.BannerMediaItem = null;
            this.annotationBanner.imageId = null;
        },

        openMediaSidebar() {
            this.$refs.mediaSidebarItem.openContent();
        },

        saveFinish() {
            this.processSuccess = false;
        },

        addProductSection() {
            this.addProductCard = true;
        }
    }
});
