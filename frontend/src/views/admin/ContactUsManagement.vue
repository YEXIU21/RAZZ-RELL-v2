<template>
  <div class="users-management">
    <main class="management-content">
      <div class="page-header">
        <h1>Contact Us Management</h1>
        <div class="search-bar">
          <i class="fas fa-search"></i>
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Search messages..."
            @input="handleSearch"
          />
        </div>
      </div>

      <div class="content-container">
        <div class="table-container">
          <table class="users-table">
            <thead>
              <tr>
                <th>
                  <div class="th-content">
                    ID
                    <i class="fas"></i>
                  </div>
                </th>
                <th>User Information</th>
                <th>Message</th>
                <th>
                  <div class="th-content">
                    Reasonable Type
                    <i class="fas fa-sort"></i>
                  </div>
                </th>
                <th>
                  <div class="th-content">
                    Date Created
                    <i class="fas fa-sort"></i>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in filteredUsers" :key="user.id">
                <td>#{{ user.id }}</td>
                <td>
                  <div class="user-info">
                    <div>
                      <span class="user-name">{{ user.first_name }} {{ user.last_name }}</span>
                      <span class="user-email">{{ user.email }}</span>
                      <span class="user-email">{{ user.phone }}</span>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="status-badge" style="text-transform: capitalize;" :class="user.flag === '1' ? 'banned' : user.event_type">
                    {{ showFullMessage[user.id] ? user.message : (user.message.length > 50 ? user.message.slice(0, 50) + '...' : user.message) }}
                    <button v-if="user.message.length > 50" 
                            @click="toggleMessage(user.id)"
                            class="view-more-btn">
                      {{ showFullMessage[user.id] ? 'View Less' : 'View More' }}
                    </button>
                  </span>
                </td>
                <td>
                  <span class="status-badge" :class="user.event_type.toLowerCase()">
                    {{ user.event_type }}
                  </span>
                </td>
                <td>
                  <div class="last-login">
                    <span class="date">{{ formatDate(user.updated_at) }}</span>
                    <span class="time">{{ formatTime(user.updated_at) }}</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="pagination">
          <button 
            :disabled="currentPage === 1" 
            @click="changePage(currentPage - 1)"
            class="page-btn"
          >
            <i class="fas fa-chevron-left"></i>
          </button>
          <span class="page-info">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          <button 
            :disabled="currentPage === totalPages" 
            @click="changePage(currentPage + 1)"
            class="page-btn"
          >
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '@/composables/useAuth';
import AdminSidebar from '@/components/admin/AdminSidebar.vue';
import AddUserModal from '@/components/admin/AddUserModal.vue';
import EditUserModal from '@/components/admin/EditUserModal.vue';
import UserDetailsModal from '@/components/admin/UserDetailsModal.vue';
import ConfirmationModal from '@/components/ui/ConfirmationModal.vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const { token } = useAuth();

// State
const contactUsData = ref([]);
const searchQuery = ref('');
const currentPage = ref(1);
const showFullMessage = ref({});

// Computed
const filteredUsers = computed(() => {
  return contactUsData.value.filter(contactus => {
    const matchesSearch = searchQuery.value === '' || 
      contactus.first_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      contactus.last_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      contactus.email.toLowerCase().includes(searchQuery.value.toLowerCase());
    
    return matchesSearch;
  });
});

const totalPages = computed(() => {
  return Math.ceil(filteredUsers.value.length / 5);
});

