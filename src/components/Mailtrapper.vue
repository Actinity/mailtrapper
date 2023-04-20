<template>
	<div class="mailtrapper">
		<div v-if="!isOpen" class="mailtrapper-alert" :class="{'mailtrapper-has-new':hasNew}" @click="open">
			<i class="fas fa-envelope"></i>
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
