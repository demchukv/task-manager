<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import api from '@/api';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const projects = ref<any[]>([]);
const pagination = ref<any>(null);
const loading = ref(false);
const search = ref('');
const sort = ref('-created_at');
const page = ref(1);

const fetchProjects = async () => {
    loading.value = true;
    try {
        const response = await api.get('/projects', {
            params: {
                q: search.value,
                sort: sort.value,
                page: page.value,
            },
        });
        projects.value = response.data.data;
        pagination.value = response.data.meta;
    } catch (err) {
        console.error('Failed to fetch projects', err);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchProjects);

watch([search, sort, page], fetchProjects);

const newProject = ref({ name: '', description: '' });
const showModal = ref(false);

const createProject = async () => {
    try {
        await api.post('/projects', newProject.value);
        newProject.value = { name: '', description: '' };
        showModal.value = false;
        fetchProjects();
    } catch (err) {
        console.error('Failed to create project', err);
    }
};
</script>

<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Projects</h1>
            <button @click="showModal = true"
                class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">
                New Project
            </button>
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
            <div class="flex-1">
                <input v-model="search" type="text" placeholder="Search projects..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" />
            </div>
            <div>
                <select v-model="sort"
                    class="block w-full px-4 py-2 border border-gray-300 bg-white rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="-created_at">Newest First</option>
                    <option value="created_at">Oldest First</option>
                    <option value="name">Name (A-Z)</option>
                    <option value="-name">Name (Z-A)</option>
                </select>
            </div>
        </div>

        <!-- Project Grid -->
        <div v-if="loading" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else-if="projects.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
            <p class="text-gray-500">No projects found.</p>
        </div>

        <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="project in projects" :key="project.id"
                class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 truncate">
                        <router-link :to="`/projects/${project.id}`" class="hover:text-indigo-600">
                            {{ project.name }}
                        </router-link>
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 line-clamp-2">
                        {{ project.description || 'No description' }}
                    </p>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-xs text-gray-400">Created: {{ new
                            Date(project.created_at).toLocaleDateString() }}</span>
                        <span class="text-xs font-medium text-indigo-600">{{ project.owner?.name }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="mt-8 flex justify-center space-x-2">
            <button v-for="p in pagination.last_page" :key="p" @click="page = p"
                :class="['px-3 py-1 rounded-md text-sm font-medium', page === p ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300']">
                {{ p }}
            </button>
        </div>

        <!-- Create Project Modal -->
        <div v-if="showModal" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                    @click="showModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-middle bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <form @submit.prevent="createProject">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Create New Project
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <input v-model="newProject.name" type="text" required
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea v-model="newProject.description" rows="3"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 flex space-x-3">
                            <button type="button" @click="showModal = false"
                                class="flex-1 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:text-sm">
                                Cancel
                            </button>
                            <button type="submit"
                                class="flex-1 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:text-sm">
                                Create
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
