<template>
<div class="mailtrapper">
    <div v-if="!isOpen" class="mailtrapper-alert" :class="{'mailtrapper-has-new':hasNew}" @click="open">
        <i class="fas fa-envelope"></i>
    </div>

    <template v-if="isOpen">

    <div class="mailtrapper-wrapper" @click.self="close"></div>
    <div class="mailtrapper-panel">

        <div v-if="!messages.length" class="mailtrapper-empty">
            <p>No recent emails</p>
            <p><a href="#" @click.prevent="close">Close</a></p>
        </div>

        <mailtrapper-message
            v-else
            v-for="message in messages"
            :message="message"
            :key="message.id"
            :open-message="openMessage"
            @open="openMessage = message.id"
            @close="openMessage = null"
        />
    </div>
    </template>

</div>
</template>
<script>
import axios from 'axios';
import MailtrapperMessage from "./MailtrapperMessage";
export default {
    mounted() {
        this.ping();
        setInterval(this.ping,3000);
        this.localLastSeen = window.localStorage.getItem('mailtrapperLastSeen') || 0;
    },
    data() {
        return {
            isOpen: false,
            messages: [],
            hasNew: false,
            openMessage: null,
            localLastSeen: 0
        }
    },
    computed: {
        lastSeen: {
            get() {
                return this.localLastSeen;
            },
            set(v) {
                this.localLastSeen = v;
                window.localStorage.setItem('mailtrapperLastSeen',v);
            }
        }
    },
    methods: {
        open() {
            this.lastSeen = this.messages.length ? this.messages[0].id : 0;
            this.hasNew = false;
            document.body.style.overflow = 'hidden';
            this.isOpen = true;
        },
        close() {
            document.body.style.overflow = 'auto';
            this.lastSeen = this.messages.length ? this.messages[0].id : 0;
            this.isOpen = false;
        },
        ping() {
            axios.get('/mailtrapper-ui/inbox')
            .then(r => {
                this.hasNew = false;
                if(r.data.length) {
                    let mails = r.data.reverse();
                    let first = r.data[mails.length - 1];
                    if(first.id > this.lastSeen && (first.created_at * 1000) > (new Date).getTime() - 30000) {
                        this.hasNew = true;
                    }
                    mails.forEach(m => {
                        if(!_.find(this.messages,{id:m.id})) {
                            this.messages.unshift(m);
                        }
                    });
                }
            });
        }
    },
    components: {
        MailtrapperMessage
    }
}
</script>
<style>
@keyframes mailtrapper-pulse {
    0% {
        transform: scale(1,1);
    }
    50% {
        transform: scale(1.2,1.2);
        bottom: 5px;
        right: 5px;
    }
}
.mailtrapper-alert {
    background: #999;
    color: #fff;
    line-height: 1em;
    padding: 10px;
    cursor: pointer;
}
.mailtrapper-has-new {
    background: #f00;
    animation: mailtrapper-pulse 1s infinite;
}
.mailtrapper {
    position: fixed;
    top: 0;
    right: 0;
	z-index: 9999999;
}
.mailtrapper-wrapper {
    background: rgba(255,255,255,0.7);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 99998;
}
.mailtrapper-panel {
    position: fixed;
    bottom: 0;
    overflow-y: scroll;
    right: 0;
    background: #fff;
    box-shadow: 0 0 10px rgba(0,0,0,0.15);
    width: 500px;
    max-width: 100%;
    top: 0;
    z-index: 99999;

}
.mailtrapper-empty {
    color: #999;
    padding: 10px;
    text-align: center;
}
</style>
