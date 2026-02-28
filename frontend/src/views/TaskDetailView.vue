<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/api';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const task = ref<any>(null);
const comments = ref<any[]>([]);
const loading = ref(true);
const newComment = ref('');
const submitting = ref(false);

const fetchTask = async () => {
    try {
        const response = await api.get(`/tasks/${route.params.id}`);
        task.value = response.data.data;
    } catch (err) {
        console.error('Failed to fetch task', err);
    } finally {
        loading.value = false;
    }
};

const fetchComments = async () => {
    try {
        const response = await api.get(`/tasks/${route.params.id}/comments`);
        comments.value = response.data.data;
    } catch (err) {
        console.error('Failed to fetch comments', err);
    }
};

onMounted(() => {
    fetchTask();
    fetchComments();
});

const postComment = async () => {
    if (!newComment.value.trim()) return;
    submitting.value = true;
    try {
        await api.post(`/tasks/${route.params.id}/comments`, { body: newComment.value });
        newComment.value = '';
        fetchComments();
    } catch (err) {
        console.error('Failed to post comment', err);
    } finally {
        submitting.value = false;
    }
};

const statusOptions = ['todo', 'in_progress', 'done'];
const priorityOptions = ['low', 'medium', 'high'];

const updateTask = async () => {
    try {
        await api.put(`/tasks/${task.value.id}`, {
            status: task.value.status,
            priority: task.value.priority,
        });
    } catch (err) {
        console.error('Failed to update task', err);
    }
};
</script>

<template>
    <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>

    <div v-else-if="task">
        <div class="mb-6 flex justify-between items-center">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <router-link :to="`/projects/${task.project_id}`"
                            class="text-sm font-medium text-gray-500 hover:text-gray-700">Project Tasks</router-link>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <span class="text-gray-400 mx-2">/</span>
                            <span class="text-sm font-medium text-gray-900">Task Details</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden mb-8">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-start">
                    <h2 class="text-2xl font-bold text-gray-900">{{ task.title }}</h2>
                    <div class="flex space-x-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Status</label>
                            <select v-model="task.status" @change="updateTask"
                                class="mt-1 block w-full pl-3 pr-10 py-1 text-sm border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md bg-gray-50">
                                <option v-for="opt in statusOptions" :key="opt" :value="opt">{{ opt.replace('_', '
                                    ').toUpperCase() }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Priority</label>
                            <select v-model="task.priority" @change="updateTask"
                                class="mt-1 block w-full pl-3 pr-10 py-1 text-sm border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md bg-gray-50">
                                <option v-for="opt in priorityOptions" :key="opt" :value="opt">{{ opt.toUpperCase() }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mt-4 prose prose-sm max-w-none text-gray-700 border-t pt-4">
                    <p>{{ task.description || 'No description available for this task.' }}</p>
                </div>
                <div class="mt-6 flex items-center text-sm text-gray-500">
                    <span v-if="task.assignee" class="mr-6">Assigned to: <strong>{{ task.assignee.name
                    }}</strong></span>
                    <span v-if="task.due_date">Due: <strong>{{ new Date(task.due_date).toLocaleDateString()
                    }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-6">Comments</h3>

                <div class="space-y-6 mb-8">
                    <div v-for="comment in comments" :key="comment.id" class="flex space-x-3">
                        <div class="flex-shrink-0">
                            <div
                                class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold">
                                {{ comment.user.name.charAt(0) }}
                            </div>
                        </div>
                        <div class="flex-1 bg-gray-50 rounded-lg px-4 py-3">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-sm font-bold text-gray-900">{{ comment.user.name }}</h4>
                                <span class="text-xs text-gray-500">{{ new Date(comment.created_at).toLocaleString()
                                }}</span>
                            </div>
                            <p class="text-sm text-gray-700">{{ comment.body }}</p>
                        </div>
                    </div>

                    <div v-if="comments.length === 0" class="text-center py-4 text-gray-500 text-sm">
                        No comments yet. Be the first to reply!
                    </div>
                </div>

                <form @submit.prevent="postComment" class="mt-6">
                    <div class="flex space-x-3">
                        <div class="flex-shrink-0">
                            <div
                                class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold">
                                {{ auth.user?.name.charAt(0) }}
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div>
                                <textarea v-model="newComment" rows="3" placeholder="Write a comment..."
                                    class="block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                            </div>
                            <div class="mt-3 flex items-center justify-end">
                                <button type="submit" :disabled="submitting || !newComment.trim()"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none disabled:opacity-50">
                                    {{ submitting ? 'Posting...' : 'Post Comment' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
