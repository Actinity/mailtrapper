<template>
	<div class="mailtrapper">
		<div v-if="!isOpen" class="mailtrapper-alert" :class="{'mailtrapper-has-new':hasNew}" @click="open">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20">
				<!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#ffffff" d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48L48 64zM0 176L0 384c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-208L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/>
			</svg>
		</div>

		<template v-if="isOpen">

			<div class="mailtrapper-wrapper" @click.self="close"></div>
			<div class="mailtrapper-panel">
				<div class="mailtrapper-messages" ref="messages">
					<div v-if="!messages.length" class="mailtrapper-empty">
						<p>No recent emails</p>
						<p><a href="#" @click.prevent="close">Close</a></p>
					</div>

					<mailtrapper-message
						v-else
						v-for="message in messages"
						:message="message"
						:key="message.id"
						:open-message="currentlyOpenMessage"
						@open="openMessage(message, $event)"
						@close="currentlyOpenMessage = null"
					/>
				</div>

				<div class="mailtrapper-footer" @click="empty" v-if="canEmpty">
					Empty
				</div>
			</div>
		</template>

	</div>
</template>
<script>
import axios from 'axios';
import MailtrapperMessage from "./MailtrapperMessage.vue";
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
			currentlyOpenMessage: null,
			localLastSeen: 0,
			canEmpty: false
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
		openMessage(message,element) {
			let top = element.getBoundingClientRect().top;

			this.currentlyOpenMessage = message.id;

			this.$nextTick(() => {
				this.$refs.messages.scrollTo({
					left: 0,
					top: element.offsetTop - top,
					behaviour: 'auto'
				});
			});
		},
		empty() {
			if(confirm('Are you sure you want to permanently delete all trapped emails?')) {
				axios.delete('/mailtrapper-ui')
					.catch(e => {
						alert('Unable to delete messages');
						this.ping();
					})
					.then(e => {
						this.ping();
					});

				this.messages = [];
			}
		},
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
			if(window.sessionStorage.getItem('mailtrapper-pause')) {
				return;
			}
			axios.get('/mailtrapper-ui/inbox')
				.then(r => {
					this.hasNew = false;
					this.canEmpty = r.data.can_empty;
					if(r.data.messages.length) {
						let mails = r.data.messages.reverse();
						let first = r.data.messages[mails.length - 1];
						if(first.id > this.lastSeen && (first.created_at * 1000) > (new Date).getTime() - 30000) {
							this.hasNew = true;
						}
						let existingIds = this.messages.map((m) => m.id);
						mails.forEach(m => {
							if(!~existingIds.indexOf(m.id)) {
								this.messages.unshift(m);
							}
						});
					}
				})
				.catch(e => {
					console.log(e,e.response);
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
	right: 0;
	background: #fff;
	box-shadow: 0 0 10px rgba(0,0,0,0.15);
	width: 600px;
	max-width: 90%;
	top: 0;
	z-index: 99999;
	display: flex;
	flex-direction: column;

}
.mailtrapper-messages {
	flex: 1;
	overflow-y: scroll;
}
.mailtrapper-footer {
	padding: 7px 10px;
	background: #eee;
	color: #999;
	text-align: center;
	cursor: pointer;
	display: block;
}
.mailtrapper-footer:hover {
	color: #000;
}
.mailtrapper-empty {
	color: #999;
	padding: 10px;
	text-align: center;
}
</style>
