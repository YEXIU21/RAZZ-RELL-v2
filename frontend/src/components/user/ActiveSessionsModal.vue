<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Active Sessions</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="modal-body">
        <div class="sessions-list">
          <div
            v-for="session in sessions"
            :key="session.id"
            class="session-item"
            :class="{ 'current': session.current }"
          >
            <div class="session-info">
              <div class="device-icon">
                <i
                  class="fas"
                  :class="{
                    'fa-desktop': session.device === 'desktop',
                    'fa-mobile-alt': session.device === 'mobile',
                    'fa-tablet-alt': session.device === 'tablet',
                  }"
                ></i>
              </div>

              <div class="session-details">
                <h3>{{ session.deviceName }}</h3>
                <div class="session-meta">
                  <span class="location">
                    <i class="fas fa-map-marker-alt"></i>
                    {{ session.location }}
                  </span>
                  <span class="browser">
                    <i class="fas fa-globe"></i>
                    {{ session.browser }}
                  </span>
                  <span class="ip">
                    <i class="fas fa-network-wired"></i>
                    {{ session.ip }}
                  </span>
                </div>
                <div class="session-time">
                  <span class="last-active">
                    <i class="fas fa-clock"></i>
                    Last active {{ formatLastActive(session.lastActive) }}
                  </span>
                  <span class="login-time">
                    <i class="fas fa-sign-in-alt"></i>
                    Logged in {{ formatLoginTime(session.loginTime) }}
                  </span>
                </div>
              </div>
            </div>

            <div class="session-actions">
              <span v-if="session.current" class="current-badge">
                <i class="fas fa-check-circle"></i>
                Current Session
              </span>
              <button
                v-else
                class="revoke-btn"
                @click="handleRevoke(session)"
                :disabled="isRevoking[session.id]"
              >
                <i
                  class="fas"
                  :class="isRevoking[session.id] ? 'fa-spinner fa-spin' : 'fa-sign-out-alt'"
                ></i>
                {{ isRevoking[session.id] ? 'Revoking...' : 'Revoke' }}
              </button>
            </div>
          </div>
        </div>

        <div v-if="sessions.length === 0" class="no-sessions">
          <i class="fas fa-laptop-house"></i>
          <p>No other active sessions</p>
        </div>
      </div>

      <div class="modal-footer">
        <button
          class="revoke-all-btn"
          @click="handleRevokeAll"
          :disabled="!hasOtherSessions || isRevokingAll"
        >
          <i
            class="fas"
            :class="isRevokingAll ? 'fa-spinner fa-spin' : 'fa-sign-out-alt'"
          ></i>
          {{ isRevokingAll ? 'Revoking All...' : 'Revoke All Other Sessions' }}
        </button>
        <button class="close-btn-secondary" @click="$emit('close')">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useConfirmation } from '@/composables/useConfirmation';
import { useNotifications } from '@/composables/useNotifications';

const props = defineProps({
  sessions: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['close', 'revoke']);

const { confirm } = useConfirmation();
const { error: showError } = useNotifications();

const isRevoking = ref({});
const isRevokingAll = ref(false);

const hasOtherSessions = computed(() => {
  return props.sessions.some(session => !session.current);
});

const formatLastActive = (timestamp) => {
  const date = new Date(timestamp);
  const now = new Date();
  const diff = now - date;

  if (diff < 60000) { // less than 1 minute
    return 'Just now';
  } else if (diff < 3600000) { // less than 1 hour
    const minutes = Math.floor(diff / 60000);
    return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
  } else if (diff < 86400000) { // less than 1 day
    const hours = Math.floor(diff / 3600000);
    return `${hours} hour${hours > 1 ? 's' : ''} ago`;
  } else {
    return date.toLocaleDateString();
  }
};

const formatLoginTime = (timestamp) => {
  const date = new Date(timestamp);
  return date.toLocaleString();
};

const handleRevoke = async (session) => {
  try {
    const confirmed = await confirm({
      title: 'Revoke Session',
      message: `Are you sure you want to revoke this session from ${session.deviceName}?`,
      confirmText: 'Revoke',
      type: 'warning',
    });

    if (confirmed) {
      isRevoking.value[session.id] = true;
      await emit('revoke', session.id);
      isRevoking.value[session.id] = false;
    }
  } catch (err) {
    showError('Failed to revoke session');
    isRevoking.value[session.id] = false;
  }
};

const handleRevokeAll = async () => {
  try {
    const confirmed = await confirm({
      title: 'Revoke All Sessions',
      message: 'Are you sure you want to revoke all other active sessions? This will sign out all devices except your current one.',
      confirmText: 'Revoke All',
      type: 'warning',
    });

    if (confirmed) {
      isRevokingAll.value = true;
      const otherSessions = props.sessions.filter(session => !session.current);
      
      for (const session of otherSessions) {
        await emit('revoke', session.id);
      }
    }
  } catch (err) {
    showError('Failed to revoke all sessions');
  } finally {
    isRevokingAll.value = false;
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: var(--card-background);
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  font-size: 1.5rem;
  color: var(--text-color);
  margin: 0;
}

.close-btn {
  padding: 0.5rem;
  border: none;
  border-radius: 6px;
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: var(--hover-background);
  color: var(--text-color);
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
}

.sessions-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.session-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--input-background);
}

.session-item.current {
  border-color: var(--primary-color);
  background: var(--primary-color);
  background: linear-gradient(
    45deg,
    var(--primary-color) 0%,
    var(--primary-hover) 100%
  );
}

.session-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.device-icon {
  width: 48px;
  height: 48px;
  background: var(--card-background);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: var(--primary-color);
}

.session-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.session-details h3 {
  font-size: 1.1rem;
  color: var(--text-color);
  margin: 0;
}

.session-meta,
.session-time {
  display: flex;
  gap: 1rem;
  color: var(--text-muted);
  font-size: 0.875rem;
}

.session-meta span,
.session-time span {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.session-actions {
  display: flex;
  align-items: center;
}

.current-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: white;
  font-size: 0.875rem;
}

.revoke-btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  background: var(--error-color);
  color: white;
  font-size: 0.875rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.revoke-btn:hover:not(:disabled) {
  background: var(--error-hover);
  transform: translateY(-1px);
}

.revoke-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.no-sessions {
  text-align: center;
  padding: 3rem;
  color: var(--text-muted);
}

.no-sessions i {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid var(--border-color);
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.revoke-all-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  background: var(--error-color);
  color: white;
  font-size: 0.95rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.revoke-all-btn:hover:not(:disabled) {
  background: var(--error-hover);
  transform: translateY(-1px);
}

.revoke-all-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.close-btn-secondary {
  padding: 0.75rem 1.5rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: transparent;
  color: var(--text-color);
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-btn-secondary:hover {
  background: var(--hover-background);
}

@media (max-width: 640px) {
  .session-item {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .session-info {
    flex-direction: column;
  }

  .session-meta,
  .session-time {
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
  }

  .modal-footer {
    flex-direction: column;
  }

  .revoke-all-btn,
  .close-btn-secondary {
    width: 100%;
    justify-content: center;
  }
}
</style> 