<template>
  <div class="chat-notification" :class="{ 'show': show }">
    <div class="notification-content">
      <div class="notification-header">
        <img :src="data.sender.avatar" :alt="data.sender.name" class="sender-avatar">
        <div class="sender-info">
          <h4>{{ data.sender.name }}</h4>
          <span>{{ data.timestamp | timeAgo }}</span>
        </div>
        <button @click="close" class="close-btn">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="notification-body">
        <p>{{ data.content }}</p>
        <div v-if="data.attachment" class="attachment-info">
          <i class="fas fa-paperclip"></i>
          <span>{{ data.attachment.filename }}</span>
        </div>
      </div>
      <div class="notification-actions">
        <button @click="openChat" class="action-btn primary">
          View Message
        </button>
        <button @click="close" class="action-btn">
          Dismiss
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';

export default {
  name: 'ChatNotification',
  props: {
    data: {
      type: Object,
      required: true
    }
  },
  setup(props, { emit }) {
    const router = useRouter();
    const show = ref(false);
    let timeout;

    const close = () => {
      show.value = false;
      emit('close');
    };

    const openChat = () => {
      router.push(`/chat/${props.data.chatId}`);
      close();
    };

    onMounted(() => {
      show.value = true;
      timeout = setTimeout(close, 5000); // Auto close after 5 seconds
    });

    onUnmounted(() => {
      if (timeout) clearTimeout(timeout);
    });

    return {
      show,
      close,
      openChat
    };
  }
};
</script>

<style scoped>
.chat-notification {
  position: fixed;
  top: 20px;
  right: 20px;
  width: 320px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transform: translateX(120%);
  transition: transform 0.3s ease-out;
  z-index: 1100;
}

.chat-notification.show {
  transform: translateX(0);
}

.notification-content {
  padding: 16px;
}

.notification-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.sender-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.sender-info {
  flex: 1;
}

.sender-info h4 {
  margin: 0;
  font-size: 14px;
  color: #111827;
}

.sender-info span {
  font-size: 12px;
  color: #6B7280;
}

.close-btn {
  background: none;
  border: none;
  color: #6B7280;
  cursor: pointer;
  padding: 4px;
}

.notification-body {
  margin-bottom: 12px;
}

.notification-body p {
  margin: 0;
  font-size: 14px;
  color: #374151;
  line-height: 1.5;
}

.attachment-info {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
  font-size: 12px;
  color: #6B7280;
}

.notification-actions {
  display: flex;
  gap: 8px;
}

.action-btn {
  flex: 1;
  padding: 8px 12px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.action-btn.primary {
  background: #4F46E5;
  color: white;
}

.action-btn.primary:hover {
  background: #4338CA;
}

.action-btn:not(.primary) {
  background: #F3F4F6;
  color: #374151;
}

.action-btn:not(.primary):hover {
  background: #E5E7EB;
}

@media (max-width: 768px) {
  .chat-notification {
    width: calc(100% - 40px);
  }
}
</style> 