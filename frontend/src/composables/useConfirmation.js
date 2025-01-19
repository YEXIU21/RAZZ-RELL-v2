import { ref } from 'vue';

const isOpen = ref(false);
const resolvePromise = ref(null);
const confirmationConfig = ref({
  title: '',
  message: '',
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  type: 'warning',
  confirmButtonClass: '',
  cancelButtonClass: '',
});

export function useConfirmation() {
  const confirm = (config = {}) => {
    return new Promise((resolve) => {
      isOpen.value = true;
      resolvePromise.value = resolve;
      
      // Merge default config with provided config
      Object.assign(confirmationConfig.value, {
        title: 'Confirm Action',
        message: 'Are you sure you want to proceed?',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        type: 'warning',
        confirmButtonClass: '',
        cancelButtonClass: '',
        ...config,
      });
    });
  };

  const handleConfirm = () => {
    isOpen.value = false;
    if (resolvePromise.value) {
      resolvePromise.value(true);
      resolvePromise.value = null;
    }
  };

  const handleCancel = () => {
    isOpen.value = false;
    if (resolvePromise.value) {
      resolvePromise.value(false);
      resolvePromise.value = null;
    }
  };

  const confirmDelete = (itemName = 'item') => {
    return confirm({
      title: 'Delete Confirmation',
      message: `Are you sure you want to delete this ${itemName}? This action cannot be undone.`,
      confirmText: 'Delete',
      type: 'danger',
      confirmButtonClass: 'bg-red-600 hover:bg-red-700',
    });
  };

  const confirmDiscard = (itemName = 'changes') => {
    return confirm({
      title: 'Discard Changes',
      message: `Are you sure you want to discard your ${itemName}? Any unsaved changes will be lost.`,
      confirmText: 'Discard',
      type: 'warning',
      confirmButtonClass: 'bg-yellow-600 hover:bg-yellow-700',
    });
  };

  const confirmLogout = () => {
    return confirm({
      title: 'Logout Confirmation',
      message: 'Are you sure you want to log out? Any unsaved changes will be lost.',
      confirmText: 'Logout',
      type: 'warning',
      confirmButtonClass: 'bg-blue-600 hover:bg-blue-700',
    });
  };

  return {
    isOpen,
    confirmationConfig,
    confirm,
    handleConfirm,
    handleCancel,
    confirmDelete,
    confirmDiscard,
    confirmLogout,
  };
} 