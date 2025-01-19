<template>
  <div class="packages-management">
    <main class="management-content">
      
      <div class="page-header">
        <h1>Packages Management</h1>
        <div class="header-actions">
          <div class="search-bar">
            <i class="fas fa-search"></i>
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search packages..."
              @input="handleSearch"
            />
          </div>
          <div class="filters">
            <select v-model="package_typeFilter">
              <option value="">All Event Types</option>
              <option value="Wedding">Wedding</option>
              <option value="Debut">Debut</option>
              <option value="Christening">Christening</option>
              <option value="Party">Party</option>
              <option value="Other">Other</option>
            </select>
            <button class="add-package-btn" @click="openAddPackageModal">
              <i class="fas fa-plus"></i>
              Add Package
            </button>
          </div>
        </div>
      </div>

      <div class="packages-grid">
        <div v-for="packageItem in filteredPackages" :key="packageItem.id" class="package-card">
          <div class="package-image">
            <img :src="getImageUrl(packageItem.package_image)" :alt="packageItem.package_name" />
            <div class="package-actions">
              <button @click="editPackage(packageItem)" class="action-btn edit">
                <i class="fas fa-edit"></i>
              </button>
              <button @click="deletePackage(packageItem)" class="action-btn delete">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
          
          <div class="package-content">
            <div class="package-header">
              <h3>{{ packageItem.package_name }}</h3>
              <div class="event-type" :style="getEventTypeStyle(packageItem.package_type)">
                {{ packageItem.package_type }}
              </div>
            </div>
            
            <div class="package-price">
              ₱{{ formatNumber(packageItem.package_price) }} <br>
              <span class="event-type" :class="packageItem.status.toLowerCase()">
                {{ packageItem.status }}
              </span>
            </div>
            
            <p class="package-description">{{ packageItem.description }}</p>
            
            <div class="package-inclusions">
              <h4>Inclusions:</h4>
              <ul>
                <li v-for="(inclusion, index) in packageItem.showAll ? JSON.parse(packageItem.package_inclusion) : JSON.parse(packageItem.package_inclusion).slice(0, 3)" 
                    :key="index">
                  {{ inclusion }}
                </li>
              </ul>
              <button 
                v-if="JSON.parse(packageItem.package_inclusion).length > 3"
                @click="packageItem.showAll = !packageItem.showAll"
                class="show-more-btn">
                {{ packageItem.showAll ? 'Show Less' : 'Show More' }}
              </button>
            </div>

            <div class="package-stats">
              <div class="stat">
                <i class="fas fa-calendar-check"></i>
                <span>{{ packageItem.bookingsCount }} Bookings</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="filteredPackages.length === 0" class="no-results">
        <i class="fas fa-box-open"></i>
        <p>No packages founds in {{ package_typeFilter }}</p>
      </div>
    </main>

    <!-- Modals -->
    <AddPackageModal
      v-if="showAddModal"
      @close="showAddModal = false"
      @success="handlePackageAdded"
    />

    <EditPackageModal
      v-if="showEditModal"
      :package="selectedPackage"
      @close="showEditModal = false"
      @update="handlePackageUpdated"
    />

    <ConfirmationModal
      v-if="showDeleteModal"
      title="Delete Package"
      message="Are you sure you want to delete this package? This action cannot be undone."
      type="danger"
      confirmText="Delete"
      @confirm="confirmDeletePackage"
      @close="showDeleteModal = false"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '@/composables/useAuth';
import AdminSidebar from '@/components/admin/AdminSidebar.vue';
import AddPackageModal from '@/components/admin/AddPackageModal.vue';
import EditPackageModal from '@/components/admin/EditPackageModal.vue';
import ConfirmationModal from '@/components/ui/ConfirmationModal.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { getEventTypeColors } from '@/utils/eventTypeColors';

