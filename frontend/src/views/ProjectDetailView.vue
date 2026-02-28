<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/api';

const route = useRoute();
const project = ref<any>(null);
const tasks = ref<any[]>([]);
const loading = ref(true);
const tasksLoading = ref(false);

const filterStatus = ref('');
const filterPriority = ref('');
const taskSearch = ref('');

const fetchProject = async () => {
    try {
        const response = await api.get(`/projects/${route.params.id}`);
        project.value = response.data.data;
    } catch (err) {
        console.error('Failed to fetch project', err);
    } finally {
        loading.value = false;
    }
};

const fetchTasks = async () => {
    tasksLoading.value = true;
    try {
        const response = await api.get(`/projects/${route.params.id}/tasks`, {
            params: {
                status: filterStatus.value,
                priority: filterPriority.value,
                q: taskSearch.value,
            },
        });
        tasks.value = response.data.data;
    } catch (err) {
        console.error('Failed to fetch tasks', err);
    } finally {
        tasksLoading.value = false;
    }
};

onMounted(() => {
    fetchProject();
    fetchTasks();
});

watch([filterStatus, filterPriority, taskSearch], fetchTasks);

const showTaskModal = ref(false);
const newTask = ref({
    title: '',
    description: '',
    status: 'todo',
    priority: 'medium',
    due_date: '',
});

const createTask = async () => {
    try {
        await api.post(`/projects/${route.params.id}/tasks`, newTask.value);
        showTaskModal.value = false;
        newTask.value = { title: '', description: '', status: 'todo', priority: 'medium', due_date: '' };
        fetchTasks();
    } catch (err) {
        console.error('Failed to create task', err);
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'todo': return 'bg-gray-100 text-gray-800';
        case 'in_progress': return 'bg-blue-100 text-blue-800';
        case 'done': return 'bg-green-100 text-green-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getPriorityColor = (priority: string) => {
    switch (priority) {
        case 'high': return 'text-red-600';
        case 'medium': return 'text-yellow-600';
        case 'low': return 'text-green-600';
        default: return 'text-gray-600';
    }
};
</script>

<template>
    <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>

    <div v-else-if="project">
        <div class="mb-8">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <router-link to="/projects"
                            class="text-sm font-medium text-gray-500 hover:text-gray-700">Projects</router-link>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <span class="text-gray-400 mx-2">/</span>
                            <span class="text-sm font-medium text-gray-900">{{ project.name }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            {{ project.name }}
                        </h2>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            {{ project.description || 'No description provided.' }}
                        </p>
                    </div>
                    <button @click="showTaskModal = true"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                        Add Task
                    </button>
                </div>
            </div>
        </div>

        <!-- Task List -->
        <div class="mt-8">
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div class="sm:col-span-2">
                    <input v-model="taskSearch" type="text" placeholder="Search tasks..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
                <div>
                    <select v-model="filterStatus"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        <option value="">All Statuses</option>
                        <option value="todo">Todo</option>
                        <option value="in_progress">In Progress</option>
                        <option value="done">Done</option>
                    </select>
                </div>
                <div>
                    <select v-model="filterPriority"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        <option value="">All Priorities</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
            </div>

            <div v-if="tasksLoading" class="flex justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <div v-else-if="tasks.length === 0" class="text-center py-12 bg-white rounded-lg shadow-sm">
                <p class="text-gray-500">No tasks found for this project.</p>
            </div>

            <div v-else class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul role="list" class="divide-y divide-gray-200">
                    <li v-for="task in tasks" :key="task.id">
                        <router-link :to="`/tasks/${task.id}`" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-indigo-600 truncate">{{ task.title }}</p>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        <p
                                            :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getStatusColor(task.status)]">
                                            {{ task.status.replace('_', ' ').toUpperCase() }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-2 sm:flex sm:justify-between">
                                    <div class="sm:flex">
                                        <p class="flex items-center text-sm text-gray-500">
                                            Priority: <span
                                                :class="['ml-1 font-medium', getPriorityColor(task.priority)]">{{
                                                task.priority }}</span>
                                        </p>
                                        <p v-if="task.assignee"
                                            class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                            Assignee: {{ task.assignee.name }}
                                        </p>
                                    </div>
                                    <div v-if="task.due_date"
                                        class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                        Due: {{ new Date(task.due_date).toLocaleDateString() }}
                                    </div>
                                </div>
                            </div>
                        </router-link>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Create Task Modal -->
        <div v-if="showTaskModal" class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showTaskModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="inline-block align-middle bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <form @submit.prevent="createTask">
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-4 mb-4">Add New Task</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Title</label>
                                <input v-model="newTask.title" type="text" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea v-model="newTask.description" rows="3"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <select v-model="newTask.status"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white">
                                        <option value="todo">Todo</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="done">Done</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Priority</label>
                                    <select v-model="newTask.priority"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Due Date</label>
                                <input v-model="newTask.due_date" type="date"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" />
                            </div>
                        </div>
                        <div class="mt-6 flex space-x-3">
                            <button type="button" @click="showTaskModal = false"
                                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">Cancel</button>
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Create
                                Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
