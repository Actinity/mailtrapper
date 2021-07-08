<template>
<div class="mailtrapper-message" @click="toggle" :class="{'mailtrapper-selected':isOpen}">
    <div class="mailtrapper-date" v-text="date"></div>
    <div class="mailtrapper-to"
        :title="from"
    >To: {{ message.to }}</div>
    <div class="mailtrapper-subject">{{ message.subject }}</div>

    <iframe class="mailtrapper-frame" v-if="isOpen" :scrolling="allowScrolling" :src="src" :height="frameHeight"></iframe>

</div>
</template>
<script>
export default {
    props: [
        'message',
        'openMessage'
    ],
    data() {
        return {
            show: false,
            frameHeight: 100,
            allowScrolling: true
        }
    },
    mounted() {
        window.addEventListener('message',this.receiveMessage);
    },
    beforeDestroy() {
        window.removeEventListener('message',this.receiveMessage);
    },
    methods: {
        open() {
            this.$emit('open');
        },
        close() {
            this.$emit('close');
        },
        toggle() {
            if(!this.isOpen) {
                this.open();
            } else {
                this.close();
            }
        },
        receiveMessage(message) {
            if(message.data.mailtrapperId) {
                if(message.data.mailtrapperId == this.message.id) {
                    this.frameHeight = message.data.mailtrapperHeight;
                    this.allowScrolling = false;
                }
            }
        }
    },
    computed: {
        isOpen() {
            return this.message.id === this.openMessage;
        },
        from() {
            return 'From: '+ this.message.from;
        },
        date() {
            return moment.unix(this.message.created_at).fromNow();
        },
        src() {
            return '/mailtrapper-ui/message/' + this.message.id;
        }
    }
}
</script>
<style lang="scss">
.mailtrapper-message {
    padding: 10px;
    & + .mailtrapper-message {
        border-top: 1px solid #ccc;
    }
    font-size: 12px;
    line-height: 1.4em;
    cursor: pointer;
}
.mailtrapper-date {
    float: right;
    color: #09c;
    user-select: none;
}
.mailtrapper-selected {
    background-color: #def;
}
.mailtrapper-to {
    cursor: help;
    user-select: none;
}
.mailtrapper-subject {
    font-weight: bold;
    user-select: none;
}
.mailtrapper-frame {
    border: none;
    margin-top: 10px;
    margin-left: -10px;
    margin-right: -10px;
    width: calc(100% + 20px);
}
</style>
