<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-10">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black bg-opacity-25" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
              <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 mb-4">
                Package Details
              </DialogTitle>

              <div class="mt-2 space-y-4">
                <!-- Package Image -->
                <div v-if="package.package_image" class="mb-4">
                  <img :src="package.package_image" alt="Package Image" class="w-full h-48 object-cover rounded-lg">
                </div>

                <!-- Package Information -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="font-semibold">Package Name:</label>
                    <p>{{ package.package_name }}</p>
                  </div>

                  <div>
                    <label class="font-semibold">Package Type:</label>
                    <p>{{ package.package_type }}</p>
                  </div>

                  <div>
                    <label class="font-semibold">Number of Packs:</label>
                    <p>{{ package.packs }}</p>
                  </div>

                  <div>
                    <label class="font-semibold">Price:</label>
                    <p>₱{{ package.package_price }}</p>
                  </div>

                  <div class="col-span-2">
                    <label class="font-semibold">Description:</label>
                    <p>{{ package.package_description }}</p>
                  </div>

                  <div class="col-span-2">
                    <label class="font-semibold">Inclusions:</label>
                    <p>{{ package.package_inclusion }}</p>
                  </div>

                  <div>
                    <label class="font-semibold">Status:</label>
                    <span :class="{
                      'px-2 py-1 rounded text-sm': true,
                      'bg-green-100 text-green-800': package.status === 'active',
                      'bg-red-100 text-red-800': package.status === 'inactive'
                    }">
                      {{ package.status }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="mt-6 flex justify-end">
                <button
                  type="button"
                  class="inline-flex justify-center rounded-md border border-transparent bg-blue-100 px-4 py-2 text-sm font-medium text-blue-900 hover:bg-blue-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                  @click="closeModal"
                >
                  Close
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  package: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close'])

const closeModal = () => {
  emit('close')
}
</script> 