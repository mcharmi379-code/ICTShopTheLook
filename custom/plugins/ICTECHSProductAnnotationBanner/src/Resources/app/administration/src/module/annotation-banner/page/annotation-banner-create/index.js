Shopware.Component.extend('annotation-banner-create', 'annotation-banner-detail', {
    methods: {
        getBannerData() {
            this.annotationBanner = this.repository.create(Shopware.Context.api);
        },
        onClickSave() {
            this.isLoading = true;

            this.repository
                .save(this.annotationBanner, Shopware.Context.api)
                .then(() => {
                    this.isLoading = false;
                    this.$router.push({name: 'annotation.banner.detail', params: {id: this.annotationBanner.id}});
                    this.addProductSection();
                }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('annotation_banner.error.title'),
                    message: this.$tc('annotation_banner.error.message'),
                });
            });
        }
    }
});