export default {
  name: 'PackagesManagement',
  components: {
    AdminSidebar,
    AddPackageModal,
    EditPackageModal,
    ConfirmationModal
  },
  setup() {
    const { token } = useAuth();

    const packages = ref([]);
    const searchQuery = ref('');
    const package_typeFilter = ref('');
    const showAddModal = ref(false);
    const showEditModal = ref(false);
    const showDeleteModal = ref(false);
    const selectedPackage = ref(null); 

    const filteredPackages = computed(() => {
      return packages.value.filter(packageItem => {
        const matchesSearch = !searchQuery.value || 
          packageItem.package_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          packageItem.package_type.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        const matchespackage_type = !package_typeFilter.value || 
          (package_typeFilter.value === 'Other' 
            ? !['Wedding', 'Debut', 'Christening', 'Party'].includes(packageItem.package_type)
            : packageItem.package_type === package_typeFilter.value);
        
        return matchesSearch && matchespackage_type;
      });
    });

    const fetchPackages = async () => {
      try {
        const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-packages`);

        packages.value = response.data.map(pkg => ({
          ...pkg,
          showAll: false
        }));
      } catch (error) {
        console.error('Error fetching packages:', error);
      }
    };

    const handleSearch = () => {
      package_typeFilter.value = '';
    };

    const formatNumber = (num) => {
      return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    };

    const openAddPackageModal = () => {
      showAddModal.value = true;
    };

    const editPackage = (pkg) => {
      selectedPackage.value = pkg;
      showEditModal.value = true;
    };

    const deletePackage = (pkg) => {
      selectedPackage.value = pkg;
      showDeleteModal.value = true;
    };

    const confirmDeletePackage = async () => {
      try {
        const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/delete-package/${selectedPackage.value.id}`);
        if (response.data.status === 200) {
          Swal.fire({
            title: 'Success',
            text: response.data.message,
            icon: 'success'
          });
          await fetchPackages();
          showDeleteModal.value = false;
        }
      } catch (error) {
        console.error('Error deleting package:', error);
        Swal.fire({
          title: 'Error',
          text: error.response?.data?.message || 'Failed to delete package',
          icon: 'error'
        });
      }
    };

    const handlePackageAdded = async () => {
      await fetchPackages();
      showAddModal.value = false;
    };

    const handlePackageUpdated = async () => {
      await fetchPackages();
      showEditModal.value = false;
    };

    const defaultImageUrl = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiNFNUU3RUIiLz48cGF0aCBkPSJNODAgOTBIMTIwVjExMEg4MFY5MFoiIGZpbGw9IiM5Q0EzQUYiLz48cGF0aCBkPSJNNjUgNjBIMTM1VjgwSDY1VjYwWiIgZmlsbD0iIzlDQTNBRiIvPjwvc3ZnPg==';

    const getImageUrl = (imagePath) => {
      if (imagePath) {
        return `${import.meta.env.VITE_API_URL}/storage/${imagePath}`;
      } else {
        return defaultImageUrl;
      }
    };

    const getEventTypeStyle = (eventType) => {
      const colors = getEventTypeColors(eventType);
      return {
        backgroundColor: colors.background,
        color: colors.color
      };
    };

    onMounted(async () => {
      await fetchPackages();
    });

    return {
      packages,
      searchQuery,
      package_typeFilter,
      showAddModal,
      showEditModal,
      showDeleteModal,
      selectedPackage,
      filteredPackages,
      handleSearch,
      formatNumber,
      openAddPackageModal,
      editPackage,
      deletePackage,
      confirmDeletePackage,
      handlePackageAdded,
      handlePackageUpdated,
      getImageUrl,
      getEventTypeStyle
    };
  }
};
</script>

<style scoped>
.packages-management {
  display: flex;
  min-height: 100vh;
  background: var(--background-color);
}

.management-content {
  flex: 1;
  padding: 1.5rem 2rem;
  margin-left: 250px;
  max-width: 1600px;
  width: 100%;
  margin: 0 auto;
}

.page-header {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
}

.page-header h1 {
  font-size: 1.8rem;
  color: var(--text-color);
  margin-bottom: 1.5rem;
  font-weight: 600;
}

.header-actions {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 1.5rem;
  align-items: start;
}

.search-bar {
  position: relative;
  max-width: 400px;
}

.search-bar input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.95rem;
  background: var(--input-background);
  color: var(--text-color);
  transition: border-color 0.2s;
}

.search-bar input:focus {
  border-color: var(--primary-color);
  outline: none;
}

.search-bar i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
}

.filters {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.filters select {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--input-background);
  color: var(--text-color);
  min-width: 140px;
  font-size: 0.95rem;
}

.add-package-btn {
  padding: 0.75rem 1.25rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
  transition: all 0.2s;
}

