<template>
    <div>
        <div v-if="!this.isUploaded" class="loading">
            Please wait, processing file...
        </div>
        <div v-if="!this.isUploaded" class="progress">
            <div ref="progressBar" class="progress-bar" role="progressbar" aria-valuenow="70"
                 aria-valuemin="0" aria-valuemax="100" style="width:0%">
                <span style="color:#000000;" ref="status">{{progress}}%</span>
            </div>
        </div>
        <button
                class="btn btn-default"
                @click="redirectToEditPage()"
                v-if="this.isUploaded"
        >
            Go to the Edit page
        </button>
    </div>
</template>

<script>
    import UrlResolver from '../services/UrlResolver';

    export default {
        data: function() {
            return {
                progress: 0,
                isUploaded: false
            }
        },
        props: ['hash'],
        mounted: function () {
            this.$nextTick(function () {
                this.init();
            });
        },
        methods: {
            init: function () {
                this.urlResolver = new UrlResolver;
                this.loadData();
            },
            loadData: function () {
                let url = this.urlResolver.getUploadStatus();
                let data = {hash: this.hash};

                this.$http.get(url, {params: data}).then((response) => {
                    if (!!response) {
                        this.updateStatus(response.body.status);
                    }
                }, (response) => {
                    console.error(response);
                });
            },
            updateStatus: function(status) {
                this.progress = status;
                this.$refs.progressBar.style.width = status+"%";
                if (status > 10) {
                    this.$refs.status.style.color = "white";
                }
                if (status < 100) {
                    setTimeout(this.loadData, 3000);
                }
                if (status === 100) {
                    this.displaySuccessMessage();
                }
            },
            redirectToEditPage: function () {
                window.location.href = this.urlResolver.editFile();
            },
            displaySuccessMessage() {
                setTimeout(() => this.isUploaded = true, 1000);

                setTimeout(() =>
                        this.$toasted.success("File has been uploaded successfully.", {
                            duration: 1500,
                            className: 'toast-success'
                        }),
                    1000
                );
            },
        }
    }
</script>
