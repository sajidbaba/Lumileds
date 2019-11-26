<template>
    <a @click="sendDeadlineReminder" class="pull-right" href="javascript: void(0);">
        <span class="glyphicon glyphicon-bell" aria-hidden="true"></span>
    </a>
</template>

<script>
    import UrlResolver from '../services/UrlResolver';

    export default {
        props: ['id'],
        mounted: function () {
            this.init();
        },
        methods: {
            init: function () {
                this.urlResolver = new UrlResolver;
            },
            sendDeadlineReminder: function () {
                let url = this.urlResolver.sendDeadlineReminderToCountry(this.id);

                this.$http.post(url).then((response) => {
                    if (!!response) {
                        this.$toasted.success("Deadline reminder has been sent successfully.", {
                            duration: 1500,
                            className: 'toast-success'
                        });
                    }
                },(response) => {
                    this.$toasted.error("Something went wrong: " + response.statusText);
                });
            },
        },
    }
</script>