.add-package-btn:hover {
  background: var(--primary-color-dark, #2563eb);
  transform: translateY(-1px);
}

.packages-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.package-card {
  background: var(--card-background);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  border: 1px solid var(--border-color);
}

.package-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.package-image {
  position: relative;
  height: 220px;
  overflow: hidden;
}

.package-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.package-card:hover .package-image img {
  transform: scale(1.05);
}

.package-actions {
  position: absolute;
  top: 1rem;
  right: 1rem;
  display: flex;
  gap: 0.5rem;
  opacity: 0;
  transform: translateY(-10px);
  transition: all 0.3s ease;
}

.package-card:hover .package-actions {
  opacity: 1;
  transform: translateY(0);
}

.action-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: none;
  background: var(--card-background);
  color: var(--text-color);
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.action-btn:hover {
  transform: translateY(-2px);
}

.action-btn.edit:hover {
  background: var(--primary-color);
  color: white;
}

.action-btn.delete:hover {
  background: var(--danger-color, #dc2626);
  color: white;
}

.package-content {
  padding: 1.5rem;
}

.package-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  gap: 1rem;
}

.package-header h3 {
  font-size: 1.25rem;
  color: var(--text-color);
  font-weight: 600;
  line-height: 1.4;
  margin: 0;
}

.event-type {
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
  text-align: center;
  display: inline-block;
  white-space: nowrap;
  transition: all 0.2s ease;
}

.event-type.wedding {
  background: #ffebee;
  color: #c62828;
}

.event-type.debut {
  background: #fff3e0;
  color: #ef6c00;
}

.event-type.christening {
  background: #e3f2fd;
  color: #1565c0;
}

.event-type.party {
  background: #fce4ec;
  color: #c2185b;
}

.event-type.other {
  background: #f3e5f5;
  color: #7b1fa2;
}

.event-type.active {
  background: #e8f5e9;
  color: #2e7d32;
}

.event-type.inactive {
  background: #ffebee;
  color: #c62828;
}

.event-type.preparing {
  background: #fff3e0;
  color: #ef6c00;
}

.package-price {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--primary-color);
  margin-bottom: 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.package-description {
  color: var(--text-muted);
  margin-bottom: 1.25rem;
  line-height: 1.6;
  font-size: 0.95rem;
}

.package-inclusions {
  margin-bottom: 1.25rem;
}

.package-inclusions h4 {
  font-size: 1rem;
  color: var(--text-color);
  margin-bottom: 0.75rem;
  font-weight: 600;
}

.package-inclusions ul {
  list-style: none;
  padding-left: 0;
  margin: 0;
}

.package-inclusions li {
  color: var(--text-muted);
  padding: 0.35rem 0;
  display: flex;
  align-items: center;
  font-size: 0.95rem;
}

.package-inclusions li::before {
  content: "•";
  color: var(--primary-color);
  font-weight: bold;
  margin-right: 0.75rem;
}

.show-more-btn {
  background: none;
  border: none;
  color: var(--primary-color);
  padding: 0.5rem 0;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 500;
}

.show-more-btn:hover {
  text-decoration: underline;
}

.package-stats {
  display: flex;
  gap: 1rem;
  padding-top: 1.25rem;
  border-top: 1px solid var(--border-color);
  margin-top: 1rem;
}

.stat {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-muted);
  font-size: 0.95rem;
}

.stat i {
  color: var(--primary-color);
}

.no-results {
  text-align: center;
  padding: 4rem 2rem;
  background: var(--card-background);
  border-radius: 12px;
  color: var(--text-muted);
  margin-top: 2rem;
}

.no-results i {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: var(--text-muted);
}

@media (max-width: 1200px) {
  .header-actions {
    grid-template-columns: 1fr;
  }
  
  .filters {
    flex-wrap: wrap;
  }
  
  .filters select {
    flex: 1;
    min-width: 120px;
  }
  
  .packages-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  }
}

@media (max-width: 768px) {
  .management-content {
    margin-left: 0;
    padding: 1rem;
  }

  .page-header {
    padding: 1rem;
  }

  .header-actions {
    gap: 1rem;
  }

  .search-bar {
    max-width: none;
  }

  .filters {
    width: 100%;
  }

  .add-package-btn {
    width: 100%;
    justify-content: center;
  }

  .packages-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .package-card {
    margin-bottom: 1rem;
  }

  .package-image {
    height: 200px;
  }
}
</style> 