<template>
	<div class="mailtrapper-message" @click="toggle" :class="{'mailtrapper-selected':isOpen}">
		<div class="mailtrapper-date" v-text="date"></div>
		<div class="mailtrapper-to"
		     :title="from"
		>To: {{ message.to }}</div>
		<div class="mailtrapper-subject">{{ message.subject }}</div>

		<iframe class="mailtrapper-frame" v-if="isOpen" :src="src" :height="frameHeight"></iframe>

	</div>
</template>
<script>
import {formatDistance} from 'date-fns';
export default {
	props: [
		'message',
		'openMessage'
	],
	data() {
		return {
			show: false,
			frameHeight: 100
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
			this.$emit('open',this.$el);
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
					this.frameHeight = message.data.mailtrapperHeight+1;
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
			return formatDistance(new Date(this.message.created_at *1000),new Date(),{addSuffix:true});
		},
		src() {
			return '/mailtrapper-ui/message/' + this.message.id;
		}
	}
}
</script>
<style scoped>
.mailtrapper-message {
	padding: 10px;
	font-size: 12px;
	line-height: 1.4em;
	cursor: pointer;
	overflow: hidden;
}
.mailtrapper-message + .mailtrapper-message {
	border-top: 1px solid #ccc;
}
.mailtrapper-date {
	float: right;
	color: #09c;
	user-select: none;
}
.mailtrapper-selected {
	background-color: #def;
	padding-bottom: 0;
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
	display: block;
	border: none;
	margin-top: 10px;
	margin-left: -10px;
	margin-right: -10px;
	width: calc(100% + 20px);
	overflow: hidden;
}
</style>