const fetchContactUs = async () => {
  const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-contact-us`);
  contactUsData.value = response.data;
  console.log(response.data);
};

const handleSearch = () => {
  currentPage.value = 1;
};

const changePage = (page) => {
  currentPage.value = page;
};

const toggleMessage = (id) => {
  showFullMessage.value[id] = !showFullMessage.value[id];
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString('en-PH', {
    hour: '2-digit',
    minute: '2-digit'
  });
};


onMounted(async () => {
  await fetchContactUs();
});
</script>

<style scoped>
.users-management {
  display: flex;
  min-height: 100vh;
  background: var(--background-color);
}

.management-content {
  flex: 1;
  padding: 2rem;
  margin-left: 250px;
  max-width: 1600px;
  width: 100%;
  margin: 0 auto;
  background: var(--background-color);
}

.page-header {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
  margin-bottom: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
}

.page-header h1 {
  font-size: 1.8rem;
  color: var(--text-color);
  font-weight: 600;
  margin: 0;
  white-space: nowrap;
}

.search-bar {
  position: relative;
  flex: 1;
  max-width: 500px;
}

.search-bar i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-secondary);
}

.search-bar input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.95rem;
  background: var(--card-background);
  color: var(--text-color);
  transition: all 0.2s ease;
}

.search-bar input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px var(--primary-light);
}

.content-container {
  background: var(--card-background);
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.table-container {
  overflow-x: auto;
}

.users-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.users-table th {
  background: var(--hover-background);
  padding: 1rem 1.5rem;
  text-align: left;
  font-weight: 600;
  color: var(--text-color);
  border-bottom: 1px solid var(--border-color);
}

.users-table td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
  color: var(--text-color);
  vertical-align: top;
}

.users-table td:nth-child(3) {
  max-width: 300px;
  word-wrap: break-word;
}

.users-table td:nth-child(2) {
  min-width: 200px;
  max-width: 250px;
}

.th-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.user-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.user-info div {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.user-name {
  font-weight: 500;
  color: var(--text-color);
  display: block;
}

.user-email {
  font-size: 0.875rem;
  color: var(--text-secondary);
  display: block;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.375rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
  background: var(--hover-background);
  color: var(--text-color);
}

.status-badge.wedding {
  background: var(--wedding-bg);
  color: var(--wedding-text);
}

.status-badge.debut {
  background: var(--debut-bg);
  color: var(--debut-text);
}

.status-badge.christening {
  background: var(--christening-bg);
  color: var(--christening-text);
}

.status-badge.party {
  background: var(--party-bg);
  color: var(--party-text);
}

.status-badge.other {
  background: var(--other-bg);
  color: var(--other-text);
}

.status-badge.active {
  background: var(--success-light);
  color: var(--success-color);
}

.status-badge.inactive {
  background: var(--error-light);
  color: var(--error-color);
}

.status-badge.preparing {
  background: var(--warning-light);
  color: var(--warning-color);
}

.status-badge.banned {
  background: var(--error-light);
  color: var(--error-color);
}

.status-badge.inquiry {
  background: var(--info-light);
  color: var(--info-color);
}

.status-badge.complaint {
  background: var(--error-light);
  color: var(--error-color);
}

.status-badge.suggestion {
  background: var(--success-light);
  color: var(--success-color);
}

.status-badge.feedback {
  background: var(--warning-light);
  color: var(--warning-color);
}

.status-badge.support {
  background: var(--other-bg);
  color: var(--other-text);
}

.status-badge.general {
  background: var(--hover-background);
  color: var(--text-color);
}

.view-more-btn {
  background: none;
  border: none;
  color: var(--primary-color);
  padding: 0;
  margin-left: 0.5rem;
  cursor: pointer;
  font-size: 0.875rem;
}

.view-more-btn:hover {
  text-decoration: underline;
}

.last-login {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.last-login .date {
  color: var(--text-color);
}

.last-login .time {
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 1rem;
  background: var(--card-background);
  border-top: 1px solid var(--border-color);
}

.page-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  cursor: pointer;
  transition: all 0.2s ease;
}

.page-btn:hover:not(:disabled) {
  background: var(--hover-background);
  border-color: var(--border-color);
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.95rem;
  color: var(--text-secondary);
}

@media (max-width: 1024px) {
  .management-content {
    padding: 1.5rem;
  }
}

@media (max-width: 768px) {
  .management-content {
    margin-left: 0;
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .search-bar {
    max-width: none;
  }

  .users-table th,
  .users-table td {
    padding: 0.75rem 1rem;
  }
}
</style> 