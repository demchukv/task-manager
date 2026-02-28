<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '@/api'
import { useAuthStore } from '@/stores/auth'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import TaskCard from '@/components/tasks/TaskCard.vue'
import {
    PlusIcon,
    SearchIcon,
    Loader2Icon,
    ChevronLeftIcon
} from 'lucide-vue-next'

const route = useRoute()
const auth = useAuthStore()
const project = ref<any>(null)
const tasks = ref<any[]>([]);
const users = ref<any[]>([])
const loading = ref(true)
const tasksLoading = ref(false)

const filterStatus = ref('')
const filterPriority = ref('')
const taskSearch = ref('')

const fetchProject = async () => {
    try {
        const response = await api.get(`/projects/${route.params.id}`)
        project.value = response.data
    } catch (err) {
        console.error('Failed to fetch project', err)
    } finally {
        loading.value = false
    }
}

const fetchTasks = async () => {
    tasksLoading.value = true
    try {
        const response = await api.get(`/projects/${route.params.id}/tasks`, {
            params: {
                status: filterStatus.value,
                priority: filterPriority.value,
                q: taskSearch.value,
            },
        })
        tasks.value = response.data.data
    } catch (err) {
        console.error('Failed to fetch tasks', err)
    } finally {
        tasksLoading.value = false
    }
}

const fetchUsers = async () => {
    if (!auth.isAdmin) return
    try {
        const response = await api.get('/users')
        users.value = response.data
    } catch (err) {
        console.error('Failed to fetch users', err)
    }
}

onMounted(() => {
    fetchProject()
    fetchTasks()
    fetchUsers()
})

watch([filterStatus, filterPriority, taskSearch], fetchTasks)

const showTaskModal = ref(false)
const newTask = ref({
    title: '',
    description: '',
    status: 'todo',
    priority: 'medium',
    due_date: '',
    assignee_id: '',
})

const createTask = async () => {
    try {
        await api.post(`/projects/${route.params.id}/tasks`, newTask.value)
        showTaskModal.value = false
        newTask.value = { title: '', description: '', status: 'todo', priority: 'medium', due_date: '', assignee_id: '' }
        fetchTasks()
    } catch (err) {
        console.error('Failed to create task', err)
    }
}
</script>

<template>
    <div class="space-y-6">
        <div v-if="loading" class="flex flex-col items-center justify-center py-24 space-y-4">
            <Loader2Icon class="h-8 w-8 animate-spin text-primary" />
            <p class="text-sm text-muted-foreground">Loading project details...</p>
        </div>

        <div v-else-if="project" class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-500">
            <!-- Breadcrumbs & Header -->
            <div class="flex flex-col gap-4">
                <nav class="flex items-center text-xs text-muted-foreground">
                    <RouterLink to="/projects" class="hover:text-primary flex items-center gap-1 transition-colors">
                        <ChevronLeftIcon class="h-3 w-3" />
                        Back to Projects
                    </RouterLink>
                    <Separator orientation="vertical" class="mx-2 h-3" />
                    <span class="font-medium text-foreground truncate max-w-[200px]">{{ project.name }}</span>
                </nav>

                <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
                    <div class="space-y-1">
                        <h1 class="text-3xl font-bold tracking-tight">{{ project.name }}</h1>
                        <p class="text-muted-foreground text-sm max-w-2xl">
                            {{ project.description || 'No description provided for this project.' }}
                        </p>
                    </div>
                    <Button @click="showTaskModal = true" class="shrink-0">
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Add Task
                    </Button>
                </div>
            </div>

            <Separator />

            <!-- Tasks Section -->
            <div class="space-y-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <h2 class="text-xl font-semibold flex items-center gap-2">
                        Tasks
                        <Badge variant="secondary" class="rounded-full px-2" v-if="tasks.length">{{ tasks.length }}
                        </Badge>
                    </h2>

                    <div class="flex flex-col sm:flex-row items-center gap-2 w-full md:w-auto">
                        <div class="relative w-full sm:w-64">
                            <SearchIcon class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input v-model="taskSearch" type="search" placeholder="Search tasks..." class="pl-8 h-9" />
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <select v-model="filterStatus"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-xs shadow-sm focus:outline-none focus:ring-1 focus:ring-ring">
                                <option value="">All Statuses</option>
                                <option value="todo">Todo</option>
                                <option value="in_progress">In Progress</option>
                                <option value="done">Done</option>
                            </select>
                            <select v-model="filterPriority"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-xs shadow-sm focus:outline-none focus:ring-1 focus:ring-ring">
                                <option value="">All Priorities</option>
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div v-if="tasksLoading" class="flex flex-col items-center justify-center py-12 space-y-2">
                    <Loader2Icon class="h-6 w-6 animate-spin text-primary" />
                    <p class="text-xs text-muted-foreground">Updating tasks...</p>
                </div>

                <div v-else-if="tasks.length === 0"
                    class="flex flex-col items-center justify-center py-24 border rounded-lg border-dashed bg-muted/20">
                    <p class="text-muted-foreground text-sm text-center">
                        No tasks found.
                        <br />
                        <span v-if="taskSearch || filterStatus || filterPriority">Try adjusting your filters.</span>
                        <span v-else>Start by adding a new task to this project.</span>
                    </p>
                    <Button variant="link" size="sm" @click="taskSearch = ''; filterStatus = ''; filterPriority = ''"
                        v-if="taskSearch || filterStatus || filterPriority">
                        Clear all filters
                    </Button>
                </div>

                <div v-else class="grid grid-cols-1 gap-4">
                    <TaskCard v-for="task in tasks" :key="task.id" :task="task" />
                </div>
            </div>
        </div>

        <!-- Add Task Modal -->
        <div v-if="showTaskModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <Card class="w-full max-w-lg shadow-2xl animate-in fade-in zoom-in duration-200">
                <CardHeader>
                    <CardTitle>Add New Task</CardTitle>
                    <p class="text-sm text-muted-foreground">Define a new task for {{ project?.name }}.</p>
                </CardHeader>
                <form @submit.prevent="createTask">
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="title">Title</Label>
                            <Input id="title" v-model="newTask.title" placeholder="e.g. Design user interface"
                                required />
                        </div>
                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="newTask.description"
                                placeholder="Add more details about this task..." rows="3" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="status">Status</Label>
                                <select id="status" v-model="newTask.status"
                                    class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring">
                                    <option value="todo">Todo</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="done">Done</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label for="priority">Priority</Label>
                                <select id="priority" v-model="newTask.priority"
                                    class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label for="due_date">Due Date</Label>
                            <Input id="due_date" v-model="newTask.due_date" type="date" />
                        </div>
                        <div v-if="auth.isAdmin" class="space-y-2">
                            <Label for="assignee">Assign To</Label>
                            <select id="assignee" v-model="newTask.assignee_id"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring">
                                <option value="">Unassigned</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }} ({{ user.email }})
                                </option>
                            </select>
                        </div>
                    </CardContent>
                    <div class="flex items-center justify-end gap-3 p-6 pt-0">
                        <Button variant="outline" type="button" @click="showTaskModal = false">Cancel</Button>
                        <Button type="submit">Add Task</Button>
                    </div>
                </form>
            </Card>
        </div>
    </div>
</template>
