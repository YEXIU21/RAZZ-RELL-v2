<template>
  <div class="notifications-wrapper" @click.stop>
    <div class="notifications-container">
      <div class="notifications-header">
        <h3>Notifications</h3>
        <button @click="closeNotifications" class="close-btn" type="button">
          &times;
        </button>
      </div>
      <div v-if="!notifications || notifications.length === 0" class="no-notifications">
        No new notifications
      </div>
      <div v-else class="notifications-list">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          class="notification"
        >
          <span :class="['icon', notification.type]">
            <i :class="getIconClass(notification.type)"></i>
          </span>
          <div class="notification-content">
            <span class="message">{{ notification.message }}</span>
            <span class="time">{{ formatTime(notification.created_at) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useAuth } from "@/composables/useAuth";

const notifications = ref([]);
const { token } = useAuth();

const emit = defineEmits(["close"]);

const fetchNotifications = async () => {
  try {
    if (!token.value) return;
    
    const response = await fetch("http://localhost:3000/api/notifications", {
      headers: {
        Authorization: `Bearer ${token.value}`,
      },
    });
    
    if (response.ok) {
      const data = await response.json();
      if (Array.isArray(data)) {
        notifications.value = data;
      } else if (data.notifications) {
        notifications.value = data.notifications;
      } else {
        notifications.value = [];
      }
    }
  } catch (error) {
    notifications.value = [];
  }
};

const getIconClass = (type) => {
  switch (type) {
    case "success":
      return "fas fa-check-circle";
    case "warning":
      return "fas fa-exclamation-triangle";
    case "error":
      return "fas fa-times-circle";
    default:
      return "fas fa-bell";
  }
};

const formatTime = (timestamp) => {
  const date = new Date(timestamp);
  return date.toLocaleString("en-PH", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "numeric",
    hour12: true,
  });
};

const closeNotifications = (event) => {
  event.preventDefault();
  event.stopPropagation();
  emit("close");
};

onMounted(fetchNotifications);
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: var(--overlay-background);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.notifications-wrapper {
  position: absolute;
  top: 100%;
  right: 0;
  z-index: 1000;
  width: 300px;
}

.notifications-container {
  width: 320px;
  background-color: var(--card-background);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  box-shadow: var(--shadow-md);
  overflow: hidden;
}

.notifications-header {
  padding: 16px;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.notifications-header h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-color);
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0 4px;
  color: var(--text-muted);
  transition: color 0.2s;
}

.close-btn:hover {
  color: var(--text-color);
}

.notifications-list {
  padding: 8px 0;
}

.notification {
  padding: 16px;
  display: flex;
  gap: 12px;
  cursor: pointer;
  transition: background-color 0.2s;
  border-bottom: 1px solid var(--border-color);
}

.notification:hover {
  background-color: var(--hover-background);
}

.icon {
  font-size: 1.2rem;
}

.icon.success {
  color: var(--success-color);
}

.icon.warning {
  color: var(--warning-color);
}

.icon.error {
  color: var(--error-color);
}

.notification-content {
  flex: 1;
}

.message {
  display: block;
  margin-bottom: 4px;
  font-size: 0.9rem;
  color: var(--text-color);
}

.time {
  display: block;
  font-size: 0.8rem;
  color: var(--text-muted);
}

.no-notifications {
  padding: 16px;
  text-align: center;
  color: var(--text-muted);
}
</style> 